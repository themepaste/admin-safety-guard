<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: LoginLogsActivity
 *
 * Records every successful sign-in as its own row, so the monitoring screen is
 * a genuine audit trail rather than a snapshot.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class LoginLogsActivity implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'login-logs-activity';

    /**
     * User meta key holding the addresses a user has signed in from before.
     *
     * @var string
     */
    const KNOWN_IPS_META = '_tpsa_known_ips';

    /**
     * How many previous addresses to remember per user.
     *
     * @var int
     */
    const KNOWN_IPS_LIMIT = 10;

    /**
     * Registers the WordPress hooks for this feature.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_hooks() {
        $this->action( 'wp_login', [$this, 'save_successful_login_log'], 10, 2 );
    }

    /**
     * Record a successful sign-in.
     *
     * Each sign-in is inserted as a new row. The previous implementation
     * UPDATEd a single row per username, which meant the log only ever showed
     * the most recent sign-in for each account — there was no way to review
     * history, spot a sign-in at an unusual hour, or see that an account had
     * been used from two different addresses.
     *
     * @param string   $user_login Username.
     * @param \WP_User $user       User object.
     *
     * @return void
     */
    public function save_successful_login_log( $user_login, $user = null ) {
        global $wpdb;

        $table = get_tpsa_db_table_name( 's_logins' );

        if ( !$this->table_exists( $table ) ) {
            return;
        }

        $ip_address = tpsa_get_client_ip();
        $user_agent = $this->get_user_agent();
        $login_time = current_time( 'mysql' );
        $user_login = substr( (string) $user_login, 0, 100 );

        $user_id = ( $user instanceof \WP_User ) ? (int) $user->ID : 0;

        // Running total of sign-ins for this account, so the UI can still show
        // how often an account signs in alongside the history.
        $login_count = 1 + (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE username = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $user_login
            )
        );

        $is_new_ip = $user_id ? $this->remember_ip( $user_id, $ip_address ) : 0;

        $wpdb->insert(
            $table,
            [
                'username'    => $user_login,
                'ip_address'  => $ip_address,
                'user_agent'  => $user_agent,
                'login_time'  => $login_time,
                'login_count' => $login_count,
                'is_new_ip'   => $is_new_ip,
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d']
        );

        if ( $is_new_ip ) {
            $this->maybe_notify_new_ip( $user, $ip_address, $user_agent, $login_time );
        }
    }

    /**
     * Track the addresses a user signs in from.
     *
     * @param int    $user_id User ID.
     * @param string $ip      Address used for this sign-in.
     *
     * @return int 1 when this address has not been seen before, else 0.
     */
    private function remember_ip( $user_id, $ip ) {
        $known = get_user_meta( $user_id, self::KNOWN_IPS_META, true );
        $known = is_array( $known ) ? $known : [];

        if ( in_array( $ip, $known, true ) ) {
            return 0;
        }

        // The first ever recorded sign-in is not "unusual", it is just the first.
        $is_first_ever = empty( $known );

        $known[] = $ip;
        if ( count( $known ) > self::KNOWN_IPS_LIMIT ) {
            $known = array_slice( $known, -self::KNOWN_IPS_LIMIT );
        }

        update_user_meta( $user_id, self::KNOWN_IPS_META, $known );

        return $is_first_ever ? 0 : 1;
    }

    /**
     * Email the administrator when a privileged account signs in from an
     * address it has never used before.
     *
     * Scoped to accounts that can manage the site: a new address for a
     * subscriber is noise, whereas a new address for an administrator is one of
     * the clearest early signals of a compromised account.
     *
     * @param \WP_User|null $user       User that signed in.
     * @param string        $ip         Address used.
     * @param string        $user_agent Browser string.
     * @param string        $login_time Site-local MySQL datetime.
     *
     * @return void
     */
    private function maybe_notify_new_ip( $user, $ip, $user_agent, $login_time ) {
        if ( !( $user instanceof \WP_User ) ) {
            return;
        }

        $settings = get_option( get_tpsa_settings_option_name( $this->features_id ), [] );

        if ( empty( $settings['notify-new-ip'] ) ) {
            return;
        }

        if ( !user_can( $user, 'manage_options' ) ) {
            return;
        }

        $admin_email = get_option( 'admin_email' );

        if ( !is_email( $admin_email ) ) {
            return;
        }

        // One alert per user + address per day is plenty.
        $throttle = 'tpsa_newip_' . md5( $user->ID . '|' . $ip );
        if ( false !== get_transient( $throttle ) ) {
            return;
        }
        set_transient( $throttle, 1, DAY_IN_SECONDS );

        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

        $subject = sprintf(
            /* translators: 1: site name, 2: username. */
            __( '[%1$s] Administrator %2$s signed in from a new IP address', 'admin-safety-guard' ),
            $site_name,
            $user->user_login
        );

        // Every value below is user-supplied or attacker-influenced.
        $body = sprintf(
            '<p>%1$s</p><ul><li><strong>%2$s</strong> %3$s</li><li><strong>%4$s</strong> %5$s</li><li><strong>%6$s</strong> %7$s</li><li><strong>%8$s</strong> %9$s</li></ul><p>%10$s</p>',
            esc_html__( 'An account with administrator access signed in from an IP address it has not used before.', 'admin-safety-guard' ),
            esc_html__( 'Account:', 'admin-safety-guard' ),
            esc_html( $user->user_login ),
            esc_html__( 'IP address:', 'admin-safety-guard' ),
            esc_html( $ip ),
            esc_html__( 'Browser:', 'admin-safety-guard' ),
            esc_html( $user_agent ),
            esc_html__( 'Time:', 'admin-safety-guard' ),
            esc_html( $login_time ),
            esc_html__( 'If this was not you, change the password for that account immediately and review the sign-in log.', 'admin-safety-guard' )
        );

        wp_mail( $admin_email, $subject, $body, ['Content-Type: text/html; charset=UTF-8'] );
    }

    /**
     * Truncated, sanitised User-Agent string.
     *
     * @return string
     */
    private function get_user_agent() {
        $agent = isset( $_SERVER['HTTP_USER_AGENT'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
        : '';

        return '' === $agent ? 'Unknown' : substr( $agent, 0, 255 );
    }

    /**
     * Whether a plugin table exists, cached per request.
     *
     * @param string $table Fully-prefixed table name.
     * @return bool
     */
    private function table_exists( $table ) {
        static $checked = [];

        if ( isset( $checked[$table] ) ) {
            return $checked[$table];
        }

        global $wpdb;

        $checked[$table] = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );

        return $checked[$table];
    }
}

<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: LimitLoginAttempts
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class LimitLoginAttempts implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'limit-login-attempts';

    /**
     * Register hooks.
     */
    public function register_hooks() {
        $settings = $this->get_settings();

        if ( $this->is_enabled( $settings ) ) {
            $this->action( 'admin_init', [$this, 'check_wp_cron_status'] );

            if ( !$this->is_white_list_ip( $settings ) ) {
                $this->action( 'wp_login_failed', [$this, 'tpsa_track_failed_login_24hr'] );
                $this->action( 'login_init', [$this, 'hide_login_form_with_ip_address_status'] );
                $this->action( 'login_init', [$this, 'maybe_block_login_form'] );
                $this->action( 'template_redirect', [$this, 'maybe_block_custom_login'] );

                $this->filter(
                    'authenticate',
                    function ( $user ) {
                        if ( $this->is_ip_locked_out() ) {
                            return new \WP_Error(
                                'access_denied',
                                __( 'You are temporarily blocked due to too many failed login attempts.', 'admin-safety-guard' )
                            );
                        }
                        return $user;
                    },
                    0
                );
            }
        }
    }

    /**
     * Whether the current IP is on the firewall whitelist and bypasses lockouts.
     *
     * @param array $settings Feature settings.
     * @return bool
     */
    public function is_white_list_ip( $settings ) {
        $whitelist_ips = isset( $settings['whitelist-ip'] ) && is_array( $settings['whitelist-ip'] )
        ? $settings['whitelist-ip']
        : [];

        if ( empty( $whitelist_ips ) ) {
            return false;
        }

        return in_array( $this->get_ip_address(), $whitelist_ips, true );
    }

    /**
     * Whether the current request targets a login or admin entry point.
     *
     * @return bool
     */
    private function is_login_request() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        return strpos( $request_uri, 'wp-login.php' ) !== false
        || strpos( $request_uri, 'wp-admin' ) !== false;
    }

    /**
     * Render the "permanently blocked" screen and stop the request.
     *
     * @return void
     */
    private function deny_permanently_blocked() {
        wp_die(
            esc_html__( 'Access Denied - You have been blocked for 1 day due to repeated login failures.', 'admin-safety-guard' ),
            esc_html__( 'Access Denied', 'admin-safety-guard' ),
            ['response' => 403]
        );
    }

    /**
     * Render the temporary lockout screen and stop the request.
     *
     * @return void
     */
    private function deny_temporarily_locked() {
        $settings = $this->get_settings();
        $block_message = isset( $settings['block-message'] ) && '' !== trim( (string) $settings['block-message'] )
        ? (string) $settings['block-message']
        : __( 'You have been locked out due to too many login attempts.', 'admin-safety-guard' );
        $block_for = isset( $settings['block-for'] ) ? (int) $settings['block-for'] : 15;

        wp_die(
            esc_html(
                sprintf(
                    /* translators: 1: configured lockout message, 2: lockout duration in minutes */
                    __( 'Access Denied - %1$s Please try again after %2$d minutes.', 'admin-safety-guard' ),
                    $block_message,
                    $block_for
                )
            ),
            esc_html__( 'Access Denied', 'admin-safety-guard' ),
            ['response' => 403]
        );
    }

    public function maybe_block_custom_login() {
        // On the front end also guard themed login/register pages.
        if ( !$this->is_login_request() && !is_page( 'login' ) && !is_page( 'register' ) ) {
            return;
        }

        if ( $this->is_permanently_blocked_ip() ) {
            $this->deny_permanently_blocked();
        } elseif ( $this->is_ip_locked_out() ) {
            $this->deny_temporarily_locked();
        }
    }

    public function maybe_block_login_form() {
        if ( !$this->is_login_request() ) {
            return;
        }

        if ( $this->is_permanently_blocked_ip() ) {
            $this->deny_permanently_blocked();
        } elseif ( $this->is_ip_locked_out() ) {
            $this->deny_temporarily_locked();
        }
    }

    /**
     * Check the status of WordPress cron and display a warning if disabled.
     *
     * @since 1.0.0
     */
    public function check_wp_cron_status() {
        $settings = $this->get_settings();
        if ( !$this->is_enabled( $settings ) ) {
            return;
        }

        if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
            add_action(
                'admin_notices',
                function () {
                    ?>
<div class="notice notice-error is-dismissible">
    <p>
        <strong><?php esc_html_e( 'Warning:', 'admin-safety-guard' ); ?></strong>
        <?php esc_html_e( 'WordPress Cron is currently disabled.', 'admin-safety-guard' ); ?>
    </p>

    <p><?php esc_html_e( 'Please check the following to resolve the issue:', 'admin-safety-guard' ); ?></p>

    <ul>
        <li>
            <?php
echo wp_kses_post(
                        __( 'Ensure the <code>DISABLE_WP_CRON</code> constant is <strong>not</strong> defined in your <code>wp-config.php</code> file. If it is, remove or comment out the line: <code>define(\'DISABLE_WP_CRON\', true);</code>', 'admin-safety-guard' )
                    );
                    ?>
        </li>

        <li>
            <?php
echo wp_kses_post(
                        __( 'Ensure your server cron is properly configured to trigger <code>wp-cron.php</code> periodically. You may need to set up a server-side cron job (using <code>cron</code> on Linux or Task Scheduler on Windows).', 'admin-safety-guard' )
                    );
                    ?>
        </li>

        <li><?php esc_html_e( 'If you\'re unsure how to configure the server cron, please consult your hosting provider for assistance.', 'admin-safety-guard' ); ?>
        </li>
    </ul>

    <p>
        <strong><?php esc_html_e( 'Note:', 'admin-safety-guard' ); ?></strong>
        <?php esc_html_e( 'This plugin will not function properly without a working cron job. The blocked users will not be unblocked automatically if the cron is not running.', 'admin-safety-guard' ); ?>
    </p>
</div>
<?php
}
            );
        }
    }

    /**
     * UPDATED LOGIC (no schema change):
     * - Keep ALL failed attempts by INSERT-ing a new row each time (history).
     * - When attempts hit threshold, INSERT a lockout-event row (lockouts=1, lockout_time=now).
     * - When lockouts in last 24h reach max_lockouts, INSERT into block_users (permanent block for your current logic).
     */
    public function tpsa_track_failed_login_24hr( $username ) {
        $settings = $this->get_settings();
        if ( !$this->is_enabled( $settings ) ) {
            return;
        }

        $max_attempts = max( 1, (int) ( $settings['max-attempts'] ?? 3 ) );
        $max_lockouts = max( 1, (int) ( $settings['max-lockout'] ?? 3 ) );

        global $wpdb;

        $table_name = get_tpsa_db_table_name( 'failed_logins' );
        $blocked_table = get_tpsa_db_table_name( 'block_users' );

        $ip = $this->get_ip_address();
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'Unknown';
        $now = current_time( 'mysql' );

        // Find existing row by IP + User Agent (your requested behavior)
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE ip_address = %s AND user_agent = %s LIMIT 1",
                $ip,
                $user_agent
            )
        );

        // If row exists but is older than 24h, reset counters (keeps same row)
        if ( $existing && !empty( $existing->last_login_time ) ) {
            $last_ts = strtotime( $existing->last_login_time );
            if ( $last_ts && ( time() - $last_ts ) > DAY_IN_SECONDS ) {
                $existing->login_attempts = 0;
                $existing->lockouts = 0;
                $existing->lockout_time = null;

                // reset the tracking window
                $wpdb->update(
                    $table_name,
                    [
                        'first_login_time' => $now,
                        'last_login_time'  => $now,
                        'login_attempts'   => 0,
                        'lockouts'         => 0,
                        'lockout_time'     => null,
                        'username'         => $username,
                        'user_agent'       => $user_agent,
                    ],
                    ['id' => (int) $existing->id],
                    ['%s', '%s', '%d', '%d', '%s', '%s', '%s'],
                    ['%d']
                );

                // Refresh object values for continuation
                $existing = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$table_name} WHERE id = %d LIMIT 1",
                        (int) $existing->id
                    )
                );
            }
        }

        if ( $existing ) {
            $attempts = (int) $existing->login_attempts + 1;
            $lockouts = (int) $existing->lockouts;

            $lockout_time = $existing->lockout_time;

            // Hit attempts threshold → lockout event, reset attempts
            if ( $attempts >= $max_attempts ) {
                $lockouts += 1;
                $attempts = 0;
                $lockout_time = $now;

                // If lockouts threshold reached → add to block_users (permanent 24h)
                if ( $lockouts >= $max_lockouts ) {

                    $already_blocked = (int) $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$blocked_table} WHERE ip_address = %s",
                            $ip
                        )
                    );

                    if ( !$already_blocked ) {
                        // Optional email (same as your current logic)
                        $admin_email = get_option( 'admin_email' );
                        $subject = __( 'A user has been blocked for 24 hours due to multiple failed login attempts.', 'admin-safety-guard' );

                        // The IP and especially the User-Agent are attacker
                        // controlled, so escape them before dropping them into
                        // an HTML email body.
                        $body = sprintf(
                            '%1$s<br><br><b>%2$s</b><br>- IP Address: %3$s<br>- User Agent: %4$s<br>- Login Time: %5$s',
                            esc_html__( 'A visitor has been blocked for 24 hours due to repeated login failures.', 'admin-safety-guard' ),
                            esc_html__( 'Details:', 'admin-safety-guard' ),
                            esc_html( $ip ),
                            esc_html( $user_agent ),
                            esc_html( $now )
                        );

                        $headers = ['Content-Type: text/html; charset=UTF-8'];
                        wp_mail( $admin_email, $subject, $body, $headers );

                        $wpdb->insert(
                            $blocked_table,
                            [
                                'user_agent' => $user_agent,
                                'ip_address' => $ip,
                                'login_time' => $now,
                            ],
                            ['%s', '%s', '%s']
                        );
                    }
                }
            }

            // Update the existing row
            $wpdb->update(
                $table_name,
                [
                    'last_login_time' => $now,
                    'login_attempts'  => $attempts,
                    'lockouts'        => $lockouts,
                    'lockout_time'    => $lockout_time,
                    'username'        => $username,
                    'user_agent'      => $user_agent,
                ],
                ['id' => (int) $existing->id],
                ['%s', '%d', '%d', '%s', '%s', '%s'],
                ['%d']
            );

            return;
        }

        // No row yet for this IP + User Agent → first insert
        $wpdb->insert(
            $table_name,
            [
                'username'         => $username,
                'user_agent'       => $user_agent,
                'ip_address'       => $ip,
                'first_login_time' => $now,
                'last_login_time'  => $now,
                'login_attempts'   => 1,
                'lockouts'         => 0,
                'lockout_time'     => null,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
        );
    }

    public function is_permanently_blocked_ip() {
        global $wpdb;

        $blocked_table = get_tpsa_db_table_name( 'block_users' );
        $ip = $this->get_ip_address();
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $blocked_table WHERE ip_address = %s",
                $ip
            )
        );

        return ( $count > 0 );
    }

    /**
     * UPDATED LOGIC:
     * - Look for latest lockout event row (lockout_time) and compare with block-for minutes.
     */
    public function is_ip_locked_out() {
        global $wpdb;

        $settings = $this->get_settings();
        $blocked_minute = (int) ( $settings['block-for'] ?? 15 );

        $table = get_tpsa_db_table_name( 'failed_logins' );
        $ip = $this->get_ip_address();

        $lockout_time = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT lockout_time
             FROM {$table}
             WHERE ip_address = %s AND lockout_time IS NOT NULL
             ORDER BY lockout_time DESC
             LIMIT 1",
                $ip
            )
        );

        if ( $lockout_time ) {
            // lockout_time is stored in site-local time via current_time( 'mysql' ),
            // so compare against the site-local "now" rather than a UTC timestamp.
            $lockout_ts = strtotime( $lockout_time );
            $now_ts = strtotime( current_time( 'mysql' ) );

            if ( $lockout_ts && ( $now_ts - $lockout_ts ) < ( $blocked_minute * 60 ) ) {
                return true;
            }
        }

        return false;
    }

    public function hide_login_form_with_ip_address_status() {
        $settings = $this->get_settings();
        if ( !$this->is_enabled( $settings ) ) {
            return;
        }
        $block_ip_lists = isset( $settings['block-ip-address'] ) && is_array( $settings['block-ip-address'] )
        ? $settings['block-ip-address']
        : [];

        if ( empty( $block_ip_lists ) ) {
            return;
        }

        if ( in_array( $this->get_ip_address(), $block_ip_lists, true ) ) {
            wp_die(
                '<h2 style="color:red;text-align:center;">' . esc_html__( 'Your IP address is not permitted to log in to this site.', 'admin-safety-guard' ) . '</h2>',
                esc_html__( 'Login Blocked', 'admin-safety-guard' ),
                ['response' => 403]
            );
        }
    }

    /**
     * Resolve the current client IP address.
     *
     * Delegates to the shared, spoofing-resistant helper which trusts only
     * REMOTE_ADDR unless the site has opted in to proxy headers via the
     * `tpsa_trust_proxy_headers` filter. Forwarded headers were previously
     * trusted unconditionally, which let attackers bypass lockouts and the IP
     * blocklist by forging X-Forwarded-For / Client-IP.
     *
     * @return string
     */
    private function get_ip_address() {
        return tpsa_get_client_ip();
    }

    /**
     * Returns the settings for this feature.
     *
     * @return array
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->features_id );
        return get_option( $option_name, [] );
    }

    /**
     * Check if the feature is enabled.
     *
     * @param array $settings
     * @return bool
     */
    private function is_enabled( $settings ) {
        return isset( $settings['enable'] ) && (int) $settings['enable'] === 1;
    }
}
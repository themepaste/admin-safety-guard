<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: LimitLoginAttempts
 *
 * Counts failed sign-in attempts per IP address and locks the address out for a
 * configurable window. Repeated lockouts inside 24 hours escalate to a 24-hour
 * block (cleared by the plugin's daily cron job).
 *
 * Coverage: wp-login.php, XML-RPC, WooCommerce/theme login forms (anything that
 * goes through wp_authenticate()) and Application Passwords.
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
     * Priority for the `authenticate` filter.
     *
     * This MUST run after core's wp_authenticate_username_password() (priority
     * 20). That callback only short-circuits when it receives a WP_User — if it
     * receives a WP_Error and the credentials are non-empty it discards the
     * error and authenticates anyway. Returning the lockout error earlier than
     * 20 therefore does not stop a locked-out client that has valid
     * credentials (e.g. over XML-RPC, which never reaches login_init).
     *
     * It also has to stay below 30, where TwoFactorAuth intercepts and emails
     * an OTP, so a locked-out address never triggers an OTP mail.
     *
     * @var int
     */
    const AUTH_PRIORITY = 25;

    /**
     * Per-request cache of the lockout state, keyed by IP.
     *
     * @var array<string, array>
     */
    private static $state_cache = [];

    /**
     * Cached settings for this request.
     *
     * @var array|null
     */
    private $settings = null;

    /**
     * Register hooks.
     */
    public function register_hooks() {
        $settings = $this->get_settings();

        if ( !$this->is_enabled( $settings ) ) {
            return;
        }

        $this->action( 'admin_init', [$this, 'check_wp_cron_status'] );

        // Record failures from every authentication path.
        $this->action( 'wp_login_failed', [$this, 'record_failed_login'], 10, 2 );
        $this->action( 'application_password_failed_authentication', [$this, 'record_failed_application_password'] );

        // Clear the counter once the visitor proves they are legitimate.
        $this->action( 'wp_login', [$this, 'clear_failed_logins'], 10, 2 );

        // Enforce. See AUTH_PRIORITY for why this priority matters.
        $this->filter( 'authenticate', [$this, 'block_authentication'], self::AUTH_PRIORITY, 3 );

        // Deny the login screen outright so a locked-out visitor gets a clear
        // message instead of a generic credentials error.
        $this->action( 'login_init', [$this, 'guard_login_screen'] );

        // Themed front-end login/registration pages.
        $this->action( 'template_redirect', [$this, 'guard_front_end_login'] );

        // Tell the visitor how many tries they have left.
        $this->filter( 'login_errors', [$this, 'append_attempts_remaining'] );
    }

    /* ---------------------------------------------------------------------
     * Enforcement
     * ------------------------------------------------------------------- */

    /**
     * Reject authentication for a locked-out or blocked address.
     *
     * @param \WP_User|\WP_Error|null $user     Result so far.
     * @param string                  $username Submitted username.
     * @param string                  $password Submitted password.
     *
     * @return \WP_User|\WP_Error|null
     */
    public function block_authentication( $user, $username = '', $password = '' ) {
        // Nothing to do for an already-failed attempt with no credentials.
        if ( '' === (string) $username && '' === (string) $password ) {
            return $user;
        }

        if ( $this->is_exempt() ) {
            return $user;
        }

        $state = $this->get_state();

        if ( $state['blocked'] ) {
            return new \WP_Error( 'tpsa_ip_blocked', $this->blocked_message() );
        }

        if ( $state['locked'] ) {
            return new \WP_Error( 'tpsa_ip_locked_out', $this->lockout_message( $state['minutes_left'] ) );
        }

        return $user;
    }

    /**
     * Stop a locked-out or denied address from reaching the login screen.
     *
     * @return void
     */
    public function guard_login_screen() {
        if ( $this->is_exempt() ) {
            return;
        }

        // Manually denied addresses never see the form at all.
        if ( $this->is_denied_ip() ) {
            \ThemePaste\SecureAdmin\Classes\ThreatLog::report( 'ip_denied' );
            $this->deny(
                __( 'Your IP address is not permitted to sign in to this site.', 'admin-safety-guard' ),
                __( 'Login Blocked', 'admin-safety-guard' )
            );
        }

        $state = $this->get_state();

        if ( $state['blocked'] ) {
            $this->deny( $this->blocked_message() );
        } elseif ( $state['locked'] ) {
            $this->deny( $this->lockout_message( $state['minutes_left'] ) );
        }
    }

    /**
     * Same protection for themed login/registration pages on the front end.
     *
     * @return void
     */
    public function guard_front_end_login() {
        // Cheap bail-out first: this runs on every front-end request, so no
        // database work unless the request actually looks like a login page.
        if ( !is_page( array( 'login', 'register', 'sign-in', 'signin' ) ) ) {
            return;
        }

        $this->guard_login_screen();
    }

    /**
     * Append "X attempts remaining" to the login error output.
     *
     * @param string $errors Existing error markup.
     * @return string
     */
    public function append_attempts_remaining( $errors ) {
        $settings = $this->get_settings();

        // Field default is on, so an option saved before this setting existed
        // should behave as on rather than silently off.
        $show_remaining = array_key_exists( 'show-remaining', $settings )
        ? !empty( $settings['show-remaining'] )
        : true;

        if ( !$show_remaining || $this->is_exempt() ) {
            return $errors;
        }

        $state = $this->get_state();

        if ( $state['blocked'] || $state['locked'] || $state['attempts_left'] < 1 ) {
            return $errors;
        }

        $notice = sprintf(
            /* translators: %d: number of sign-in attempts left before lockout. */
            _n(
                'You have %d attempt remaining before this IP address is locked out.',
                'You have %d attempts remaining before this IP address is locked out.',
                $state['attempts_left'],
                'admin-safety-guard'
            ),
            $state['attempts_left']
        );

        return $errors . '<p class="tpsa-attempts-remaining">' . esc_html( $notice ) . '</p>';
    }

    /* ---------------------------------------------------------------------
     * Recording
     * ------------------------------------------------------------------- */

    /**
     * Record a failed sign-in for the current IP address.
     *
     * Attempts are tracked per IP ONLY. An earlier version keyed the counter on
     * IP + User-Agent, which meant an attacker who randomised the User-Agent
     * header started a fresh counter on every request and was never locked out.
     *
     * @param string         $username Attempted username (stored for the log only).
     * @param \WP_Error|null $error    Error from the authentication stack.
     *
     * @return void
     */
    public function record_failed_login( $username, $error = null ) {
        if ( $this->is_exempt() ) {
            return;
        }

        $settings = $this->get_settings();
        $max_attempts = max( 1, (int) ( $settings['max-attempts'] ?? 3 ) );
        $max_lockouts = max( 1, (int) ( $settings['max-lockout'] ?? 3 ) );

        $table = get_tpsa_db_table_name( 'failed_logins' );

        if ( !$this->table_exists( $table ) ) {
            return;
        }

        global $wpdb;

        $ip = $this->get_ip_address();
        $user_agent = $this->get_user_agent();
        $now = current_time( 'mysql' );
        $username = is_string( $username ) ? substr( sanitize_user( $username, true ), 0, 100 ) : '';

        // Newest row for this IP. Installs upgraded from the IP + User-Agent
        // scheme can hold several rows per address; the most recent one carries
        // the live counter.
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE ip_address = %s ORDER BY id DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $ip
            )
        );

        $attempts = 1;
        $lockouts = 0;
        $lockout_time = null;
        $first_seen = $now;

        if ( $row ) {
            $last_ts = !empty( $row->last_login_time ) ? strtotime( $row->last_login_time ) : 0;
            $window_expired = $last_ts && ( strtotime( $now ) - $last_ts ) > DAY_IN_SECONDS;

            if ( $window_expired ) {
                // Rolling 24-hour window: start fresh.
                $attempts = 1;
                $lockouts = 0;
                $lockout_time = null;
                $first_seen = $now;
            } else {
                $attempts = (int) $row->login_attempts + 1;
                $lockouts = (int) $row->lockouts;
                $lockout_time = $row->lockout_time;
                $first_seen = $row->first_login_time;
            }
        }

        // Threshold reached: open a lockout window and reset the attempt counter.
        $just_locked = false;
        if ( $attempts >= $max_attempts ) {
            $attempts = 0;
            $lockouts++;
            $lockout_time = $now;
            $just_locked = true;
        }

        if ( $row ) {
            $wpdb->update(
                $table,
                [
                    'username'         => $username,
                    'user_agent'       => $user_agent,
                    'first_login_time' => $first_seen,
                    'last_login_time'  => $now,
                    'login_attempts'   => $attempts,
                    'lockouts'         => $lockouts,
                    'lockout_time'     => $lockout_time,
                ],
                ['id' => (int) $row->id],
                ['%s', '%s', '%s', '%s', '%d', '%d', '%s'],
                ['%d']
            );
        } else {
            $wpdb->insert(
                $table,
                [
                    'username'         => $username,
                    'user_agent'       => $user_agent,
                    'ip_address'       => $ip,
                    'first_login_time' => $first_seen,
                    'last_login_time'  => $now,
                    'login_attempts'   => $attempts,
                    'lockouts'         => $lockouts,
                    'lockout_time'     => $lockout_time,
                ],
                ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
            );
        }

        // Too many lockouts inside the window: escalate to a 24-hour block.
        if ( $just_locked && $lockouts >= $max_lockouts ) {
            $this->block_ip( $ip, $user_agent, $now );
        }

        if ( $just_locked ) {
            \ThemePaste\SecureAdmin\Classes\ThreatLog::report(
                $lockouts >= $max_lockouts ? 'ip_blocked' : 'login_lockout',
                $username,
                $ip
            );

            $this->maybe_notify_admin( $ip, $user_agent, $username, $now, $lockouts >= $max_lockouts );
        }

        // The state changed; drop the cached copy.
        unset( self::$state_cache[$ip] );
    }

    /**
     * Record a failed Application Password authentication.
     *
     * Application Passwords authenticate through the `determine_current_user`
     * filter rather than wp_authenticate(), so they never fire wp_login_failed.
     * Without this the REST API is an uncounted brute-force surface.
     *
     * @param \WP_Error $error Authentication error.
     * @return void
     */
    public function record_failed_application_password( $error = null ) {
        $username = '';

        if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            $username = sanitize_user( wp_unslash( $_SERVER['PHP_AUTH_USER'] ), true );
        }

        $this->record_failed_login( $username );
    }

    /**
     * Clear the failure counter for an address after a successful sign-in.
     *
     * Without this a legitimate user who mistypes their password stays one
     * attempt away from a lockout for the next 24 hours.
     *
     * @param string    $user_login Username.
     * @param \WP_User  $user       User object.
     *
     * @return void
     */
    public function clear_failed_logins( $user_login = '', $user = null ) {
        $table = get_tpsa_db_table_name( 'failed_logins' );

        if ( !$this->table_exists( $table ) ) {
            return;
        }

        global $wpdb;

        $ip = $this->get_ip_address();

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table} SET login_attempts = 0, lockouts = 0, lockout_time = NULL WHERE ip_address = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $ip
            )
        );

        unset( self::$state_cache[$ip] );
    }

    /**
     * Add an address to the 24-hour block table.
     *
     * @param string $ip         IP address.
     * @param string $user_agent User agent string.
     * @param string $now        Site-local MySQL datetime.
     *
     * @return void
     */
    private function block_ip( $ip, $user_agent, $now ) {
        $table = get_tpsa_db_table_name( 'block_users' );

        if ( !$this->table_exists( $table ) ) {
            return;
        }

        global $wpdb;

        $already = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE ip_address = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $ip
            )
        );

        if ( $already ) {
            return;
        }

        $wpdb->insert(
            $table,
            [
                'user_agent' => $user_agent,
                'ip_address' => $ip,
                'login_time' => $now,
            ],
            ['%s', '%s', '%s']
        );
    }

    /**
     * Email the site administrator about a lockout, if enabled.
     *
     * @param string $ip         IP address.
     * @param string $user_agent User agent string.
     * @param string $username   Attempted username.
     * @param string $now        Site-local MySQL datetime.
     * @param bool   $escalated  Whether this lockout escalated to a 24h block.
     *
     * @return void
     */
    private function maybe_notify_admin( $ip, $user_agent, $username, $now, $escalated ) {
        $settings = $this->get_settings();

        if ( empty( $settings['notify-admin'] ) ) {
            return;
        }

        $admin_email = get_option( 'admin_email' );

        if ( !is_email( $admin_email ) ) {
            return;
        }

        // Don't let a sustained attack turn into a mail flood.
        $throttle_key = 'tpsa_lockout_mail_' . md5( $ip );
        if ( false !== get_transient( $throttle_key ) ) {
            return;
        }
        set_transient( $throttle_key, 1, HOUR_IN_SECONDS );

        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

        $subject = $escalated
        ? sprintf(
            /* translators: %s: site name. */
            __( '[%s] An IP address has been blocked for 24 hours', 'admin-safety-guard' ),
            $site_name
        )
        : sprintf(
            /* translators: %s: site name. */
            __( '[%s] An IP address has been locked out', 'admin-safety-guard' ),
            $site_name
        );

        // The IP, username and especially the User-Agent are attacker
        // controlled, so escape them before building an HTML email body.
        $body = sprintf(
            '<p>%1$s</p><ul><li><strong>%2$s</strong> %3$s</li><li><strong>%4$s</strong> %5$s</li><li><strong>%6$s</strong> %7$s</li><li><strong>%8$s</strong> %9$s</li></ul>',
            $escalated
            ? esc_html__( 'An IP address has been blocked for 24 hours after repeated lockouts.', 'admin-safety-guard' )
            : esc_html__( 'An IP address has been locked out after too many failed sign-in attempts.', 'admin-safety-guard' ),
            esc_html__( 'IP address:', 'admin-safety-guard' ),
            esc_html( $ip ),
            esc_html__( 'Attempted username:', 'admin-safety-guard' ),
            esc_html( '' !== $username ? $username : __( '(none)', 'admin-safety-guard' ) ),
            esc_html__( 'User agent:', 'admin-safety-guard' ),
            esc_html( $user_agent ),
            esc_html__( 'Time:', 'admin-safety-guard' ),
            esc_html( $now )
        );

        wp_mail( $admin_email, $subject, $body, ['Content-Type: text/html; charset=UTF-8'] );
    }

    /* ---------------------------------------------------------------------
     * State
     * ------------------------------------------------------------------- */

    /**
     * Resolve the lockout state for the current address.
     *
     * Cached for the lifetime of the request: the login screen, the
     * authenticate filter and the error notice all need it, and each lookup
     * would otherwise be two more queries.
     *
     * @return array{blocked:bool,locked:bool,minutes_left:int,attempts_left:int}
     */
    private function get_state() {
        $ip = $this->get_ip_address();

        if ( isset( self::$state_cache[$ip] ) ) {
            return self::$state_cache[$ip];
        }

        $settings = $this->get_settings();
        $max_attempts = max( 1, (int) ( $settings['max-attempts'] ?? 3 ) );
        $block_minutes = max( 1, (int) ( $settings['block-for'] ?? 15 ) );

        $state = [
            'blocked'       => false,
            'locked'        => false,
            'minutes_left'  => 0,
            'attempts_left' => $max_attempts,
        ];

        global $wpdb;

        $block_table = get_tpsa_db_table_name( 'block_users' );
        if ( $this->table_exists( $block_table ) ) {
            $state['blocked'] = (bool) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT 1 FROM {$block_table} WHERE ip_address = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $ip
                )
            );
        }

        $failed_table = get_tpsa_db_table_name( 'failed_logins' );
        if ( !$state['blocked'] && $this->table_exists( $failed_table ) ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT login_attempts, lockout_time FROM {$failed_table}
                     WHERE ip_address = %s
                     ORDER BY ( lockout_time IS NULL ), lockout_time DESC, id DESC
                     LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $ip
                )
            );

            if ( $row ) {
                $state['attempts_left'] = max( 0, $max_attempts - (int) $row->login_attempts );

                if ( !empty( $row->lockout_time ) ) {
                    // Stored with current_time( 'mysql' ), so compare against
                    // site-local "now" rather than a UTC timestamp.
                    $lockout_ts = strtotime( $row->lockout_time );
                    $now_ts = strtotime( current_time( 'mysql' ) );
                    $elapsed = $now_ts - $lockout_ts;
                    $window = $block_minutes * MINUTE_IN_SECONDS;

                    if ( $lockout_ts && $elapsed >= 0 && $elapsed < $window ) {
                        $state['locked'] = true;
                        $state['minutes_left'] = max( 1, (int) ceil( ( $window - $elapsed ) / MINUTE_IN_SECONDS ) );
                    }
                }
            }
        }

        self::$state_cache[$ip] = $state;

        return $state;
    }

    /* ---------------------------------------------------------------------
     * Exemptions
     * ------------------------------------------------------------------- */

    /**
     * Whether the current visitor is exempt from all lockout handling.
     *
     * @return bool
     */
    private function is_exempt() {
        // WP-CLI and cron are not brute-force surfaces and must never be locked out.
        if ( ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
            return true;
        }

        // A visitor who already holds a valid administrator session cannot be
        // brute-forcing the login form, and locking them out would leave nobody
        // able to switch the feature off.
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            return true;
        }

        if ( $this->is_trusted_ip() ) {
            return true;
        }

        /**
         * Filter whether the current request bypasses login-attempt limiting.
         *
         * @since 1.4.0
         *
         * @param bool   $exempt Whether to skip enforcement.
         * @param string $ip     Resolved client IP address.
         */
        return (bool) apply_filters( 'tpsa_limit_login_exempt', false, $this->get_ip_address() );
    }

    /**
     * Whether the current IP is on the trusted (never lock out) list.
     *
     * @return bool
     */
    public function is_trusted_ip() {
        $settings = $this->get_settings();

        return $this->ip_matches_list( $this->get_ip_address(), $settings['whitelist-ip'] ?? [] );
    }

    /**
     * Whether the current IP is on the manual deny list.
     *
     * @return bool
     */
    public function is_denied_ip() {
        $settings = $this->get_settings();

        return $this->ip_matches_list( $this->get_ip_address(), $settings['block-ip-address'] ?? [] );
    }

    /**
     * Match an IP against a list of addresses, CIDR ranges or wildcards.
     *
     * Accepts exact addresses (`203.0.113.7`), CIDR notation
     * (`203.0.113.0/24`, `2001:db8::/32`) and trailing wildcards
     * (`203.0.113.*`), so admins can trust a whole office network.
     *
     * @param string $ip   Address to test.
     * @param mixed  $list Configured list.
     *
     * @return bool
     */
    private function ip_matches_list( $ip, $list ) {
        if ( '' === $ip || empty( $list ) ) {
            return false;
        }

        foreach ( (array) $list as $entry ) {
            $entry = trim( (string) $entry );

            if ( '' === $entry ) {
                continue;
            }

            if ( $entry === $ip ) {
                return true;
            }

            if ( false !== strpos( $entry, '/' ) && $this->ip_in_cidr( $ip, $entry ) ) {
                return true;
            }

            // Trailing wildcard, e.g. 203.0.113.*
            if ( false !== strpos( $entry, '*' ) ) {
                $prefix = rtrim( substr( $entry, 0, strpos( $entry, '*' ) ), '.' );
                if ( '' !== $prefix && 0 === strpos( $ip, $prefix . '.' ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether an address falls inside a CIDR range (IPv4 or IPv6).
     *
     * @param string $ip   Address to test.
     * @param string $cidr Range in CIDR notation.
     *
     * @return bool
     */
    private function ip_in_cidr( $ip, $cidr ) {
        list( $subnet, $bits ) = array_pad( explode( '/', $cidr, 2 ), 2, null );

        $subnet = trim( (string) $subnet );
        if ( null === $bits || '' === trim( (string) $bits ) ) {
            return false;
        }
        $bits = (int) $bits;

        $ip_bin = @inet_pton( $ip );
        $subnet_bin = @inet_pton( $subnet );

        // Both must parse, and both must be the same family (4 or 16 bytes).
        if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
            return false;
        }

        $max_bits = strlen( $ip_bin ) * 8;
        if ( $bits < 0 || $bits > $max_bits ) {
            return false;
        }

        $whole_bytes = intdiv( $bits, 8 );
        $remainder = $bits % 8;

        if ( $whole_bytes > 0 && strncmp( $ip_bin, $subnet_bin, $whole_bytes ) !== 0 ) {
            return false;
        }

        if ( 0 === $remainder ) {
            return true;
        }

        $mask = chr( 0xFF << ( 8 - $remainder ) & 0xFF );

        return ( $ip_bin[$whole_bytes] & $mask ) === ( $subnet_bin[$whole_bytes] & $mask );
    }

    /* ---------------------------------------------------------------------
     * Messaging
     * ------------------------------------------------------------------- */

    /**
     * Message shown to an address that is blocked for 24 hours.
     *
     * @return string
     */
    private function blocked_message() {
        return __( 'Access denied. This IP address has been blocked for 24 hours after repeated failed sign-in attempts.', 'admin-safety-guard' );
    }

    /**
     * Message shown to a temporarily locked-out address.
     *
     * Supports {minutes} in the admin-defined message; otherwise the remaining
     * time is appended.
     *
     * @param int $minutes_left Minutes until the lockout expires.
     * @return string
     */
    private function lockout_message( $minutes_left ) {
        $settings = $this->get_settings();
        $configured = isset( $settings['block-message'] ) ? trim( (string) $settings['block-message'] ) : '';

        if ( '' === $configured ) {
            $configured = __( 'You have been locked out due to too many failed sign-in attempts.', 'admin-safety-guard' );
        }

        $minutes_left = max( 1, (int) $minutes_left );

        if ( false !== strpos( $configured, '{minutes}' ) ) {
            return str_replace( '{minutes}', (string) $minutes_left, $configured );
        }

        return $configured . ' ' . sprintf(
            /* translators: %d: minutes until the lockout expires. */
            _n( 'Please try again in %d minute.', 'Please try again in %d minutes.', $minutes_left, 'admin-safety-guard' ),
            $minutes_left
        );
    }

    /**
     * Stop the request with a 403 and a plain explanation.
     *
     * @param string      $message Body text.
     * @param string|null $title   Optional page title.
     *
     * @return void
     */
    private function deny( $message, $title = null ) {
        $title = $title ? $title : __( 'Access Denied', 'admin-safety-guard' );

        nocache_headers();

        wp_die(
            esc_html( $message ),
            esc_html( $title ),
            ['response' => 403]
        );
    }

    /* ---------------------------------------------------------------------
     * Admin notice
     * ------------------------------------------------------------------- */

    /**
     * Warn the administrator when WP-Cron is disabled, since the daily cleanup
     * job is what releases 24-hour blocks.
     *
     * @since 1.0.0
     */
    public function check_wp_cron_status() {
        if ( !defined( 'DISABLE_WP_CRON' ) || true !== DISABLE_WP_CRON ) {
            return;
        }

        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        add_action(
            'admin_notices',
            static function () {
                echo '<div class="notice notice-warning is-dismissible"><p><strong>';
                esc_html_e( 'Admin Safety Guard:', 'admin-safety-guard' );
                echo '</strong> ';
                esc_html_e( 'WP-Cron is disabled on this site, so 24-hour IP blocks will not be released automatically. Configure a real server cron job that calls wp-cron.php, or remove the DISABLE_WP_CRON constant from wp-config.php.', 'admin-safety-guard' );
                echo '</p></div>';
            }
        );
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    /**
     * Whether a plugin table exists, cached per request.
     *
     * Guards against a fatal-free but broken state on sites where activation
     * could not create the tables (restrictive DB grants, migrations, etc.).
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

    /**
     * Truncated, sanitised User-Agent string.
     *
     * @return string
     */
    private function get_user_agent() {
        $agent = isset( $_SERVER['HTTP_USER_AGENT'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
        : '';

        if ( '' === $agent ) {
            return 'Unknown';
        }

        return substr( $agent, 0, 255 );
    }

    /**
     * Resolve the current client IP address.
     *
     * Delegates to the shared, spoofing-resistant helper which trusts only
     * REMOTE_ADDR unless the site has opted in to proxy headers via the
     * `tpsa_trust_proxy_headers` filter.
     *
     * @return string
     */
    private function get_ip_address() {
        return tpsa_get_client_ip();
    }

    /**
     * Returns the settings for this feature, cached per request.
     *
     * @return array
     */
    private function get_settings() {
        if ( null === $this->settings ) {
            $settings = get_option( get_tpsa_settings_option_name( $this->features_id ), [] );
            $this->settings = is_array( $settings ) ? $settings : [];
        }

        return $this->settings;
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

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
                            return new \WP_Error( 'access_denied', '🚫 You are temporarily blocked due to too many failed login attempts.' );
                        }
                        return $user;
                    },
                    0
                );
            }
        }
    }

    public function is_white_list_ip( $settings ) {
        $current_ip_address = $this->get_ip_address();
        $whitelist_ips = isset( $settings['whitelist-ip'] ) ? $settings['whitelist-ip'] : [];
        if ( empty( $whitelist_ips ) ) {
            return;
        }
        return in_array( $current_ip_address, $whitelist_ips, true );
    }

    public function maybe_block_custom_login() {
        if ( $this->is_permanently_blocked_ip() ) {
            // Only block access to login/registration pages
            if ( is_page( 'login' ) || is_page( 'register' ) || strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) !== false ) {
                wp_die(
                    '🚫 Access Denied – You have been permanently blocked for 1 day due to repeated login failures.',
                    'Access Denied',
                    ['response' => 403]
                );
            }
        } elseif ( $this->is_ip_locked_out() ) {
            global $wp;
            $current_path = $wp->request;

            // Only block login and register pages
            if ( is_page( 'login' ) || is_page( 'register' ) || strpos( $current_path, 'wp-login.php' ) !== false || strpos( $current_path, 'wp-admin' ) !== false ) {
                wp_die(
                    '🚫 Access Denied – You have been temporarily blocked due to too many failed login attempts. Please try again after 15 minutes.',
                    'Access Denied',
                    ['response' => 403]
                );
            }
        }
    }

    public function maybe_block_login_form() {
        $settings = $this->get_settings();
        $block_message = isset( $settings['block-message'] ) ? $settings['block-message'] : 'You have been permanently blocked due to repeated login failures.';
        $block_for = isset( $settings['block-for'] ) ? $settings['block-for'] : '15';

        if ( $this->is_permanently_blocked_ip() ) {
            if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) !== false ) {
                wp_die(
                    '🚫 Access Denied – You have been permanently blocked for 1 day due to repeated login failures.',
                    'Access Denied',
                    ['response' => 403]
                );
            }
        } elseif ( $this->is_ip_locked_out() ) {
            // Only show the lockout message if it's for login/registration page
            if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) !== false ) {
                wp_die(
                    esc_html( '🚫 Access Denied – ' . $block_message . '. Please try again after ' . $block_for . ' minutes.' ),
                    esc_html__( 'Access Denied', 'admin-safety-guard' ),
                    ['response' => 403]
                );

            }
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
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : 'Unknown';
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
                        $subject = 'A user has been blocked for 24 hours due to multiple failed login attempts.';
                        $body = "
                        Hi Admin,<br><br>
                        A user has been <b>blocked for 24 hours</b> due to repeated login failures.<br><br>
                        <b>Details:</b><br>
                        - IP Address: {$ip}<br>
                        - User Agent: {$user_agent}<br>
                        - Login Time: {$now}<br><br>
                        Please review the logs for further action if necessary.<br><br>
                        Thanks,<br>
                        Secure Admin Plugin
                    ";
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
            $lockout_ts = strtotime( $lockout_time );
            $now_ts = current_time( 'timestamp' );

            if ( ( $now_ts - $lockout_ts ) < ( $blocked_minute * 60 ) ) {
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
        $block_ip_lists = isset( $settings['block-ip-address'] ) ? $settings['block-ip-address'] : '';

        if ( empty( $block_ip_lists ) ) {
            return;
        }

        $current_ip_address = $this->get_ip_address();

        if ( in_array( $current_ip_address, $block_ip_lists, true ) ) {
            wp_die(
                '<h2 style="color:red;text-align:center;">' . esc_html( 'Your IP address is not permitted to log in to this site.' ) . '</h2>',
                'Login Blocked',
                ['response' => 403]
            );
            exit;
        }
    }

    private function get_ip_address() {
        if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $ip = sanitize_text_field( $ip );

        // Normalize IPv6 loopback to IPv4
        if ( $ip === '::1' ) {
            $ip = '127.0.0.1';
        }

        return $ip;
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
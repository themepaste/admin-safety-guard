<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: LimitLoginAttempts
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class LimitLoginAttempts implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['limit-login-attempts']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=limit-login-attempts
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'limit-login-attempts';

    /**
     * Registers the WordPress hooks for the HideAdminBar feature.
     *
     * Hooks the 'init' action to the 'hide_admin_bar' method.
     *
     * @since 1.0.0
     *
     * @return void
     */

    public function register_hooks() {
        $this->action( 'admin_init', [$this, 'check_wp_cron_status'] );
        $this->action( 'wp_login_failed', [$this, 'tpsa_track_failed_login_24hr']);
        $this->action( 'login_init', [ $this, 'hide_login_form_with_ip_address_status' ] );
        $this->action( 'login_init', [ $this, 'hide_login_form_with_login_attempts' ] );
    }

    public function check_wp_cron_status() {
        $settings = $this->get_settings();
        if ( ! $this->is_enabled( $settings ) ) {
            return;
        }

        // Check if the constant is defined before using it
        if ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === true) {
            add_action( 'admin_notices', function() {
                ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong><?php _e('Warning:', 'tp-secure-plugin'); ?></strong> <?php _e('WordPress Cron is currently disabled.', 'tp-secure-plugin'); ?></p>
                        <p><?php _e('Please check the following to resolve the issue:', 'tp-secure-plugin'); ?></p>
                        <ul>
                            <li><?php _e('Ensure the <code>DISABLE_WP_CRON</code> constant is <strong>not</strong> defined in your <code>wp-config.php</code> file. If it is, remove or comment out the line: <code>define(\'DISABLE_WP_CRON\', true);</code>', 'tp-secure-plugin'); ?></li>
                            <li><?php _e('Ensure your server cron is properly configured to trigger <code>wp-cron.php</code> periodically. You may need to set up a server-side cron job (using <code>cron</code> on Linux or Task Scheduler on Windows).', 'tp-secure-plugin'); ?></li>
                            <li><?php _e('If you\'re unsure how to configure the server cron, please consult your hosting provider for assistance.', 'tp-secure-plugin'); ?></li>
                        </ul>
                        <p><strong><?php _e('Note:', 'tp-secure-plugin'); ?></strong> <?php _e('This plugin will not function properly without a working cron job. The blocked users will not be unblocked automatically if the cron is not running.', 'tp-secure-plugin'); ?></p>
                    </div>
                <?php
            } );
        }
    }

    public function tpsa_track_failed_login_24hr( $username ) {
        global $wpdb;

        $settings       = $this->get_settings();
        if ( ! $this->is_enabled( $settings ) ) {
            return;
        }

        $ip             = $this->get_ip_address();
        $failed_logins  = get_option( 'tpsa_failed_logins', [] );
        $now            = time();
        $attempts       = isset( $failed_logins[$ip] ) ? $failed_logins[$ip] : [];

        // Filter out old attempts (older than 24 hours)
        $attempts = array_filter( $attempts, function( $entry ) use ( $now ) {
            return ( $now - $entry['time'] ) <= DAY_IN_SECONDS;
        });

        // Add current attempt
        $attempts[]         = [ 'time' => $now ];
        $failed_logins[$ip] = $attempts;
        update_option( 'tpsa_failed_logins', $failed_logins );

        $attempt_count = count( $attempts );
        $is_blocked    = false;

        if ( $attempt_count > $settings['max-attempts'] ) {
            $is_blocked = true;
            set_transient( 'tpsa_blocked_ip_' . $ip, true, $settings['block-for'] * 60 );
        }

        // INSERT or UPDATE failed_logins table
        $table = get_tpsa_db_table_name( 'failed_logins' );
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE ip_address = %s AND username = %s LIMIT 1",
                $ip, $username
            )
        );

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $login_time = current_time('mysql');
        $lockouts   = 0;

        if ( $existing ) {
            $lockouts = $existing->lockouts;
            $update_data = [
                'login_attempts' => $existing->login_attempts + 1,
                'login_time'     => $login_time,
                'user_agent'     => $user_agent
            ];

            if ( $is_blocked ) {
                $lockouts++;
                $update_data['lockouts'] = $lockouts;
            }

            $wpdb->update(
                $table,
                $update_data,
                [ 'id' => $existing->id ],
                [ '%d', '%s', '%s', isset($update_data['lockouts']) ? '%d' : null ],
                [ '%d' ]
            );
        } else {
            $lockouts = $is_blocked ? 1 : 0;

            $wpdb->insert(
                $table,
                [
                    'username'       => $username,
                    'user_agent'     => $user_agent,
                    'ip_address'     => $ip,
                    'login_time'     => $login_time,
                    'login_attempts' => 1,
                    'lockouts'       => $lockouts
                ],
                [ '%s', '%s', '%s', '%s', '%d', '%d' ]
            );
        }

        // ✅ If lockouts exceed the max-lockout setting → insert into block_users
        if ( $lockouts >= $settings['max-lockout'] ) {
            $block_table = get_tpsa_db_table_name( 'block_users' );

            // Check if IP is already in block_users
            $already_blocked = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $block_table WHERE ip_address = %s LIMIT 1",
                    $ip
                )
            );

            if ( ! $already_blocked ) {
                $wpdb->insert(
                    $block_table,
                    [
                        'user_agent' => $user_agent,
                        'ip_address' => $ip,
                        'login_time' => $login_time
                    ],
                    [ '%s', '%s', '%s' ]
                );
            }
        }
    }

    public function hide_login_form_with_ip_address_status() {
        $settings       = $this->get_settings();
        if ( ! $this->is_enabled( $settings ) ) {
            return;
        }
        $block_ip_lists = isset( $settings['block-ip-address'] ) ? $settings['block-ip-address'] : '';

        if( empty( $block_ip_lists ) ) {
            return;
        }
        $block_ip_lists = array_map( 'trim', explode( ',', $block_ip_lists ) );
        $current_ip_address = $this->get_ip_address();

        if ( in_array( $current_ip_address, $block_ip_lists ) ) {
            wp_die(
                '<h2 style="color:red;text-align:center;">' . esc_html( 'Your IP address is not permitted to log in to this site.' ) . '</h2>',
                'Login Blocked',
                [ 'response' => 403 ]
            );
            exit;
        }
    }

    
    public function hide_login_form_with_login_attempts() {
        global $wpdb;

        $settings = $this->get_settings();
        $ip       = $this->get_ip_address();
        $tpsa_ip  = get_transient( 'tpsa_blocked_ip_' . $ip );
        $block_message = $settings['block-message'];

        // ✅ Check permanent block from block_users table
        $block_table = get_tpsa_db_table_name( 'block_users' );
        $is_permanently_blocked = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $block_table WHERE ip_address = %s LIMIT 1",
                $ip
            )
        );

        // Show error if permanently or temporarily blocked
        if ( $tpsa_ip || $is_permanently_blocked ) {
            $message = $is_permanently_blocked
                ? 'Access Denied - You have been permanently blocked due to too many login failures & lockouts.'
                : 'Access Denied for ' . $settings['block-for'] . ' minutes';

            wp_die(
                '<h2 style="color:red;text-align:center;">' . esc_html( $message ) . '</h2>
                <p style="text-align:center;">' . esc_html( $block_message ) . '</p>',
                'Login Blocked',
                [ 'response' => 403 ]
            );
            exit;
        }
    }


    private function get_ip_address() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            return sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            return sanitize_text_field( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0] );
        }

        return sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
    }

    /**
     * Get plugin settings.
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->features_id );
        return get_option( $option_name, [] );
    }

    /**
     * Check if the feature is enabled.
     */
    private function is_enabled( $settings ) {
        return isset( $settings['enable'] ) && $settings['enable'] == 1;
    }
}
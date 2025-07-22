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
        $this->action( 'wp_login_failed', [$this, 'tpsa_track_failed_login_24hr']);
        $this->action( 'login_init', [ $this, 'maybe_hide_login_form' ] );
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

        // Count attempts
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

        if ( $existing ) {
            // Update login_attempts and maybe lockouts
            $update_data = [
                'login_attempts' => $existing->login_attempts + 1,
                'login_time'     => $login_time,
                'user_agent'     => $user_agent
            ];

            if ( $is_blocked ) {
                $update_data['lockouts'] = $existing->lockouts + 1;
            }

            $wpdb->update(
                $table,
                $update_data,
                [ 'id' => $existing->id ],
                [ '%d', '%s', '%s', isset($update_data['lockouts']) ? '%d' : null ],
                [ '%d' ]
            );
        } else {
            // Insert first attempt
            $wpdb->insert(
                $table,
                [
                    'username'       => $username,
                    'user_agent'     => $user_agent,
                    'ip_address'     => $ip,
                    'login_time'     => $login_time,
                    'login_attempts' => 1,
                    'lockouts'       => $is_blocked ? 1 : 0
                ],
                [ '%s', '%s', '%s', '%s', '%d', '%d' ]
            );
        }
    }

    public function maybe_hide_login_form() {
        $settings = $this->get_settings();
        $ip       = $this->get_ip_address();
        $tpsa_ip = get_transient( 'tpsa_blocked_ip_' . $ip );
        $block_message = $settings['block-message'];

        if ( $tpsa_ip ) {
            wp_die(
                '<h2 style="color:red;text-align:center;">
                    Access Denied
                </h2>
                <p style="text-align:center;">
                    ' . $block_message . '
                </p>',
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
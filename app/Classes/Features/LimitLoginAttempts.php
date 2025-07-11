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
        $settings       = $this->get_settings();
        if( $this->is_enabled( $settings ) ) {

            $ip             = $this->get_ip_address();
            $failed_logins  = get_option( 'tpsa_failed_logins', [] );
            $now            = time();
            $attempts       = isset( $failed_logins[$ip] ) ? $failed_logins[$ip] : [];
    
            // Remove entries older than 24 hours
            $attempts = array_filter( $attempts, function( $entry ) use ( $now ) {
                return ( $now - $entry['time'] ) <= DAY_IN_SECONDS;
            });
    
            // Add current attempt
            $attempts[]         = ['time' => $now];
            $failed_logins[$ip] = $attempts;
    
            update_option( 'tpsa_failed_logins', $failed_logins );
    
            // Check if over limit
            if ( count( $attempts ) > $settings['max-attempts'] ) {
                set_transient( 'tpsa_blocked_ip_' . $ip, true, $settings['block-for'] * 60 );
            }
        }
    }

    public function maybe_hide_login_form() {
        $settings = $this->get_settings();
        $ip       = $this->get_ip_address();

        if ( get_transient( 'tpsa_blocked_ip_' . $ip ) ) {
            wp_die(
                '<h2 style="color:red;text-align:center;">
                    Access Denied
                </h2>
                <p style="text-align:center;">
                    You are temporarily blocked from accessing the login page.
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
     * Hides the admin bar.
     *
     * Checks the settings for the feature and if the feature is enabled, it filters the 'show_admin_bar' hook
     * to disable the admin bar. If the 'disable-for-admin' option is set to true, it only disables the admin bar
     * for non-admin users.
     *
     * @since 1.0.0
     *
     * @return void
     */
    // public function hide_admin_bar() {
    //     $settings       = $this->get_settings();
    //     if( $this->is_enabled( $settings ) ) {
            
    //     }
    // }

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
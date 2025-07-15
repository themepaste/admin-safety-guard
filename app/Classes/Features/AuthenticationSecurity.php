<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: AuthenticationSecurity
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class AuthenticationSecurity implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['authentication-security']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=authentication-security
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'authentication-security';

    /**
     * Registers the WordPress hooks for the HideAdminBar feature.
     *
     * Hooks the 'init' action to the 'authentication-security' method.
     *
     * @since 1.0.0
     *
     * @return void
     */

    public function register_hooks() {
        $this->action( 'init', [$this, 'hide_admin_bar']);
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
    public function hide_admin_bar() {
        $settings = $this->get_settings();
        if( $this->is_enabled( $settings ) ) {
            
        }
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
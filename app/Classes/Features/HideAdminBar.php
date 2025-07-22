<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: HideAdminBar
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class HideAdminBar implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['admin-bar']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=admin-bar
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'admin-bar';

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
            $exclude = isset( $settings['exclude'] ) ? $settings['exclude'] : [];

            if ( is_array( $exclude ) && ! empty( $exclude ) ) {
                
                $this->filter( 'show_admin_bar', function( $show ) use ( $exclude ) {
                    $user = wp_get_current_user();
                    $user_roles = (array) $user->roles;

                    foreach ( $user_roles as $role ) {
                        if ( in_array( $role, $exclude ) ) {
                            return $show;
                        }
                    }

                    // For everyone else, hide admin bar
                    return false;
                } );

            } else {
                $this->filter( 'show_admin_bar', '__return_false' );
            }
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
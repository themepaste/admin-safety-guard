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
     * - Settings array: tpsa_settings_fields()['hide-admin-bar']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=hide-admin-bar
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'hide-admin-bar';

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
        $option_name    = get_tpsa_settings_option_name( $this->features_id );
        $settings       = get_option( $option_name, [] );
        if( ! empty( $settings ) && is_array( $settings ) ) {
            if( isset( $settings['enable'] ) && $settings['enable'] == 1 ) {
                if( isset( $settings['disable-for-admin'] ) && $settings['disable-for-admin'] == 1 ) {
                    $this->filter( 'show_admin_bar', function( $show ) {
                        if ( ! current_user_can( 'administrator' ) ) {
                            return false;
                        }
                        return $show;
                    } );
                }else {
                    $this->filter( 'show_admin_bar', '__return_false');
                }
            }
        }
    }
}
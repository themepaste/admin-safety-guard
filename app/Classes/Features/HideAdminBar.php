<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class HideAdminBar implements FeatureInterface {

    use Hook;

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
     * Hide the admin bar when the feature is enabled.
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
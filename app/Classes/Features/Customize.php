<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: Customize
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class Customize implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['customize']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=customize
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'customize';

    /**
     * Registers the WordPress hooks for the HideAdminBar feature.
     *
     * Hooks the 'init' action to the 'customize' method.
     *
     * @since 1.0.0
     *
     * @return void
     */

    public function register_hooks() {
        $this->action( 'init', [$this, 'customize']);
        $this->action( 'login_enqueue_scripts', [$this, 'customize_login_form']);
        $this->action( 'register_form', [$this, 'customize_registration_form']);
    }

    /**
     * Customize login form logo
     */
    public function customize_login_form() {
        $settings = $this->get_settings();
        if( $this->is_enabled( $settings ) ) {
            // Retrieve the settings
            $logo = isset( $settings['logo'] ) ? $settings['logo'] : '';
            $logo_url = isset( $settings['logo-url'] ) ? $settings['logo-url'] : '';
            $logo_width = isset( $settings['logo-width'] ) ? $settings['logo-width'] : '';
            $logo_height = isset( $settings['logo-height'] ) ? $settings['logo-height'] : '';

            // Inject the custom logo into the login page
            if ( ! empty( $logo ) ) {
                echo '<style>
                    #login h1 a { 
                        background-image: url(' . esc_url( $logo ) . ') !important;
                        background-size: ' . esc_attr( $logo_width ) . 'px ' . esc_attr( $logo_height ) . 'px !important;
                        width: ' . esc_attr( $logo_width ) . 'px !important;
                        height: ' . esc_attr( $logo_height ) . 'px !important;
                    }
                </style>';
            }

            // Optionally, change the logo URL if provided
            if ( ! empty( $logo_url ) ) {
                add_filter( 'login_headerurl', function() use ( $logo_url ) {
                    return esc_url( $logo_url );
                });
            }
        }
    }

    /**
     * Customize registration form logo
     */
    public function customize_registration_form() {
        $settings = $this->get_settings();
        if( $this->is_enabled( $settings ) ) {
            // Retrieve the settings
            $logo = isset( $settings['logo'] ) ? $settings['logo'] : '';
            $logo_url = isset( $settings['logo-url'] ) ? $settings['logo-url'] : '';
            $logo_width = isset( $settings['logo-width'] ) ? $settings['logo-width'] : '';
            $logo_height = isset( $settings['logo-height'] ) ? $settings['logo-height'] : '';

            // Inject the custom logo into the registration page
            if ( ! empty( $logo ) ) {
                echo '<style>
                    .register h1 a { 
                        background-image: url(' . esc_url( $logo ) . ') !important;
                        background-size: ' . esc_attr( $logo_width ) . 'px ' . esc_attr( $logo_height ) . 'px !important;
                        width: ' . esc_attr( $logo_width ) . 'px !important;
                        height: ' . esc_attr( $logo_height ) . 'px !important;
                    }
                </style>';
            }

            // Optionally, change the logo URL if provided
            if ( ! empty( $logo_url ) ) {
                add_filter( 'login_headerurl', function() use ( $logo_url ) {
                    return esc_url( $logo_url );
                });
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
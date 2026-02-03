<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: PrivacyHardening
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class PrivacyHardening implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['privacy-hardening']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=privacy-hardening
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'privacy-hardening';

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
        $this->action( 'init', [$this, 'disable_XML_RPC'] );
    }

    public function disable_XML_RPC() {
        $settings = $this->get_settings();
        if ( $this->is_enabled( $settings, 'xml-rpc-enable' ) ) {

            $this->filter( 'xmlrpc_enabled', '__return_false' );

            if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && basename( $_SERVER['SCRIPT_FILENAME'] ) === 'xmlrpc.php' ) {
                header( 'HTTP/1.1 403 Forbidden' );
                header( 'Content-Type: text/plain; charset=utf-8' );
                esc_html_e( 'XML-RPC disabled by site admin.', 'admin-safety-guard' );
                exit;
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
    private function is_enabled( $settings, $key = 'enable' ) {
        return isset( $settings[$key] ) && $settings[$key] == 1;
    }
}
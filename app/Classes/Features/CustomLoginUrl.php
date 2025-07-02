<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: CustomLoginUrl
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class CustomLoginUrl implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['custom-login-url']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=custom-login-url
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'custom-login-url';

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
        $this->action( 'init', [$this, 'custom_login_url']);
        $this->filter( 'tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_url_field'], 10, 2 );
    }

    /**
     * Modifies the custom login URL field to include the site URL.
     *
     * Prepend the site URL to the field template to provide a better user experience.
     *
     * @since 1.0.0
     *
     * @param string $template The field template.
     * @param array  $args     The field args.
     *
     * @return string The modified field template.
     */
    public function modify_the_custom_login_url_field( $template, $args ) {

        $site_url = get_site_url();

        $template = str_replace(
			'<input type="text" id="%2$s" name="%2$s" value="%3$s">',
			$site_url . '/<input type="text" id="%2$s" name="%2$s" value="%3$s">',
			$template
		);


        return $template;
    }

    
    public function custom_login_url() {
        $option_name    = get_tpsa_settings_option_name( $this->features_id );
        $settings       = get_option( $option_name, [] );

        if( ! empty( $settings ) && is_array( $settings ) ) {
            if( isset( $settings['enable'] ) && $settings['enable'] == 1 ) {
                
            }
        }
    }
}
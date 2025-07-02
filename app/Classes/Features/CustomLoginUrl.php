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

    private $custom_login_path = '';

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
			$site_url . '/<input type="text" id="%2$s" name="%2$s" value="%3$s">' . get_tpsa_site_login_path(),
			$template
		);

        return $template;
    }

    
    public function custom_login_url() {
        $option_name    = get_tpsa_settings_option_name( $this->features_id );
        $settings       = get_option( $option_name, [] );

        if( ! empty( $settings ) && is_array( $settings ) ) {
            if( isset( $settings['enable'] ) && $settings['enable'] == 1 ) {
                if( isset( $settings['login-url'] ) && ! empty( $settings['login-url'] ) ) {
                    $this->custom_login_path = $settings['login-url'];
                    $this->register_custom_login_url( $this->custom_login_path );
                }
            }
        }
    }

    private function register_custom_login_url( $custom_login_path ) {
        if( empty( $custom_login_path ) ) {
            return;
        }
        $this->action( 'init', [$this, 'tpsa_custom_login_rewrite'], 1 );
        $this->action( 'init', [$this, 'return_404_for_wp_login'], 1 );
        $this->action( 'init', [$this, 'return_404_for_wp_admin'] );
        $this->filter( 'login_url', [$this, 'change_login_url'], 10, 3 );
    }


    public function tpsa_custom_login_rewrite() {
        if( empty( $this->custom_login_path ) ) {
            return;
        }

         global $pagenow;

        // Block direct wp-login.php access (except logout)
        if (
            strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false &&
            ( !isset($_GET['action']) || $_GET['action'] !== 'logout' ) &&
            !is_admin()
        ) {
            wp_redirect( home_url( $this->custom_login_path ) );
            exit;
        }

        // If visiting /login, load wp-login.php
        if ( trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/') === $this->custom_login_path ) {
            global $user_login, $error, $interim_login, $redirect_to;

            // Initialize variables expected by wp-login.php
            $user_login    = isset( $_GET['user_login'] ) ? sanitize_user( $_GET['user_login'] ) : '';
            $error         = '';
            $interim_login = false;
            $redirect_to   = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : '';

            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    public function return_404_for_wp_login() {
        if (
            strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false &&
            (!isset($_GET['action']) || $_GET['action'] !== 'logout')
        ) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_404_template(); // or use custom template
            exit;
        }
    }

    public function return_404_for_wp_admin() {
        if (
            is_admin() &&
            !is_user_logged_in() &&
            !( defined('DOING_AJAX' ) && DOING_AJAX)
        ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
            include get_404_template(); // or use a custom page if desired
            exit;
        }
    }

    public function change_login_url( $login_url, $redirect, $force_reauth ) {

        if( empty( $this->custom_login_path ) ) {
            return $login_url;
        }

        $custom_url = home_url( $this->custom_login_path );

        if ( !empty( $redirect ) ) {
            $custom_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom_url );
        }

        if ( $force_reauth ) {
            $custom_url = add_query_arg( 'reauth', '1', $custom_url );
        }

        return $custom_url;
    }
}
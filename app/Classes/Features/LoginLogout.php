<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined('ABSPATH') || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class LoginLogout implements FeatureInterface
{
    use Hook;

    private $features_id        = 'custom-login-url';
    private $custom_login_slug  = ''; 
    private $redirect_slug      = ''; 
    private $logout_url         = ''; 

    public function register_hooks() {
        $settings       = $this->get_settings();

        // Get the custom login slug from settings, fallback to empty string
        $this->custom_login_slug = !empty( $settings['login-url'] ) ? trim( $settings['login-url'], '/' ) : '';
        $this->redirect_slug = !empty( $settings['redirect-url'] ) ? trim( $settings['redirect-url'], '/' ) : '';
        $this->logout_url = !empty( $settings['logout-url'] ) ? trim( $settings['logout-url'], '/' ) : '';

        if( $this->is_enabled( $settings ) && !empty( $this->custom_login_slug ) ) {
            $this->action( 'init', [$this, 'rewrite_url'] );
            $this->action( 'init', [$this, 'show_404'] );
            $this->action( 'init', [$this, 'redirect_wp_admin'] );
            $this->action( 'template_redirect', [$this, 'force_custom_login_url'] );
    
            $this->filter( 'site_url', [$this, 'override_site_url'], 10, 4 );
        }

        if( $this->is_enabled( $settings ) && !empty( $this->logout_url ) ) {
            $this->filter( 'logout_redirect', [$this, 'logout_redirect'], 10, 3 );
        }

        $this->filter( 'tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_logout-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_redirect-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
    }

    /**
     * Override all WordPress-generated login URLs (login, register, lost password, etc.)
     */
    public function override_site_url( $url, $path, $scheme, $blog_id ) {
        if ( strpos( $url, 'wp-login.php' ) !== false ) {
            $url = str_replace( 'wp-login.php', $this->custom_login_slug, $url );
        }
        return $url;
    }

    /**
     * Redirect /wp-admin to home if not logged in
     */
    function redirect_wp_admin() {
        $request_uri = $_SERVER['REQUEST_URI'];
        $admin_path = parse_url( admin_url(), PHP_URL_PATH ); // This gives the correct path to wp-admin
        error_log( 'Request URI: ' . $request_uri );
        error_log( 'Admin path: ' . $admin_path );

        if ( strpos( $request_uri, $admin_path ) === 0 && !is_user_logged_in() ) {
            $redirect_url = home_url( $this->redirect_slug );
            error_log( 'Redirecting to: ' . $redirect_url );
            wp_redirect( $redirect_url );
            exit;
        }
    }


    /**
     * Show 404 if /wp-login.php is accessed
     */
    public function show_404() {
        // Only proceed if custom slug is set
        if (
            !empty($this->custom_login_slug) &&
            strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false &&
            strpos( $_SERVER['REQUEST_URI'], '/' . $this->custom_login_slug ) === false
        ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();

            $template_404 = get_404_template();
            if ( $template_404 && file_exists( $template_404 ) ) {
                include $template_404;
            } else {
                // Fallback plain 404 message
                wp_die( '404 - Page not found.' );
            }

            exit;
        }
    }




    /**
     * Rewrite /habib-login → wp-login.php with all query args
     */
    public function rewrite_url() {
        add_rewrite_rule( '^' . $this->custom_login_slug . '/?$', 'wp-login.php', 'top' );
    }

    /**
     * Force WordPress to parse /habib-login as wp-login.php
     */
    public function force_custom_login_url() {
        $request_uri = $_SERVER['REQUEST_URI'];

        // Normalize custom login slug match
        if ( preg_match( '#^/' . $this->custom_login_slug . '(/|\?|$)#', $request_uri ) ) {

            // Define missing expected variables to avoid PHP warnings
            global $user_login, $error;

            // Set them to empty string if not set
            if (!isset($user_login)) {
                $user_login = '';
            }

            if (!isset($error)) {
                $error = '';
            }
            
            require ABSPATH . 'wp-login.php';
            exit;
        }
    }

    public function logout_redirect($redirect_to, $requested_redirect_to, $user) {
        // Check if logout URL is set in settings
        $settings = $this->get_settings();
        if ( !empty( $settings['logout-url'] ) ) {
            return home_url( $settings['logout-url'] );
        }

        return $redirect_to; // Fallback to default redirect
    }

    private function get_settings()
    {
        $option_name = get_tpsa_settings_option_name($this->features_id);
        return get_option($option_name, []);
    }

    private function is_enabled($settings)
    {
        return isset($settings['enable']) && $settings['enable'] == 1;
    }

    /**
     * Add site URL in login/logout input
     */
    public function modify_the_custom_login_logout_url_field( $template, $args ){
        $site_url = get_site_url();
        $template = str_replace(
            '<input type="text" id="%2$s" name="%2$s" value="%3$s">',
            '<span>' . $site_url . '/</span><input type="text" id="%2$s" name="%2$s" value="%3$s">',
            $template
        );
        return $template;
    }
}

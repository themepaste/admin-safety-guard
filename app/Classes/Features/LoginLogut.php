<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined('ABSPATH') || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class LoginLogut implements FeatureInterface
{
    use Hook;

    private $features_id = 'custom-login-url';

    public function register_hooks()
    {
        $this->filter('logout_redirect', [$this, 'logout_redirect'], 10, 3);
        $this->filter('tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2);
        $this->filter('tpsa_custom-login-url_logout-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2);

        $settings       = $this->get_settings();
        define('CUSTOM_LOGIN_SLUG', 'habib-login');
        if( ! $this->is_enabled( $settings ) ) {
            return;
        }

        $this->action( 'init', [$this, 'rewrite_url'] );
        $this->action( 'init', [$this, 'show_404'] );
        $this->action( 'init', [$this, 'redirect_wp_admin'] );
        $this->action( 'template_redirect', [$this, 'force_custom_login_url'] );

        $this->filter( 'site_url', [$this, 'override_site_url'], 10, 4 );

    }

    /**
     * Override all WordPress-generated login URLs (login, register, lost password, etc.)
     */
    public function override_site_url( $url, $path, $scheme, $blog_id ) {
        if ( strpos( $url, 'wp-login.php' ) !== false ) {
            $url = str_replace( 'wp-login.php', CUSTOM_LOGIN_SLUG, $url );
        }
        return $url;
    }

    /**
     * Redirect /wp-admin to home if not logged in
     */
    function redirect_wp_admin() {
        if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') === 0 && !is_user_logged_in()) {
            wp_redirect(home_url('/'));
            exit;
        }
    }

    /**
     * Show 404 if /wp-login.php is accessed
     */
    public function show_404() {
        if (
            strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false &&
            strpos($_SERVER['REQUEST_URI'], '/' . CUSTOM_LOGIN_SLUG) === false
        ) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_404_template();
            exit;
        }
    }

    /**
     * Rewrite /habib-login → wp-login.php with all query args
     */
    public function rewrite_url() {
        add_rewrite_rule('^' . CUSTOM_LOGIN_SLUG . '/?$', 'wp-login.php', 'top');
    }

    /**
     * Force WordPress to parse /habib-login as wp-login.php
     */
    public function force_custom_login_url() {
        $request_uri = $_SERVER['REQUEST_URI'];

        // Normalize custom login slug match
        if (preg_match('#^/' . CUSTOM_LOGIN_SLUG . '(/|\?|$)#', $request_uri)) {
            require ABSPATH . 'wp-login.php';
            exit;
        }
    }

    /**
     * Logout redirect to custom URL
     */
    public function logout_redirect($redirect_to, $requested_redirect_to, $user)
    {
        $settings       = $this->get_settings();
        $redirect_to    = $settings['logout-url'];
        if( $this->is_enabled( $settings ) ) {
            return home_url( $redirect_to );
        }

        return $redirect_to;
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
    public function modify_the_custom_login_logout_url_field($template, $args)
    {
        $site_url = get_site_url();
        $template = str_replace(
            '<input type="text" id="%2$s" name="%2$s" value="%3$s">',
            '<span>' . $site_url . '/</span><input type="text" id="%2$s" name="%2$s" value="%3$s">',
            $template
        );
        return $template;
    }
}

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
        $this->action('init', [$this, 'add_custom_rewrite_rule']);
        $this->action('template_redirect', [$this, 'block_wp_admin_access']);
        $this->action('login_init', [$this, 'block_direct_login_access']);
        $this->filter('logout_redirect', [$this, 'logout_redirect'], 10, 3);
        $this->filter('tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2);
        $this->filter('tpsa_custom-login-url_logout-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2);
        $this->action('admin_init', [$this, 'maybe_flush_rules']);
    }

    /**
     * Add a rewrite rule for the custom login slug
     */
    public function add_custom_rewrite_rule()
    {
        $settings = $this->get_settings();
        if ($this->is_enabled($settings) && !empty($settings['login-url'])) {
            add_rewrite_rule("^" . preg_quote($settings['login-url'], '/') . "/?", 'wp-login.php', 'top');
        }
    }

    /**
     * Flush rules on settings update
     */
    public function maybe_flush_rules()
    {
        if (isset($_GET['tpsa_flush_rules'])) {
            flush_rewrite_rules();
        }
    }

    /**
     * Block direct access to wp-login.php unless via custom slug
     */
    public function block_direct_login_access()
    {
        $settings = $this->get_settings();
        if (!$this->is_enabled($settings)) {
            return;
        }

        $custom_slug = trim($settings['login-url'], '/');
        $parsed_custom_path = trim(parse_url(site_url($custom_slug), PHP_URL_PATH), '/');
        $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        // If accessing wp-login.php directly (not via rewrite rule), block it
        if ($request_path === 'wp-login.php' && $parsed_custom_path !== 'wp-login.php') {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_404_template();
            exit;
        }
    }

    /**
     * Block /wp-admin access for non-logged-in users
     */
    public function block_wp_admin_access()
    {
        if (is_user_logged_in()) {
            return;
        }

        $settings = $this->get_settings();
        if (!$this->is_enabled($settings)) {
            return;
        }

        $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        // If accessing /wp-admin and not logged in
        if (strpos($request_path, 'wp-admin') === 0) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_404_template();
            exit;
        }
    }

    /**
     * Logout redirect to custom URL
     */
    public function logout_redirect($redirect_to, $requested_redirect_to, $user)
    {
        $settings = $this->get_settings();
        if ($this->is_enabled($settings) && !empty($settings['logout-url'])) {
            return home_url($settings['logout-url']);
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
     * Show full URL in settings fields
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

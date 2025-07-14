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

        // Login redirect functionality
        $this->action('init', [$this, 'add_rewrite_rule']);
        $this->filter('query_vars', [$this, 'add_query_vars']);
        $this->action('template_redirect', [$this, 'handle_custom_login']);
        $this->action('login_init', [$this, 'maybe_block_default_login']);
        $this->action('init', [$this, 'block_wp_admin'], 1);
        $this->action('init', [$this, 'maybe_flush_rules']);
    }

    /**
     * Add rewrite rule for custom login
     */
    public function add_rewrite_rule()
    {
        $settings = $this->get_settings();
        if (! $this->is_enabled($settings) || empty($settings['login-url'])) {
            return;
        }

        add_rewrite_rule("^{$settings['login-url']}/?$", 'index.php?custom_login=1', 'top');
    }

    /**
     * Add custom query var
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'custom_login';
        return $vars;
    }

    /**
     * Handle login form rendering
     */
    public function handle_custom_login()
    {
        if (get_query_var('custom_login')) {
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    /**
     * Block default login access
     */
    public function maybe_block_default_login()
    {
        $settings = $this->get_settings();
        if (! $this->is_enabled($settings) || empty($settings['login-url'])) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'];

        if (strpos($request_uri, $settings['login-url']) !== false) {
            return;
        }

        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include get_query_template('404');
        exit;
    }

    /**
     * Block wp-admin for unauthenticated users
     */
    public function block_wp_admin()
    {
        if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX')) {
            wp_redirect(home_url('/404'));
            exit;
        }
    }

    /**
     * Trigger flush rewrite rules if necessary
     */
    public function maybe_flush_rules()
    {
        if (get_option('_tpsa_flush_login_rewrite') !== 'yes') {
            flush_rewrite_rules();
            update_option('_tpsa_flush_login_rewrite', 'yes');
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

    private function get_settings()
    {
        $option_name = get_tpsa_settings_option_name($this->features_id);
        return get_option($option_name, []);
    }

    private function is_enabled($settings)
    {
        return isset($settings['enable']) && $settings['enable'] == 1;
    }
}

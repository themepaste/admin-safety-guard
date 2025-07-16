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

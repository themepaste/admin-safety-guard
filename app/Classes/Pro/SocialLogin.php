<?php

namespace ThemePaste\SecureAdmin\Classes\Pro;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class SocialLogin implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference.
     *
     * @since 1.0.0
     * @var string
     */
    private $feature_id = 'social-login';

    public function register_hooks() {
        $this->filter( 'tpsa_settings_option', [$this, 'extend_pro_settings'] );
        $this->filter( 'tpsa_settings_fields', [$this, 'extend_pro_fields'] );
    }

    /**
     * Extends the settings array with a new option for social login.
     *
     * @param array $settings The settings array to extend.
     *
     * @return array The extended settings array.
     */
    public function extend_pro_settings( $settings ) {
        $settings['social-login'] = [
            'label'  => __( 'Social Login', 'tp-secure-plugin' ),
            'class'  => '',
            'is_pro' => true,
        ];

        return $settings;
    }

    /**
     * Extends the settings fields with a new field for social login.
     *
     * Adds a field to the settings array: 'social-logins'. The 'social-logins' field allows
     * the user to enable or disable providers for login.
     *
     * @param array $fields The settings fields array to extend.
     *
     * @return array The extended settings fields array.
     */
    public function extend_pro_fields( $fields ) {

        // Social Login
        $fields['social-login']['fields']['social-logins'] = array(
            'type'    => 'social-login',
            'label'   => __( 'Social Login', 'tp-secure-plugin' ),
            'class'   => '',
            'id'      => '',
            'desc'    => __( 'Enable or disable providers for login.', 'tp-secure-plugin' ),
            'default' => array(),
            'options' => array(
                'google'   => array(
                    'label' => 'Google',
                    'desc'  => 'Enable Google sign-in',
                    'logo'  => tp_svg_google(),
                ),
                'facebook' => array(
                    'label' => 'Facebook',
                    'desc'  => 'Enable Facebook login',
                    'logo'  => tp_svg_facebook(),
                ),
                'linkedin' => array(
                    'label' => 'LinkedIn',
                    'desc'  => 'Enable LinkedIn login',
                    'logo'  => tp_svg_linkedin(),
                ),
            ),
        );

        return $fields;
    }

}
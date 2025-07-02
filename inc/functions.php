<?php 

if ( ! function_exists( 'tpsa_settings_option' ) ) {
/**
 * Returns an associative array of features available for the Secure Admin plugin.
 *
 * The array is composed of feature slugs as keys and arrays of 'label' and 'class' as values.
 *
 * @since 1.0.0
 *
 * @filter tpsa_features_lists
 *
 * @return array Associative array of available features.
 */
    function tpsa_settings_option() {
        return apply_filters(
            'tpsa_settings_option',
            array(
                'dashboard' => array(
                    'label' => __( 'Dashboard', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'hide-admin-bar' => array(
                    'label' => __( 'Hide Admin Bar', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'custom-login-url' => array(
                    'label' => __( 'Custom URL', 'tp-secure-plugin' ),
                    'class' => '',
                ),
            )
        );
    }
}

if( ! function_exists( 'tpsa_settings_fields' ) ) {
/**
 * Returns an associative array of settings fields available for the Secure Admin plugin.
 *
 * The array is composed of feature slugs as keys and arrays of 'fields' as values.
 * Each 'fields' array is composed of field slugs as keys and arrays of 'type', 'label', 'class', 'id', and 'desc' as values.
 *
 * @since 1.0.0
 *
 * @filter tpsa_settings_fields
 *
 * @return array Associative array of available settings fields.
 */
    function tpsa_settings_fields() {
        return apply_filters(
            'tpsa_settings_fields',
            array(
                'dashboard' => array(
                    
                ),
                'hide-admin-bar' => array(
                    'fields' => array(
                        'enable' => array(
                            'type'  => 'switch',
                            'label' => __( 'Enable', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  =>  __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 0
                        ),
                        'disable-for-admin' => array(
                            'type'  => 'switch',
                            'label' => __( 'Disable for Administrator', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  =>  __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 1
                        )
                    )
                ),
                'custom-login-url' => array(
                    'fields' => array(
                        'enable' => array(
                            'type'  => 'switch',
                            'label' => __( 'Enable', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'login-url' => array(
                            'type'  => 'text',
                            'label' => __( 'Login Url', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Protect your website by changing the login page URL.', 'tp-secure-plugin' ),
                            'default' => get_tpsa_site_login_path(),
                        ),

                    )
                ),
            )
        );
    }
}

if(  ! function_exists( 'get_tpsa_site_login_path' ) ) {
    /**
     * Returns the relative path of the WordPress login URL.
     *
     * @since 1.0.0
     *
     * @return string The relative path of the WordPress login URL.
     */
    function get_tpsa_site_login_path() {
        $site_url  = get_site_url();
        $login_url = wp_login_url();
        $relative_path = str_replace( trailingslashit( $site_url ), '', trailingslashit( $login_url ) );

        return $relative_path;
    }
}

if( ! function_exists( 'get_tpsa_prefix' ) ) {
    /**
     * Returns the prefix used for options and other identifiers throughout the plugin.
     *
     * @since 1.0.0
     *
     * @return string The prefix used by the plugin.
     */
    function get_tpsa_prefix() {
        return 'tpsa_';
    }
}

/**
 * Returns the option name used to store the settings for a given screen slug.
 *
 * @since 1.0.0
 *
 * @param string $screen_slug The screen slug.
 *
 * @return string The option name used to store the settings.
 */
function get_tpsa_settings_option_name( $screen_slug ) {
    return get_tpsa_prefix() . $screen_slug . '_settings';
}
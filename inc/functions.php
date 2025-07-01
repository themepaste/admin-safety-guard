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
                            'label' => __( 'Hide Admin Bar', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => ''
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
                            'desc'  => __( 'To enable/disable this feature.', 'tp-secure-plugin' )
                        ),
                        'login-url' => array(
                            'type'  => 'text',
                            'label' => __( 'Login Url', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Protect your website by changing the login page URL.', 'tp-secure-plugin' )
                        ),

                    )
                ),
            )
        );
    }
}
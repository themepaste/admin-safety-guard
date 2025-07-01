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
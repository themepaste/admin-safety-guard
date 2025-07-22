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
                'analytics' => array(
                    'label' => __( 'Analytics', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'admin-bar' => array(
                    'label' => __( 'Hide Admin Bar', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'custom-login-url' => array(
                    'label' => __( 'Login/Logout', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'limit-login-attempts' => array(
                    'label' => __( 'Limit Login Attempts', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'login-logs-activity' => array(
                    'label' => __( 'Login Logs & Activity', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'recaptcha' => array(
                    'label' => __( 'reCAPTCHA', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'two-factor-auth' => array(
                    'label' => __( 'Two Factor Auth', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'password-protection' => array(
                    'label' => __( 'Password Protection', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'customize' => array(
                    'label' => __( 'Customize', 'tp-secure-plugin' ),
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
                'analytics' => array(
                    
                ),
                'admin-bar' => array(
                    'fields' => array(
                        'enable' => array(
                            'type'  => 'switch',
                            'label' => __( 'Hide Admin Bar', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  =>  __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 0
                        ),
                        'exclude' => array(
                            'type'  => 'multi-check',
                            'label' => __( 'Exclude', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Exclude user this features', 'tp-secure-plugin' ),
                            'default' => 'light',
                            'options' => get_tps_all_user_roles()
                        ),
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
                            'label' => __( 'Custom Login Url', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Protect your website by changing the login page URL.', 'tp-secure-plugin' ),
                            'default' => get_tpsa_site_login_path(),
                        ),
                        'redirect-url' => array(
                            'type'  => 'text',
                            'label' => __( 'Redirect URL', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Wp Admin redirect URL. Default: home_url()', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                        'logout-url' => array(
                            'type'  => 'text',
                            'label' => __( 'Redirect URL After Logout', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Redirect URL After Logout', 'tp-secure-plugin' ),
                            'default' => get_tpsa_site_login_path(),
                        ),

                    )
                ),
                'limit-login-attempts' => array(
                    'fields' => array(
                        'enable' => array(
                            'type'  => 'switch',
                            'label' => __( 'Enable', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'max-attempts' => array(
                            'type'  => 'number',
                            'label' => __( 'Max Login Attempts', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Maximum number of login attempts within 1 day for temporary block IP/user.', 'tp-secure-plugin' ),
                            'default' => 3,
                        ),
                        'block-for' => array(
                            'type'  => 'number',
                            'label' => __( 'Lock for', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Add How many munite will lock', 'tp-secure-plugin' ),
                            'default' => 15,
                        ),
                        'max-lockout' => array(
                            'type'  => 'number',
                            'label' => __( 'Max Lockouts', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Maximum number of lockout within 1 days for temporary block IP/user.', 'tp-secure-plugin' ),
                            'default' => 3,
                        ),
                        'block-message' => array(
                            'type'  => 'textarea',
                            'label' => __( 'Block Message', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Block user can see this message when they are locked out.', 'tp-secure-plugin' ),
                            'default' => __( 'You have been locked out due to too many login attempts.', 'tp-secure-plugin' ),
                        ),
                    )
                ),
                'login-logs-activity' => array(
                    'fields' => array(
                        
                    )
                ),
                'recaptcha' => array(
                    'fields' => array(
                        'enable' => array(
                            'type'  => 'switch',
                            'label' => __( 'Enable', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'version' => array(
                            'type'  => 'option',
                            'label' => __( 'Version', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( '', 'tp-secure-plugin' ),
                            'default' => '',
                            'options' => array(
                                'v2' => 'v2',
                                'v3' => 'v3',
                            )
                        ),
                        'site-key' => array(
                            'type'  => 'text',
                            'label' => __( 'Site Key', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( '', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                        'secret-key' => array(
                            'type'  => 'text',
                            'label' => __( 'Screet Key', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( '', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                        'theme' => array(
                            'type'  => 'option',
                            'label' => __( 'Theme', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( '', 'tp-secure-plugin' ),
                            'default' => 'light',
                            'options' => array(
                                'light' => 'Light Theme',
                                'dark'  => 'Dark Theme',
                            )
                        ),
                    )
                ),
                'two-factor-auth' => array(
                    'fields' => array(
                        'otp-email' => array(
                            'type'  => 'switch',
                            'label' => __( 'OTP via Email', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'After entering the correct login credentials, the user will be asked for the OTP. The OTP will be emailed to the user.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                    ),
                ),
                'password-protection' => array(
                    'fields' => array(
                        'enable' => array(
                            'type'  => 'switch',
                            'label' => __( 'Enable', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'To enable/disable this feature.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'password' => array(
                            'type'  => 'password',
                            'label' => __( 'Set Password', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Password-protect the entire site to hide the content from public view and search engine bots / crawlers.', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                        'password-expiry' => array(
                            'type'  => 'number',
                            'label' => __( 'Password Access Duration', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'How long visitors can access the site after entering the correct password.', 'tp-secure-plugin' ),
                            'default' => '15',
                        ),
                        'exclude' => array(
                            'type'  => 'multi-check',
                            'label' => __( 'Exclude', 'tp-secure-plugin' ),
                            'class' => '',
                            'id'    => '',
                            'desc'  => __( 'Exclude user from this password protection', 'tp-secure-plugin' ),
                            'default' => 'light',
                            'options' => array_merge(
                                array(
                                    'all-login-user' => 'All login user',
                                ),
                                get_tps_all_user_roles()
                            ),
                        ),
                    ),
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

        // Remove site URL from login URL
        $relative_path = str_replace( $site_url, '', $login_url );

        // Trim any leading/trailing slashes
        $relative_path = trim( $relative_path, '/' );

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

/**
 * Returns the full table name with the TPSA prefix.
 *
 * This function combines the global WordPress table prefix with the TPSA-specific
 * prefix and the provided table name to generate the full table name.
 *
 * @since 1.0.0
 *
 * @param string $table_name The base name of the table.
 *
 * @return string The full table name with the TPSA prefix.
 */

function get_tpsa_db_table_name( $table_name ) {
    global $wpdb;
    $prefix          = $wpdb->prefix . TPSA_PREFIX . '_';
    return $prefix . $table_name;
}

function get_tps_all_user_roles() {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    $roles = $wp_roles->roles;
    $role_names = [];

    foreach ( $roles as $key => $role ) {
        $role_names[ $key ] = $role['name'];
    }

    return $role_names;
}

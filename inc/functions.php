<?php

if ( !function_exists( 'tpsa_settings_option' ) ) {
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
                'analytics'           => array(
                    'label' => __( 'Safety Analytics', 'tp-secure-plugin' ),
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
                    'class' => '',
                ),
                'security-core'       => array(
                    'label' => __( 'Security Core', 'tp-secure-plugin' ),
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-icon lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
                    'class' => '',
                    'sub'   => array(
                        'limit-login-attempts' => array(
                            'label' => __( 'Limit Login Attempts', 'tp-secure-plugin' ),
                        ),
                        'two-factor-auth'      => array(
                            'label' => __( 'Two-Factor Authentication', 'tp-secure-plugin' ),
                        ),
                        'password-protection'  => array(
                            'label' => __( 'Password Protection', 'tp-secure-plugin' ),
                        ),
                        'recaptcha'            => array(
                            'label' => __( 'reCAPTCHA', 'tp-secure-plugin' ),
                        ),
                    ),
                ),
                'firewall-malware'    => array(
                    'label' => __( 'Firewall & Malware', 'tp-secure-plugin' ),
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-alert-icon lucide-shield-alert"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
                    'class' => '',
                    'sub'   => array(
                        'advanced-malware-scanner' => array(
                            'label' => __( 'Advanced Malware Scanner', 'tp-secure-plugin' ),
                        ),
                        'web-application-firewall' => array(
                            'label' => __( 'Web Application Firewall', 'tp-secure-plugin' ),
                        ),
                    ),
                ),
                'login-logs-activity' => array(
                    'label' => __( 'Monitoring & Analytics', 'tp-secure-plugin' ),
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity-icon lucide-activity"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>',
                    'class' => '',
                ),
                'privacy-hardening'   => array(
                    'label' => __( 'Privacy & Hardening', 'tp-secure-plugin' ),
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database-icon lucide-database"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>',
                    'class' => '',
                ),
                'customize'           => array(
                    'label' => __( 'Customization & Access', 'tp-secure-plugin' ),
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-palette-icon lucide-palette"><path d="M12 22a1 1 0 0 1 0-20 10 9 0 0 1 10 9 5 5 0 0 1-5 5h-2.25a1.75 1.75 0 0 0-1.4 2.8l.3.4a1.75 1.75 0 0 1-1.4 2.8z"/><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/></svg>',
                    'class' => '',
                ),
            )
        );
    }
}

if ( !function_exists( 'tpsa_settings_fields' ) ) {
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
                'analytics'            => array(

                ),
                'admin-bar'            => array(
                    'fields' => array(
                        'enable'  => array(
                            'type'    => 'switch',
                            'label'   => __( 'Hide Admin Bar', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To enable/disable admin bar', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'exclude' => array(
                            'type'    => 'multi-check',
                            'label'   => __( 'Exclude', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Exclude users', 'tp-secure-plugin' ),
                            'default' => 'light',
                            'options' => get_tps_all_user_roles(),
                        ),
                    ),
                ),
                'custom-login-url'     => array(
                    'fields' => array(
                        'enable'       => array(
                            'type'    => 'switch',
                            'label'   => __( 'Enable', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To enable/disable custom login/logut url.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'login-url'    => array(
                            'type'    => 'text',
                            'label'   => __( 'Custom Login Url', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Protect your website by changing the login page URL.', 'tp-secure-plugin' ),
                            'default' => get_tpsa_site_login_path(),
                        ),
                        'redirect-url' => array(
                            'type'    => 'text',
                            'label'   => __( 'Redirect URL', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Wp Admin redirect URL. <strong>Default</strong>: home_url()', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                        'logout-url'   => array(
                            'type'    => 'text',
                            'label'   => __( 'Redirect URL After Logout', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Redirect URL after Logout', 'tp-secure-plugin' ),
                            'default' => get_tpsa_site_login_path(),
                        ),

                    ),
                ),
                'limit-login-attempts' => array(
                    'fields' => array(
                        'enable'           => array(
                            'type'    => 'switch',
                            'label'   => __( 'Enable', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To enable/disable limit login attempts.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'max-attempts'     => array(
                            'type'    => 'number',
                            'label'   => __( 'Max Login Attempts', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Maximum number of login attempts within 1 day for temporarily blocked IP/user.', 'tp-secure-plugin' ),
                            'default' => 3,
                        ),
                        'block-for'        => array(
                            'type'    => 'number',
                            'label'   => __( 'Lock for', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Add how many minutes will be locked.', 'tp-secure-plugin' ),
                            'default' => 15,
                        ),
                        'max-lockout'      => array(
                            'type'    => 'number',
                            'label'   => __( 'Max Lockouts', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Maximum number of lockout within 1 days for temporarily blocked IP/user.', 'tp-secure-plugin' ),
                            'default' => 3,
                        ),
                        'block-message'    => array(
                            'type'    => 'textarea',
                            'label'   => __( 'Blocked Message', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Blocked users can see this message when they are locked out.', 'tp-secure-plugin' ),
                            'default' => __( 'You have been locked out due to too many login attempts.', 'tp-secure-plugin' ),
                        ),
                        'block-ip-address' => array(
                            'type'    => 'single-repeater',
                            'label'   => __( 'Block with IP Address', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'IP addresses', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                    ),
                ),
                'login-logs-activity'  => array(
                    'fields' => array(

                    ),
                ),
                'recaptcha'            => array(
                    'fields' => array(
                        'enable'     => array(
                            'type'    => 'switch',
                            'label'   => __( 'Enable', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To enable/disable reCAPTCHA.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'version'    => array(
                            'type'    => 'option',
                            'label'   => __( 'Version', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Select Google reCAPTCHA version', 'tp-secure-plugin' ),
                            'default' => '',
                            'options' => array(
                                'v2' => 'v2',
                                'v3' => 'v3',
                            ),
                        ),
                        'site-key'   => array(
                            'type'    => 'text',
                            'label'   => __( 'Site Key', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => "Enter you Google reCAPTCHA site key. <a href='https://developers.google.com/recaptcha' target='_blank'>Get site key</a>",
                            'default' => '',
                        ),
                        'secret-key' => array(
                            'type'    => 'text',
                            'label'   => __( 'Secret Key', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => "Enter you Google reCAPTCHA screet key. <a href='https://developers.google.com/recaptcha' target='_blank'>Get secret key</a>",
                            'default' => '',
                        ),
                        'theme'      => array(
                            'type'    => 'option',
                            'label'   => __( 'Theme', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Select your preferred theme', 'tp-secure-plugin' ),
                            'default' => 'light',
                            'options' => array(
                                'light' => 'Light Theme',
                                'dark'  => 'Dark Theme',
                            ),
                        ),
                    ),
                ),
                'two-factor-auth'      => array(
                    'fields' => array(
                        'otp-email' => array(
                            'type'    => 'switch',
                            'label'   => __( 'OTP via Email', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'After entering the correct login credentials, the user will be asked for the OTP. The OTP will be emailed to the user.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                    ),
                ),
                'password-protection'  => array(
                    'fields' => array(
                        'enable'          => array(
                            'type'    => 'switch',
                            'label'   => __( 'Enable', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To enable/disable password protection.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'password'        => array(
                            'type'    => 'password',
                            'label'   => __( 'Set Password', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Password-protect the entire site to hide the content from public view and search engine bots / crawlers.', 'tp-secure-plugin' ),
                            'default' => '',
                        ),
                        'password-expiry' => array(
                            'type'    => 'number',
                            'label'   => __( 'Password Access Duration', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'How long visitors can access the site after entering the correct password.', 'tp-secure-plugin' ),
                            'default' => '15',
                        ),
                        'exclude'         => array(
                            'type'    => 'multi-check',
                            'label'   => __( 'Exclude', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Exclude user from this password protection', 'tp-secure-plugin' ),
                            'default' => 'light',
                            'options' => array_merge(
                                array(
                                    'all-login-user' => 'All logged in users',
                                ),
                                get_tps_all_user_roles()
                            ),
                        ),
                    ),
                ),
                'privacy-hardening'    => array(
                    'fields' => array(
                        'xml-rpc-enable' => array(
                            'type'    => 'switch',
                            'label'   => __( 'Disable XML-RPC', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To disable/enable XML-RPC.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                    ),
                ),
                'customize'            => array(
                    'fields' => array(
                        'enable'      => array(
                            'type'    => 'switch',
                            'label'   => __( 'Enable', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'To enable/disable customizer.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'logo'        => array(
                            'type'    => 'upload',
                            'label'   => __( 'Logo', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Preferred logo size: 84×84 px. Please upload accordingly.', 'tp-secure-plugin' ),
                            'default' => 0,
                        ),
                        'logo-url'    => array(
                            'type'    => 'text',
                            'label'   => __( 'Logo URL', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( 'Enter logo url', 'tp-secure-plugin' ),
                            'default' => site_url(),
                        ),
                        'logo-width'  => array(
                            'type'    => 'number',
                            'label'   => __( 'Logo Width', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( '', 'tp-secure-plugin' ),
                            'default' => 84,
                        ),
                        'logo-height' => array(
                            'type'    => 'number',
                            'label'   => __( 'Logo Height', 'tp-secure-plugin' ),
                            'class'   => '',
                            'id'      => '',
                            'desc'    => __( '', 'tp-secure-plugin' ),
                            'default' => 84,
                        ),
                    ),
                ),
            )
        );
    }
}

if ( !function_exists( 'get_tpsa_site_login_path' ) ) {
    /**
     * Returns the relative path of the WordPress login URL.
     *
     * @since 1.0.0
     *
     * @return string The relative path of the WordPress login URL.
     */
    function get_tpsa_site_login_path() {
        $site_url = get_site_url();
        $login_url = wp_login_url();

        // Remove site URL from login URL
        $relative_path = str_replace( $site_url, '', $login_url );

        // Trim any leading/trailing slashes
        $relative_path = trim( $relative_path, '/' );

        return $relative_path;
    }
}

if ( !function_exists( 'get_tpsa_prefix' ) ) {
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
    $prefix = $wpdb->prefix . TPSA_PREFIX . '_';
    return $prefix . $table_name;
}

function get_tps_all_user_roles() {
    global $wp_roles;

    if ( !isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    $roles = $wp_roles->roles;
    $role_names = [];

    foreach ( $roles as $key => $role ) {
        $role_names[$key] = $role['name'];
    }

    return $role_names;
}

if ( !function_exists( 'tpsm_saved_remote_data' ) ) {

/**
 * Sends the current user's information to a remote server.
 *
 * This function retrieves the currently logged-in user's full name,
 * email address, and the site URL, and sends this data to a specified
 * remote server endpoint using a POST request. This is intended to
 * collect user data for integration with a remote service.
 *
 * @return void
 */
    function tpsm_saved_remote_data() {
        $current_user = wp_get_current_user();

        // Check if a user is logged in
        if ( !$current_user || 0 === $current_user->ID ) {
            return;
        }

        // Get full name (first + last or fallback to display name)
        $full_name = trim( $current_user->first_name . ' ' . $current_user->last_name );
        if ( empty( $full_name ) ) {
            $full_name = $current_user->display_name;
        }

        $email_address = $current_user->user_email;
        $site_url = get_site_url();

        wp_remote_post( 'https://themepaste.com/wp-json/v2/collect-email/admin-safety-guard', [
            'headers' => [
                'X-Auth-Token' => 'c7fc312817194d30c79da538204eaec3',
                'Content-Type' => 'application/json',
            ],
            'body'    => json_encode( [
                'email_address' => $email_address,
                'full_name'     => $full_name,
                'site_url'      => $site_url,
            ] ),
        ] );

    }
}

if ( !function_exists( 'login_page_templates' ) ) {
/**
 * Returns an associative array of available login page templates.
 *
 * The array is composed of template slugs as keys and arrays of 'label' and 'css' as values.
 *
 * @since 1.0.0
 *
 * @return array Associative array of available login page templates.
 */
    /**
     * Get login page templates.
     *
     * @return array
     */
    function login_page_templates() {
        $templates = [
            'default'  => ['label' => __( 'WordPress Default', 'tp-login-designer' ), 'css' => '', 'smalImg' => 'https://placehold.co/200x200', 'bigImg' => 'https://placehold.co/800x600'],
            'classic'  => ['label' => __( 'Classic Card', 'tp-login-designer' ), 'css' => 'classic.css', 'smalImg' => 'https://placehold.co/200x200', 'bigImg' => 'https://placehold.co/800x600'],
            'glass'    => ['label' => __( 'Frosted Glass', 'tp-login-designer' ), 'css' => 'glass.css', 'smalImg' => 'https://placehold.co/200x200', 'bigImg' => 'https://placehold.co/800x600'],
            'split'    => ['label' => __( 'Split Hero', 'tp-login-designer' ), 'css' => 'split.css', 'smalImg' => 'https://placehold.co/200x200', 'bigImg' => 'https://placehold.co/800x600'],
            'gradient' => ['label' => __( 'Soft Gradient', 'tp-login-designer' ), 'css' => 'gradient.css', 'smalImg' => 'https://placehold.co/200x200', 'bigImg' => 'https://placehold.co/800x600'],
        ];

        /**
         * Filter: Modify the available login templates.
         *
         * @param array $templates Associative array of template definitions.
         */
        return apply_filters( 'customize/login_templates', $templates );
    }

}

if ( !function_exists( 'tp_asg_pro_current_prefix' ) ) {
    function tp_asg_pro_current_prefix() {
        global $wpdb;
        return $wpdb->prefix;
    }
}

/**
 * Generate a random DB table prefix string.
 *
 * @param int $len Optional length of the prefix. Default is 5.
 * @return string A random DB table prefix string (e.g. 'abcde_').
 */
if ( !function_exists( 'tp_asg_pro_random_prefix' ) ) {
    function tp_asg_pro_random_prefix( int $len = 5 ) {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $s = '';
        $s .= chr( ord( 'a' ) + random_int( 0, 25 ) );
        for ( $i = 1; $i < $len; $i++ ) {
            $s .= $chars[random_int( 0, strlen( $chars ) - 1 )];
        }
        return $s . '_';
    }
}

if ( !function_exists( 'tp_asg_pro_is_prefix_good' ) ) {
    function tp_asg_pro_is_prefix_good( $prefix ) {

        // 0) Must end with "_"
        if ( substr( $prefix, -1 ) !== '_' ) {
            return false;
        }

        $clean = rtrim( $prefix, '_' );

        // 1) Basic charset & shape
        if ( $clean === '' ) {
            return false;
        }
        // Only lowercase letters, digits, underscore (allow uppercase but normalize for checks)
        if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $clean ) ) {
            return false;
        }
        // Start with a letter (avoid numeric start for readability)
        if ( !preg_match( '/^[a-zA-Z]/', $clean ) ) {
            return false;
        }
        // No internal double underscores
        if ( strpos( $clean, '__' ) !== false ) {
            return false;
        }

        // Normalize to lowercase for word checks
        $lc = strtolower( $clean );

        // 2) Length guard (excluding trailing underscore)
        $len = strlen( $clean );
        if ( $len < 3 || $len > 20 ) { // reasonable bounds; MySQL table name limit is 64 but keep prefixes compact
            return false;
        }

        // 3) Require letters + numbers
        if ( !preg_match( '/[a-z]/i', $clean ) || !preg_match( '/[0-9]/', $clean ) ) {
            return false;
        }

        // 4) Block common/guessable words and environments
        $bad_words = [
            'wp', 'wordpress', 'blog', 'site', 'cms', 'press', 'db', 'data', 'table',
            'admin', 'root', 'superadmin', 'secure', 'security', 'guard', 'shield', 'lock', 'safe', 'safety',
            'school', 'college', 'university', 'edu',
            'user', 'member', 'login', 'auth',
            'test', 'demo', 'sample', 'example', 'sandbox', 'staging', 'stage', 'dev', 'devel', 'prod', 'production',
            'alpha', 'beta', 'backup', 'bkp', 'tmp', 'temp', 'cache', 'public', 'private',
            'qwerty', 'abc', 'abcd', 'abcde', 'abcd1234', 'test123', 'admin123',
        ];

        foreach ( $bad_words as $w ) {
            if ( $lc === $w || str_contains( $lc, $w ) ) {
                return false;
            }
        }

        // 5) Block SQL keywords (simple set)
        $sql_keywords = [
            'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter', 'grant', 'revoke',
            'union', 'where', 'from', 'join', 'table', 'into', 'values', 'set', 'index', 'view',
        ];
        foreach ( $sql_keywords as $kw ) {
            if ( $lc === $kw || str_contains( $lc, $kw ) ) {
                return false;
            }
        }

        // 6) Obvious sequences / repeats
        // 6a) Same char repeated (aaa…)
        if ( preg_match( '/^([a-z0-9])\1{3,}$/i', $clean ) ) {
            return false;
        }

        // 6b) Strict ascending/descending sequences of length >= 5 (abcde, 12345, edcba)
        $is_monotonic = function ( $s ) {
            $n = strlen( $s );
            if ( $n < 5 ) {
                return false;
            }

            $asc = true;
            $desc = true;
            for ( $i = 1; $i < $n; $i++ ) {
                $d = ord( $s[$i] ) - ord( $s[$i - 1] );
                if ( $d !== 1 ) {$asc = false;}
                if ( $d !== -1 ) {$desc = false;}
                if ( !$asc && !$desc ) {
                    return false;
                }

            }
            return $asc || $desc;
        };
        if ( $is_monotonic( preg_replace( '/[^a-z0-9]/i', '', $lc ) ) ) {
            return false;
        }

        // 6c) Common keyboard runs
        $keyboard_runs = ['qwerty', 'asdf', 'zxcv', '12345', '98765'];
        foreach ( $keyboard_runs as $run ) {
            if ( str_contains( $lc, $run ) ) {
                return false;
            }
        }

        // 7) Entropy-ish check: require variety
        $letters = array_unique( str_split( preg_replace( '/[^a-z]/', '', $lc ) ) );
        $digits = array_unique( str_split( preg_replace( '/[^0-9]/', '', $lc ) ) );
        $unique = array_unique( str_split( $lc ) );
        if ( ( count( $letters ) < 2 || count( $digits ) < 2 ) && count( $unique ) < 5 ) {
            return false;
        }

        // 8) Specific legacy defaults
        if ( $lc === 'wp' || $lc === 'wp1' || $lc === 'wp2' || $lc === 'wp3' ) {
            return false;
        }

        return true;
    }
}

function tp_svg_google() {
    return '<svg viewBox="0 0 48 48" width="24" height="24" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.8 32.2 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.4 6.3 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.3-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.4 16 18.8 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.4 6.3 29.5 4 24 4 16.1 4 9.3 8.6 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 10.1-2 13.7-5.3l-6.3-5.3C29.3 34.8 26.8 36 24 36c-5.3 0-9.8-3.8-11.3-8.9l-6.6 5.1C9.2 39.3 16 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1 3.1-3.5 5.7-6.6 6.7l6.3 5.3C38.4 37.8 44 31.6 44 24c0-1.2-.1-2.3-.4-3.5z"/></svg>';
}
function tp_svg_facebook() {
    return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.093 10.125 24v-8.437H7.078v-3.49h3.047V9.356c0-3.016 1.792-4.687 4.533-4.687 1.312 0 2.686.235 2.686.235v2.953h-1.513c-1.49 0-1.953.927-1.953 1.875v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.093 24 18.1 24 12.073z"/></svg>';
}
function tp_svg_twitter() {
    return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M18.244 2H21.5l-7.62 8.71L22 22h-6.098l-4.77-5.495L5.78 22H2.5l8.147-9.312L2 2h6.21l4.316 4.934L18.244 2zm-1.07 18h1.775L7.919 4h-1.86l10.114 16z" fill="#111"/></svg>';
}
function tp_svg_linkedin() {
    return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path fill="#0A66C2" d="M20.451 20.451h-3.554v-5.569c0-1.329-.024-3.04-1.852-3.04-1.853 0-2.135 1.447-2.135 2.943v5.666H9.356V9h3.414v1.561h.049c.476-.9 1.638-1.852 3.369-1.852 3.603 0 4.269 2.372 4.269 5.455v6.287zM5.337 7.433c-1.143 0-2.067-.927-2.067-2.07 0-1.144.924-2.07 2.067-2.07 1.145 0 2.07.926 2.07 2.07 0 1.143-.925 2.07-2.07 2.07zM7.114 20.451H3.558V9h3.556v11.451z"/></svg>';
}

if ( !function_exists( 'tpsa_all_features' ) ):
    function tpsa_all_features() {
        $features = tpsa_settings_option();

        $all_features_list = [];

        foreach ( $features as $feature_slug => $feature_data ) {

            if ( isset( $feature_data['sub'] ) ) {
                $features = $feature_data['sub'];
                foreach ( $features as $key => $value ) {
                    $all_features_list[] = $key;
                }
            } else {
                $all_features_list[] = $feature_slug;
            }

        }

        return $all_features_list;
    }
endif;

if ( !function_exists( 'tpsa_get_features_summary' ) ):
/**
 * Returns a summary of all features in the plugin.
 *
 * The summary object contains three properties:
 * - total: The total number of features.
 * - active: The number of features that are currently active (i.e. enabled).
 * - inactive: The number of features that are currently inactive (i.e. disabled).
 *
 * @return object Summary object containing the total, active, and inactive feature counts.
 */
    function tpsa_get_features_summary() {

        $settings_fields = tpsa_settings_fields();

        $all_features_list = tpsa_all_features();

        $total = 0;
        $active = 0;
        $inactive = 0;

        $all_active_features = [];

        foreach ( $all_features_list as $feature_slug ) {

            $total++;
            if ( isset( $settings_fields[$feature_slug] ) ) {
                $fields = $settings_fields[$feature_slug]['fields'];

                // If feature has an enable switch
                if ( isset( $fields['enable'] ) ) {

                    $option_name = get_tpsa_settings_option_name( $feature_slug );
                    $saved_opts = get_option( $option_name, [] );

                    if ( !empty( $saved_opts['enable'] ) ) {
                        $active++;
                        $all_active_features[] = $feature_slug;
                    } else {
                        $inactive++;
                    }
                }
            }

            // If no fields exist → always active
            if ( empty( $settings_fields[$feature_slug]['fields'] ) ) {
                $active++;
                $all_active_features[] = $feature_slug;
                continue;
            }

        }

        return (object) [
            'total'               => $total,
            'active'              => $active,
            'inactive'            => $inactive,
            'all_active_features' => $all_active_features,
        ];
    }
endif;

if ( !function_exists( 'tpsa_get_security_score' ) ):
    function tpsa_get_security_score() {

        $summary = tpsa_get_features_summary();

        if ( empty( $summary->total ) ) {
            return 0;
        }

        $raw_score = ( $summary->active / $summary->total ) * 100;

        // Absolute + safe bounds
        $score = abs( round( $raw_score ) );
        $score = min( 100, max( 0, $score ) );

        return $score;
    }
endif;

if ( !function_exists( 'tpsa_get_security_label' ) ):
    function tpsa_get_security_label( $score ) {

        if ( $score >= 90 ) {
            return 'Excellent protection';
        } elseif ( $score >= 75 ) {
        return 'Strong protection';
    } elseif ( $score >= 50 ) {
        return 'Moderate protection';
    } elseif ( $score >= 30 ) {
        return 'Weak protection';
    } else {
        return 'Critical risk';
    }
}
endif;

if ( !function_exists( 'tp_is_pro_active' ) ) {

/**
 * Checks if the Admin Safety Guard Pro plugin is active.
 *
 * @return bool True if the plugin is active, false otherwise.
 */
    function tp_is_pro_active() {

        $plugin_path = 'admin-safety-guard-pro/admin-safety-guard-pro.php';

        if ( !function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Normal activation
        if ( is_plugin_active( $plugin_path ) ) {
            return true;
        }

        // Network activation (multisite)
        if ( is_multisite() && is_plugin_active_for_network( $plugin_path ) ) {
            return true;
        }

        return false;
    }
}
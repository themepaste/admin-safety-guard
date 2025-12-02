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
                'analytics'            => array(
                    'label' => __( 'Safety Analytics', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'limit-login-attempts' => array(
                    'label' => __( 'Limit Login Attempts', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'login-logs-activity'  => array(
                    'label' => __( 'Login Logs & Activity', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'custom-login-url'     => array(
                    'label' => __( 'Custom Login/Logout', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'recaptcha'            => array(
                    'label' => __( 'Google reCAPTCHA', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'two-factor-auth'      => array(
                    'label' => __( 'Two Factor Auth', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'password-protection'  => array(
                    'label' => __( 'Password Protection', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'privacy-hardening'    => array(
                    'label' => __( 'Privacy Hardening', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'admin-bar'            => array(
                    'label' => __( 'Hide Admin Bar', 'tp-secure-plugin' ),
                    'class' => '',
                ),
                'customize'            => array(
                    'label' => __( 'Customizer', 'tp-secure-plugin' ),
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

        $response = wp_remote_post( 'https://themepaste.com/wp-json/v2/collect-email/admin-safety-guard', [
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
function tp_asg_pro_random_prefix( int $len = 5 ) {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $s = '';
    $s .= chr( ord( 'a' ) + random_int( 0, 25 ) );
    for ( $i = 1; $i < $len; $i++ ) {
        $s .= $chars[random_int( 0, strlen( $chars ) - 1 )];
    }
    return $s . '_';
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
<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Traits\Hook;

class Admin {

    use Hook;
    use Asset;

    /**
     * Admin constructor.
     *
     * Initializes the Secure Admin plugin by triggering the
     * initialization of the settings page and enqueueing the
     * admin styles.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'plugins_loaded', function () {
            new Wizard();
            new Notice();
            ( new Settings() )->init();
        } );
        $this->action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_styles'] );
        $this->action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );
        $this->ajax_priv( 'tpsa_deactivate_plugin', [$this, 'tpsa_deactivate_plugin_callback'] );
        $this->ajax_priv( 'tpsa_generate_prefix_suggestions', [$this, 'tpsa_generate_prefix_suggestions_callback'] );
    }

    public function tpsa_deactivate_plugin_callback() {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tpsa_deactivate_nonce' ) ) {
            wp_send_json_error( ['message' => 'Nonce verification failed'] );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( ['message' => 'Unauthorized'] );
        }

        $reason   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
        $feedback = isset( $_POST['feedback'] ) ? sanitize_textarea_field( wp_unslash( $_POST['feedback'] ) ) : '';
        $api_url  = 'https://themepaste.com/wp-json/tpsa/v1/feedback';

        $response = wp_remote_post( $api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key'    => 'a2e4a51671af827045df95bcd686c7ae4dae3b99',
            ],

            'body'    => wp_json_encode( [
                'website_url' => site_url(),
                'admin_name'  => wp_get_current_user()->display_name,
                'admin_email' => wp_get_current_user()->user_email,
                'plugin_name' => 'Admin Safety Guard',
                'reason'      => $reason,
                'feedback'    => $feedback,
            ] ),
            'timeout' => 20,
        ] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( ['message' => 'API request failed'] );
        }

        wp_send_json_success( ['message' => 'Success'] );
        exit;
    }

    /**
     * Enqueues admin styles for the Secure Admin settings page.
     *
     * @param string $screen The current screen ID.
     *
     * @return void
     */
    public function admin_enqueue_styles( $screen ) {
        if ( 'toplevel_page_' . Settings::$SETTING_PAGE_ID === $screen || 'admin-safety-guard_page_tp-admin-safety-guard-pro' === $screen || 'admin-safety-guard_page_asg-support' === $screen ) {
            $this->enqueue_style(
                'tpsa-settings',
                TPSA_ASSETS_URL . '/admin/css/settings.css'
            );
            $this->enqueue_style(
                'tpsa-fields',
                TPSA_ASSETS_URL . '/admin/css/fields.css'
            );
        }

        // Deactivate
        if ( $screen === 'plugins.php' ) {
            $this->enqueue_style(
                'tpsa-deactivate',
                TPSA_ASSETS_URL . '/admin/css/deactivate.css'
            );

        }
    }

    public function admin_enqueue_scripts( $screen ) {
        $current_setting_screen = Settings::get_current_screen();

        // fields.js also powers the support form and the security-issue dialog,
        // so it has to load on the Support screen too — not just the screens
        // that mount a React bundle.
        $plugin_screens = [
            'toplevel_page_' . Settings::$SETTING_PAGE_ID,
            'admin-safety-guard_page_tp-admin-safety-guard-pro',
            'admin-safety-guard_page_asg-support',
        ];

        if ( in_array( $screen, $plugin_screens, true ) ) {
            $this->enqueue_script(
                'tpsa-fields',
                TPSA_ASSETS_URL . '/admin/js/fields.js',
                [], null, array( 'in_footer' => true )
            );
        }

        if ( 'toplevel_page_' . Settings::$SETTING_PAGE_ID === $screen || 'admin-safety-guard_page_tp-admin-safety-guard-pro' === $screen ) {

            // Handle that carries the tpsaAdmin localized data. It has no file
            // of its own (src === false), so it costs no HTTP request while
            // still giving every React bundle a dependency to order against.
            wp_register_script( 'tpsa-admin', false, ['jquery'], TPSA_ASSETS_VERSION, false );
            wp_enqueue_script( 'tpsa-admin' );

            // Screen slug => React bundle. Only the bundle for the screen being
            // viewed is loaded.
            $bundles = [
                'login-logs-activity'  => 'loginLogActivity',
                'analytics'            => 'analytics',
                'security-core'        => 'securityCore',
                'firewall-malware'     => 'firewallMalware',
                '2fa-using-mobile-app' => 'twoFAUsingMobileApp',
                'privacy-hardening'    => 'privacyHardening',
                'customize'            => 'loginTemplate',
            ];

            if ( isset( $bundles[$current_setting_screen] ) ) {
                // React and the webpack runtime are split into shared chunks so
                // they are downloaded and parsed once rather than being inlined
                // into each of the seven bundles.
                $this->enqueue_script(
                    'tpsa-runtime',
                    TPSA_ASSETS_URL . '/admin/build/runtime.bundle.js',
                    [], null, array( 'in_footer' => false )
                );
                $this->enqueue_script(
                    'tpsa-framework',
                    TPSA_ASSETS_URL . '/admin/build/framework.bundle.js',
                    ['tpsa-runtime'], null, array( 'in_footer' => false )
                );

                // wp_enqueue_media() is only needed by screens with an upload
                // field; loading it everywhere pulls in the whole media modal.
                if ( 'customize' === $current_setting_screen ) {
                    wp_enqueue_media();
                }

                $this->enqueue_script(
                    'tpsa-screen-' . $current_setting_screen,
                    TPSA_ASSETS_URL . '/admin/build/' . $bundles[$current_setting_screen] . '.bundle.js',
                    ['tpsa-admin', 'tpsa-runtime', 'tpsa-framework'], null, array( 'in_footer' => false )
                );
            }

            $login_url = wp_login_url();
            $glue = strpos( $login_url, '?' ) !== false ? '&' : '?';

            $localize = [
                'nonce'           => wp_create_nonce( 'tpsa-nonce' ),
                'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
                'ajax_url'        => admin_url( 'admin-ajax.php' ),
                'screen_slug'     => Settings::$SETTING_PAGE_ID,
                'setting_slug'    => $current_setting_screen,
                'rest_url'        => esc_url_raw( rest_url() ),
                'limit_login'     => $this->is_enabled( $this->get_settings() ),
                'admin_url'       => admin_url(),
                'assets_url'      => TPSA_ASSETS_URL,
                'previewUrl'      => $login_url . $glue . 'cdp_preview=1',
                'social_login'    => array_keys( (array) get_option( 'social_login_crendentials', [] ) ),
                'sameOrigin'      => ( wp_parse_url( admin_url(), PHP_URL_HOST ) === wp_parse_url( $login_url, PHP_URL_HOST ) ),
                'feature_status'  => tpsa_get_features_summary(),
                'total_users'     => $this->get_total_users(),

                //server info
                'php_version'     => phpversion(),
                'server_software' => sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ) ),
                'server_os'       => PHP_OS,
                'site_url'        => get_bloginfo( 'url' ),
                'memory_limit'    => ini_get( 'memory_limit' ),
                'max_execution'   => ini_get( 'max_execution_time' ),
            ];

            if ( $current_setting_screen === 'customize' ) {
                $localize['login_templates'] = login_page_templates();
            }

            if ( $current_setting_screen === 'firewall-malware' ) {
                $localize['companion'] = $this->get_companion_plugin_data();
            }

            $this->localize_script( 'tpsa-admin', 'tpsaAdmin', $localize );
        }

        // Deactivate
        if ( $screen === 'plugins.php' ) {
            wp_enqueue_script(
                'tpsa-deactivate',
                TPSA_ASSETS_URL . '/admin/js/deactivate.js',
                ['jquery'],
                '1.0',
                true
            );

            wp_localize_script( 'tpsa-deactivate', 'tpsaDeactivate', [
                'ajax_url'    => admin_url( 'admin-ajax.php' ),
                'plugin_slug' => TPSA_PLUGIN_BASENAME,
                'nonce'       => wp_create_nonce( 'tpsa_deactivate_nonce' ),
                'privacy_url' => 'https://themepaste.com/privacy-policy',
                'i18n'        => [
                    'disclosure'    => __( 'If you submit feedback, your admin name, email address and site URL are sent to ThemePaste (themepaste.com) along with your message, so we can follow up and improve the plugin.', 'admin-safety-guard' ),
                    'privacy_label' => __( 'Privacy Policy', 'admin-safety-guard' ),
                    'skip_note'     => __( 'Prefer not to share anything? Choose “Skip” — no data is sent.', 'admin-safety-guard' ),
                ],
            ] );
        }
    }

    /**
     * State of the Deep Malware Cleaner companion plugin.
     *
     * Malware scanning and cleanup are handled by that separate free plugin
     * rather than duplicated here, so the Firewall & Malware screen needs to
     * know whether it is already installed and active in order to show the
     * right call to action.
     *
     * @return array
     */
    private function get_companion_plugin_data() {
        $basename = 'deep-malware-cleaner/deep-malware-cleaner.php';
        $slug = 'deep-malware-cleaner';

        if ( !function_exists( 'get_plugins' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed = array_key_exists( $basename, get_plugins() );
        // is_plugin_active() alone misses a network-activated plugin on multisite.
        $active = $installed
        && ( is_plugin_active( $basename )
            || ( is_multisite() && is_plugin_active_for_network( $basename ) ) );

        return [
            'name'          => 'Deep Malware Cleaner',
            'installed'     => $installed,
            'active'        => $active,
            // Where to go once it is running.
            'dashboard_url' => admin_url( 'admin.php?page=' . $slug ),
            // Native install screen: keeps the user inside wp-admin.
            'install_url'   => current_user_can( 'install_plugins' )
            ? admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug )
            : '',
            // Installed but switched off.
            'plugins_url'   => current_user_can( 'activate_plugins' )
            ? admin_url( 'plugins.php?s=' . rawurlencode( 'Deep Malware Cleaner' ) . '&plugin_status=all' )
            : '',
            'wporg_url'     => 'https://wordpress.org/plugins/' . $slug . '/',
        ];
    }

    /**
     * Total user count for the dashboard tiles.
     *
     * count_users() runs an uncached GROUP BY over the whole user table, which
     * is expensive on large sites. The dashboard only needs a rough figure, so
     * the result is cached for an hour.
     *
     * @return int
     */
    private function get_total_users() {
        $total = get_transient( 'tpsa_total_users' );

        if ( false === $total ) {
            $counts = count_users();
            $total = isset( $counts['total_users'] ) ? (int) $counts['total_users'] : 0;
            set_transient( 'tpsa_total_users', $total, HOUR_IN_SECONDS );
        }

        return (int) $total;
    }

    /**
     * Get plugin settings.
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( 'limit-login-attempts' );
        return get_option( $option_name, [] );
    }

    /**
     * Check if the feature is enabled.
     */
    private function is_enabled( $settings ) {
        return isset( $settings['enable'] ) && (int) $settings['enable'] === 1;
    }

    public function tpsa_generate_prefix_suggestions_callback() {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tpsa-nonce' ) ) {
            wp_send_json_error( ['message' => 'Nonce verification failed'] );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( ['message' => 'Permission denied'] );
        }
        wp_send_json_success( self::generate_valid_prefix_suggestions() );
    }

    public static function generate_valid_prefix_suggestions( int $count = 4 ): array {
        $lengths     = [ 5, 6, 4, 5 ];
        $suggestions = [];
        foreach ( array_slice( $lengths, 0, $count ) as $len ) {
            for ( $try = 0; $try < 20; $try++ ) {
                $candidate = tp_asg_pro_random_prefix( $len );
                if ( tp_asg_pro_is_prefix_good( $candidate ) ) {
                    $suggestions[] = $candidate;
                    break;
                }
            }
        }
        return $suggestions;
    }

}
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

        add_action( 'login_init', function () {
            if ( isset( $_GET['cdp_preview'] ) ) {
                add_filter( 'wp_headers', function ( $h ) {$h['Cache-Control'] = 'no-store, must-revalidate';return $h;} );
            }
        } );

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

            'body'    => json_encode( [
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

        if ( 'toplevel_page_' . Settings::$SETTING_PAGE_ID === $screen || 'admin-safety-guard_page_tp-admin-safety-guard-pro' === $screen ) {

            wp_enqueue_media();
            $this->enqueue_script(
                'tpsa-admin',
                TPSA_ASSETS_URL . '/admin/js/admin.js',
                ['jquery'], null, array( 'in_footer' => false )
            );
            if ( $current_setting_screen === 'login-logs-activity' ) {
                $this->enqueue_script(
                    'tpsa-login-log-activity',
                    TPSA_ASSETS_URL . '/admin/build/loginLogActivity.bundle.js', [], null, array( 'in_footer' => false )
                );
            } elseif ( $current_setting_screen === 'analytics' ) {
                $this->enqueue_script(
                    'tpsa-analytics',
                    TPSA_ASSETS_URL . '/admin/build/analytics.bundle.js', [], null, array( 'in_footer' => false )
                );
            } elseif ( $current_setting_screen === 'security-core' ) {
                $this->enqueue_script(
                    'tpsa-security-core',
                    TPSA_ASSETS_URL . '/admin/build/securityCore.bundle.js', [], null, array( 'in_footer' => false )
                );

            } elseif ( $current_setting_screen === 'firewall-malware' ) {
                $this->enqueue_script(
                    'tpsa-firewall-malware',
                    TPSA_ASSETS_URL . '/admin/build/firewallMalware.bundle.js', [], null, array( 'in_footer' => false )
                );
            } elseif ( $current_setting_screen === '2fa-using-mobile-app' ) {
                $this->enqueue_script(
                    'tpsa-2fa-using-mobile-app',
                    TPSA_ASSETS_URL . '/admin/build/twoFAUsingMobileApp.bundle.js', [], null, array( 'in_footer' => false )
                );
            } elseif ( $current_setting_screen === 'privacy-hardening' ) {
                $this->enqueue_script(
                    'tpsa-security-core',
                    TPSA_ASSETS_URL . '/admin/build/privacyHardening.bundle.js', [], null, array( 'in_footer' => false )
                );
            } elseif ( $current_setting_screen === 'customize' ) {
                $this->enqueue_script(
                    'tpsa-customize',
                    TPSA_ASSETS_URL . '/admin/build/loginTemplate.bundle.js', [], null, array( 'in_footer' => false )
                );
            }

            $login_url = wp_login_url();
            $glue = strpos( $login_url, '?' ) !== false ? '&' : '?';

            $localize = [
                'nonce'           => wp_create_nonce( 'tpsa-nonce' ),
                'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
                'site_url'        => site_url(),
                'ajax_url'        => admin_url( 'admin-ajax.php' ),
                'screen_slug'     => Settings::$SETTING_PAGE_ID,
                'setting_slug'    => $current_setting_screen,
                'rest_url'        => esc_url_raw( rest_url() ),
                'limit_login'     => $this->is_enabled( $this->get_settings() ),
                'admin_url'       => admin_url(),
                'assets_url'      => TPSA_ASSETS_URL,
                'previewUrl'      => $login_url . $glue . 'cdp_preview=1',
                'social_login'    => array_keys( (array) get_option( 'social_login_crendentials' ) ),
                'sameOrigin'      => ( wp_parse_url( admin_url(), PHP_URL_HOST ) === wp_parse_url( $login_url, PHP_URL_HOST ) ),
                'feature_status'  => tpsa_get_features_summary(),
                'total_users'     => count_users()['total_users'],

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
            ] );
        }
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
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

        add_action( 'login_init', function () {
            if ( isset( $_GET['cdp_preview'] ) ) {
                add_filter( 'wp_headers', function ( $h ) {$h['Cache-Control'] = 'no-store, must-revalidate';return $h;} );
            }
        } );

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
                ['jquery']
            );
            if ( $current_setting_screen === 'login-logs-activity' ) {
                $this->enqueue_script(
                    'tpsa-login-log-activity',
                    TPSA_ASSETS_URL . '/admin/build/loginLogActivity.bundle.js'
                );
            } elseif ( $current_setting_screen === 'analytics' ) {
                $this->enqueue_script(
                    'tpsa-analytics',
                    TPSA_ASSETS_URL . '/admin/build/analytics.bundle.js'
                );
            } elseif ( $current_setting_screen === 'security-core' ) {
                $this->enqueue_script(
                    'tpsa-security-core',
                    TPSA_ASSETS_URL . '/admin/build/securityCore.bundle.js'
                );
            } elseif ( $current_setting_screen === 'firewall-malware' ) {
                $this->enqueue_script(
                    'tpsa-security-core',
                    TPSA_ASSETS_URL . '/admin/build/firewallMalware.bundle.js'
                );
            } elseif ( $current_setting_screen === 'privacy-hardening' ) {
                $this->enqueue_script(
                    'tpsa-security-core',
                    TPSA_ASSETS_URL . '/admin/build/privacyHardening.bundle.js'
                );
            } elseif ( $current_setting_screen === 'monitoring-analytics' ) {
                $this->enqueue_script(
                    'tpsa-security-core',
                    TPSA_ASSETS_URL . '/admin/build/monitoringAnalytics.bundle.js'
                );
            } elseif ( $current_setting_screen === 'customize' ) {
                $this->enqueue_script(
                    'tpsa-customize',
                    TPSA_ASSETS_URL . '/admin/build/loginTemplate.bundle.js'
                );
            }

            $login_url = wp_login_url();
            $glue = strpos( $login_url, '?' ) !== false ? '&' : '?';

            $localize = [
                'nonce'           => wp_create_nonce( 'tpsa-nonce' ),
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
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
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
                'nonce'       => wp_create_nonce( 'tpsm_feedback_nonce' ),
                'admin_name'  => wp_get_current_user()->display_name,
                'admin_email' => wp_get_current_user()->user_email,
                'site_url'    => site_url(),
                'plugin_name' => 'Admin Safety Guard',
                'tp_rest_url' => 'http://localhost:10078/wp-json/tpsa/v1/feedback',
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
        return isset( $settings['enable'] ) && $settings['enable'] == 1;
    }

}
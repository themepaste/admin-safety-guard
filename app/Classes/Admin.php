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
            } elseif ( $current_setting_screen === 'customize' ) {
                $this->enqueue_script(
                    'tpsa-customize',
                    TPSA_ASSETS_URL . '/admin/build/loginTemplate.bundle.js'
                );
            }

            $login_url = wp_login_url();
            $glue = strpos( $login_url, '?' ) !== false ? '&' : '?';

            $localize = [
                'nonce'          => wp_create_nonce( 'tpsa-nonce' ),
                'site_url'       => site_url(),
                'ajax_url'       => admin_url( 'admin-ajax.php' ),
                'screen_slug'    => Settings::$SETTING_PAGE_ID,
                'setting_slug'   => $current_setting_screen,
                'rest_url'       => esc_url_raw( rest_url() ),
                'limit_login'    => $this->is_enabled( $this->get_settings() ),
                'admin_url'      => admin_url(),
                'assets_url'     => TPSA_ASSETS_URL,
                'previewUrl'     => $login_url . $glue . 'cdp_preview=1',
                'social_login'   => array_keys( (array) get_option( 'social_login_crendentials' ) ),
                'sameOrigin'     => ( wp_parse_url( admin_url(), PHP_URL_HOST ) === wp_parse_url( $login_url, PHP_URL_HOST ) ),
                'feature_status' => tpsa_get_features_summary(),
            ];

            if ( $current_setting_screen === 'customize' ) {
                $localize['login_templates'] = login_page_templates();
            }

            $this->localize_script( 'tpsa-admin', 'tpsaAdmin', $localize );
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
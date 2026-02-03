<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;
use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Class Settings
 *
 * Handles the admin settings page for Secure Admin.
 *
 * @package ThemePaste\SecureAdmin\Classes
 */
class Settings {

    use Hook;
    use Asset;

    /**
     * Settings Page Slug.
     *
     * @var string
     */
    public static $SETTING_PAGE_ID = 'tp-admin-safety-guard';

    /**
     * Admin settings page URL.
     *
     * @var string
     */
    public $setting_page_url = '';

    /**
     * Initialize hooks.
     */
    public function init() {
        $this->setting_page_url = add_query_arg(
            array(
                'page' => self::$SETTING_PAGE_ID,
            ),
            admin_url( 'admin.php' )
        );

        $this->action( 'admin_menu', array( $this, 'register_settings_page' ) );
        $this->filter( 'plugin_action_links_' . TPSA_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );

        // Process and save settings (nonce validated inside FormProcessor::process_form).
        $this->action( 'admin_post_tpsa_process_form', array( FormProcessor::class, 'process_form' ) );

        // Admin redirect handler (routing only).
        $this->action( 'admin_init', array( $this, 'redirect_to_default_tab' ) );
    }

    /**
     * Registers the settings page in the admin menu.
     *
     * @return void
     */
    public function register_settings_page() {
        add_menu_page(
            esc_html__( 'Admin Safety Guard', 'admin-safety-guard' ),
            esc_html__( 'Admin Safety Guard', 'admin-safety-guard' ),
            'manage_options',
            self::$SETTING_PAGE_ID,
            array( $this, 'render_settings_page' ),
            'dashicons-lock',
            56
        );

        add_submenu_page(
            self::$SETTING_PAGE_ID,
            __( 'Support', 'admin-safety-guard' ),
            __( 'Support', 'admin-safety-guard' ),
            'manage_options',
            'asg-support',
            array( $this, 'render_asg_support_page' )
        );
    }

    /**
     * Renders the settings page layout.
     *
     * @return void
     */
    public function render_settings_page() {
        printf( '%s', Utility::get_template( 'settings/layout.php' ) );
    }

    /**
     * Renders the support page layout.
     *
     * @return void
     */
    public function render_asg_support_page() {
        printf( '%s', Utility::get_template( 'settings/support.php' ) );
    }

    /**
     * Redirect to a default tab if no tab is provided.
     *
     * NOTE: This method only reads query vars for routing/UI and does not process
     * form submissions or change data, so nonce verification is not required here.
     *
     * @return void
     */
    public function redirect_to_default_tab() {
        if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading query vars for routing/UI; no state change.
        $page = $this->get_query_key( 'page' );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading query vars for routing/UI; no state change.
        $tpsa_setting = $this->get_query_key( 'tpsa-setting' );

        if ( $page === sanitize_key( self::$SETTING_PAGE_ID ) && empty( $tpsa_setting ) ) {
            $redirect_url = add_query_arg(
                array(
                    'page'         => self::$SETTING_PAGE_ID,
                    'tab'          => 'analytics',
                    'tpsa-setting' => 'analytics',
                ),
                admin_url( 'admin.php' )
            );

            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Adds a "Settings" link to the plugin actions.
     *
     * @param array $links Existing plugin action links.
     * @return array Modified plugin action links.
     */
    public function add_settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( $this->setting_page_url ),
            esc_html__( 'Settings', 'admin-safety-guard' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Get current screen slug from query string (sanitized).
     *
     * @return string|null
     */
    public static function get_current_screen() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading query var for routing/UI; no state change.
        return isset( $_GET['tpsa-setting'] )
        ? sanitize_key( wp_unslash( $_GET['tpsa-setting'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe read only.
        : null;
    }

    /**
     * Safely read a "key-like" query var (slug/page/tab).
     *
     * @param string $key Query var name.
     * @return string Sanitized value or empty string.
     */
    private function get_query_key( $key ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading query var for routing/UI; no state change.
        return isset( $_GET[$key] )
        ? sanitize_key( wp_unslash( $_GET[$key] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe read only.
        : '';
    }
}
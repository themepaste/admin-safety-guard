<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Helpers\Utility;

class Admin {

    use Hook;

    /**
     * Settings Page Slug
     */
    const SETTING_PAGE_ID = 'tp-secure-admin';

    public $setting_page_url = '';

    public function __construct() {

        $this->setting_page_url = add_query_arg(
            [
                'page' => self::SETTING_PAGE_ID,
            ],
            admin_url( 'admin.php' )
        );

        $this->action( 'init', function() {
            $this->action( 'admin_menu', [ $this, 'setting_page' ] );
            $this->filter( 'plugin_action_links_' . TPSA_PLUGIN_BASENAME, [ $this, 'settings_link' ] );
        } );
    }


    /**
     * Adds a settings page to the WordPress admin menu.
     *
     * @action admin_menu
     *
     * @uses add_menu_page()
     */
    public function setting_page() {
        add_menu_page(
            esc_html__( 'Secure Admin', 'shipping-manager' ),
            esc_html__( 'Secure Admin', 'shipping-manager' ),
            'manage_options',
            self::SETTING_PAGE_ID,
            [ $this, 'settings_page_layout' ],
            'dashicons-lock',
            56
        );
    }

    /**
     * The layout for the settings page.
     *
     * @action admin_menu
     */
    public function settings_page_layout() {
        printf( '%s', Utility::get_template( 'settings/layout.php' ) );
    }

    public function settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( $this->setting_page_url ),
            esc_html__( 'Settings', 'shipping-manager' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }
}
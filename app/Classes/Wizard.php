<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Helpers\Utility;

class Wizard {

    use Hook;
    use Asset;

    public function __construct() {
        $this->action( 'admin_init', [$this, 'redirect_to_setup_wizard_page'] );
        $this->action( 'admin_menu', [$this, 'add_setup_wizard_page'] );
        $this->action( 'admin_enqueue_scripts', [$this, 'enqueue_assets'] );
        $this->action( 'admin_init', [$this, 'setup_wizard_process'] );
    }

    function setup_wizard_process() {
        if ( ! isset( $_POST['tpsm_optin_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['tpsm-nonce_name'] ) || ! wp_verify_nonce( $_POST['tpsm-nonce_name'], 'tpsm-nonce_action' ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'shipping-manager' ) );
        }

        // Check capabilities if needed
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized user', 'shipping-manager' ) );
        }

        // Sanitize the choice
        $choice = isset( $_POST['tpsm_optin_choice'] ) ? sanitize_text_field( $_POST['tpsm_optin_choice'] ) : '0';

        // Convert to int and sanitize
        $value = (int) $choice === 1 ? 1 : 0;

        // Save the option
        update_option( 'tpsm_is_setup_wizard', $value );

        // Save remote data if enabled
        if( $value === 1 ) {
            tpsm_saved_remote_data();
        }

        $redirect_url = add_query_arg( 
            array(
                'page'         => 'tp-admin-safety-guard',
                'tpsa-setting' => 'analytics',
            ),
            admin_url( 'admin.php' )
        );

        wp_redirect( $redirect_url );
        error_log( 'Redirecting to: ' . $redirect_url );
        exit;

    }

    public function enqueue_assets( $screen ) {
        if ( 'toplevel_page_' . 'tpasg_setup_wizard' === $screen ) {
            $this->enqueue_style(
                'tpasg-setup-wizard',
                TPSA_ASSETS_URL . '/admin/css/wizard.css',
            );
        }
    }

    public function redirect_to_setup_wizard_page() {
        if ( get_transient( 'tpsm_do_activation_redirect' ) ) {
            delete_transient('tpsm_do_activation_redirect');
        
            if( get_option( 'tpsm_is_setup_wizard', 0 ) ) {
                return;
            }
            wp_redirect( add_query_arg( 
                array(
                    'page'     => 'tpasg_setup_wizard',
                ),
                admin_url( 'admin.php' )
            ) );
            exit;
        }
    }

    public function add_setup_wizard_page() {
        add_menu_page(
            'Shipping Manager',                     // Page title
            'Shipping Manager',                     // Menu title (temporarily)
            'manage_options',                       
            'tpasg_setup_wizard',                    
            [ $this, 'render_setup_wizard_page' ],  // Callback
            '',                                     
            100                                     
        );

        add_submenu_page(
            null,                                   // No parent slug means it's hidden
            'Shipping Manager Setup Wizard',       // Page title
            'Setup Wizard',                        // Menu title (not shown)
            'manage_options',                      // Capability
            'tpasg_setup_wizard',                   // Menu slug
            [ $this, 'render_setup_wizard_page' ]  // Callback function
        );

        // Remove it right after adding to hide from menu
        remove_menu_page( 'tpasg_setup_wizard' );
    }

    public function render_setup_wizard_page() {
        printf( '%s', Utility::get_template( 'wizard/wizard.php' ) );
    }
}
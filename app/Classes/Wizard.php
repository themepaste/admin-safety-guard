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
                    'page'     => 'tpsm_setup_wizard',
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
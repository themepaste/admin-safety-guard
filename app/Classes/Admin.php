<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;

class Admin {

    use Hook;
    use Asset;

    
    private $settings;

    public function __construct() {
        $this->action( 'plugins_loaded', function() {
            $this->settings = new Settings();
            $this->settings->init();
        } );
        $this->action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_styles'] );
    }

    /**
     * Enqueues admin styles for the Secure Admin settings page.
     *
     * @param string $screen The current screen ID.
     *
     * @return void
     */
    public function admin_enqueue_styles( $screen ) {
		if ( 'toplevel_page_' . $this->settings::SETTING_PAGE_ID === $screen ) {
            $this->enqueue_style(
                'tpsa-settings',
                TPSA_ASSETS_URL . '/admin/css/settings.css'
            );
        }
		if ( 'toplevel_page_' . $this->settings::SETTING_PAGE_ID === $screen ) {
            $this->enqueue_style(
                'tpsa-fields',
                TPSA_ASSETS_URL . '/admin/css/fields.css'
            );
        }
	}

}
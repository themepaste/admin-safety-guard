<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Helpers\Utility;

class Notice {

    use Hook;
    use Asset;

    public function __construct() {
        $this->action( 'admin_notices', [$this, 'render_admin_notices'] );
        $this->action( 'admin_enqueue_scripts', [$this, 'enqueue_assets'] );
    }

    public function render_admin_notices() {

        $current_admin_page_slug = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

        // Don't show on setup wizard
        if( $current_admin_page_slug == 'tpasg_setup_wizard' ) {
            return;
        }

        // Only show to admin users
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Already completed? Then skip notice
        $setup_wizard_value = get_option( 'tpsm_is_setup_wizard', null );
        if ( $setup_wizard_value === '0' || $setup_wizard_value === '1' || $setup_wizard_value === 0 || $setup_wizard_value === 1 ) {
            return;
        }

        // Check if user dismissed the notice manually
        if ( get_user_meta( get_current_user_id(), 'tpsm_dismissed_setup_notice', true ) ) {
            return;
        }

        printf( '%s', Utility::get_template( 'notice/setup-wizard-notice.php' ) );
    }

    public function enqueue_assets() {
        $this->enqueue_style(
            'tpsm-notice',
            TPSA_ASSETS_URL . '/admin/css/notice.css',
        );
    }
}
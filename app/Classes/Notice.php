<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;
use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Renders the one-off "run the setup wizard" admin notice.
 */
class Notice {

    use Hook;
    use Asset;

    public function __construct() {
        $this->action( 'admin_notices', [$this, 'render_admin_notices'] );
        $this->action( 'admin_enqueue_scripts', [$this, 'enqueue_assets'] );
    }

    /**
     * Whether the setup-wizard notice should be rendered on this request.
     *
     * @return bool
     */
    private function should_show_notice() {
        // Never on the wizard screen itself.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing value.
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( 'tpasg_setup_wizard' === $page ) {
            return false;
        }

        if ( !current_user_can( 'manage_options' ) ) {
            return false;
        }

        // The wizard writes 0 or 1 once a choice has been made; anything else
        // (including the option being absent) means it has not run yet.
        $setup_wizard_value = get_option( 'tpsm_is_setup_wizard', null );
        if ( null !== $setup_wizard_value && in_array( (int) $setup_wizard_value, [0, 1], true ) ) {
            return false;
        }

        // Check if user dismissed the notice manually.
        if ( get_user_meta( get_current_user_id(), 'tpsm_dismissed_setup_notice', true ) ) {
            return false;
        }

        return true;
    }

    public function render_admin_notices() {
        if ( !$this->should_show_notice() ) {
            return;
        }

        printf( '%s', Utility::get_template( 'notice/setup-wizard-notice.php' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template escapes its own output.
    }

    /**
     * Load the notice stylesheet only when the notice is actually rendered,
     * rather than on every screen in wp-admin.
     *
     * @return void
     */
    public function enqueue_assets() {
        // The stylesheet also carries the security-audit notice, which can
        // appear on any admin screen, so load it whenever an administrator is
        // looking at wp-admin rather than only on the wizard prompt.
        if ( !$this->should_show_notice() && !current_user_can( 'manage_options' ) ) {
            return;
        }

        $this->enqueue_style(
            'tpsm-notice',
            TPSA_ASSETS_URL . '/admin/css/notice.css'
        );
    }
}

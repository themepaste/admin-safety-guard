<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Helpers\Utility;

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
	public static $SETTING_PAGE_ID = 'tp-secure-admin';

	/**
	 * Admin settings page URL.
	 *
	 * @var string
	 */
	public $setting_page_url = '';

	/**
	 * Constructor.
	 */
	public function init() {
		$this->setting_page_url = add_query_arg(
			[
				'page' => self::$SETTING_PAGE_ID,
			],
			admin_url( 'admin.php' )
		);

		$this->action( 'admin_menu', [ $this, 'register_settings_page' ] );
		$this->filter( 'plugin_action_links_' . TPSA_PLUGIN_BASENAME, [ $this, 'add_settings_link' ] );

		//Process and save settings
		$this->action( 'admin_post_tpsa_process_form', [ FormProcessor::class, 'process_form' ] );
	}

	/**
	 * Registers the settings page in the admin menu.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		add_menu_page(
			esc_html__( 'Secure Admin', 'shipping-manager' ),
			esc_html__( 'Secure Admin', 'shipping-manager' ),
			'manage_options',
			self::$SETTING_PAGE_ID,
			[ $this, 'render_settings_page' ],
			'dashicons-lock',
			56
		);
	}

	/**
	 * Renders the settings page layout.
	 *
	 * @return void
	 */
	public function render_settings_page() {

		// if ( ! isset( $_GET['tpsa-setting'] ) ) {
        //     $redirect_url = add_query_arg(
        //         [
        //             'tpsa-setting'  => 'dashboard',
        //         ],
        //         $this->setting_page_url
        //     );

        //     wp_safe_redirect( $redirect_url );
        //     exit;
        // }

        printf( '%s', Utility::get_template( 'settings/layout.php' ) );
	}

	/**
	 * Adds a "Settings" link to the plugin actions.
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $this->setting_page_url ),
			esc_html__( 'Settings', 'shipping-manager' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}

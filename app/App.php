<?php
/**
 * Main application class for initializing the plugin.
 *
 * @package ThemePaste\SecureAdmin
 */

namespace ThemePaste\SecureAdmin;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Final Class App
 *
 * Responsible for bootstrapping all necessary plugin components.
 */
final class App {

	use Hook;

	/**
	 * Initialize all plugin hooks and core components.
	 *
	 * This method sets up both frontend and backend functionalities.
	 *
	 * @return void
	 */
	public static function hooks() {

		// Register activation-related setup such as DB installation, version check, etc.
		// new Classes\Install();

		// Load common functionality (AJAX, scripts, etc.)
		new Classes\Common();

		// Register shipping methods setup.
		// new Classes\Shipping\ShippingMethods();

		// Register admin-specific hooks and classes.
		if ( is_admin() ) {
			new Classes\Admin();
		}

		// Register frontend-specific hooks and classes.
		if ( ! is_admin() ) {
			// new Classes\Front();
		}
	}
}

<?php 

namespace ThemePaste\SecureAdmin\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Utility class with static helper functions for general use throughout the plugin.
 */
class Utility {

    /**
	 * Prints information about a variable in a more readable format.
	 *
	 * @param mixed $data The variable you want to display.
	 * @param bool  $admin_only Should it display in wp-admin area only
	 * @param bool  $hide_adminbar Should it hide the admin bar
	 */
	public static function pri( $data, $admin_only = true, $hide_adminbar = true ) {
		if ( $admin_only && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<pre>';
		if ( is_object( $data ) || is_array( $data ) ) {
			print_r( $data );
		} else {
			var_dump( $data );
		}
		echo '</pre>';

		if ( is_admin() && $hide_adminbar ) {
			echo '<style>#adminmenumain{display:none;}</style>';
		}
	}

    /**
	 * Includes a template file from the 'view' directory.
	 *
	 * @param string $template The template file name.
	 * @param array  $args Optional. An associative array of variables to pass to the template file.
	 */
	public static function get_template( $template, $args = array() ) {
		$path = TPSA_PLUGIN_DIR . 'views/' . $template;

		if ( file_exists( $path ) ) {
			if ( ! empty( $args ) && is_array( $args ) ) {
				extract( $args );
			}

			ob_start();
			include $path;
			return ob_get_clean();
		}
	}

	/**
	 * Includes a template file from the 'tp-secure-plugin-pro/views' directory.
	 *
	 * This method is used to load a view/template file specifically from the pro version
	 * of the plugin. It supports passing variables to the template via an associative array.
	 *
	 * @param string $template The relative path to the template file inside the 'tp-secure-plugin-pro/views/' directory.
	 * @param array  $args     Optional. An associative array of variables to extract into the template's scope.
	 *
	 * @return string|null The output of the template file, or null if the file doesn't exist.
	 */
	public static function get_pro_template( $template, $args = array() ) {
		// if ( is_plugin_active( 'secure-admin-pro/admin-safety-guard-pro.php' ) ) { 
			$path = TPASG_PRO_REAL_PATH . '/secure-admin-pro/views/' . $template;
	
			if ( file_exists( $path ) ) {
				if ( ! empty( $args ) && is_array( $args ) ) {
					extract( $args );
				}
	
				ob_start();
				include $path;
				return ob_get_clean();
			}
		// }
	} 

	/**
	 * @param string $var the variable name 
	 * @return string
	 */
	public static function get_screen( $var = '' ) {
		return isset( $_GET[$var]) ? sanitize_text_field( $_GET[$var] ) : null;
	}


}
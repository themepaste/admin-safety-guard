<?php
/*
Plugin Name: Admin Safety Guard
Plugin URI: http://themepaste.com/product/themepaste-secure-admin-pro/
Description: Secure your WordPress login with Admin safety guard to ensure secured access with limit login attempts, 2FA, reCaptcha, IP Blocking, Disable XML-RPC and activity tracking.
Version: 1.2.1
Author: Themepaste Team
Author URI: http://themepaste.com/
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: admin-safety-guard
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Plugin Main Class
 */
final class TPSucureAdmin {
    static $instance = false;

    /**
     * Class Constructor
     */
    private function __construct() {
        $this->define();
        $this->include();
        ThemePaste\SecureAdmin\App::init();
    }

    /**
     * define all constant
     */
    private function define() {
        define( "TPSA_DEVS", false ); // 'true' | is development mode on

        define( 'TPSA_PLUGIN_FILE', __FILE__ );
        define( 'TPSA_PREFIX', 'tpsa' );
        define( 'TPSA_PLUGIN_VERSION', '1.2.1' );
        define( 'TPSA_PLUGIN_DIRNAME', dirname( TPSA_PLUGIN_FILE ) );
        define( 'TPSA_PLUGIN_BASENAME', plugin_basename( TPSA_PLUGIN_FILE ) );
        define( 'TPSA_PLUGIN_DIR', plugin_dir_path( TPSA_PLUGIN_FILE ) );
        define( 'TPSA_PLUGIN_URL', plugin_dir_url( TPSA_PLUGIN_FILE ) );
        define( 'TPSA_ASSETS_URL', plugins_url( 'assets', TPSA_PLUGIN_FILE ) );
        define( 'TPSA_REAL_PATH', realpath( dirname( TPSA_PLUGIN_DIR ) ) );

        if ( TPSA_DEVS ) {
            define( 'TPSA_ASSETS_VERSION', time() );
        } else {
            define( 'TPSA_ASSETS_VERSION', TPSA_PLUGIN_VERSION );
        }
    }

    /**
     * Include all needed files
     */
    private function include() {
        // Include custom helper functions from the inc/functions.php file
        require_once dirname( __FILE__ ) . '/inc/functions.php';

        /**
         * Check if the Composer autoloader class for TPShippingManager exists.
         *
         * The class name usually includes the suffix defined in the composer.json
         * file, typically something like 'ComposerAutoloaderInitTPShippingManager'.
         *
         * If the class does not exist, include the Composer autoloader file to
         * register the necessary autoload mappings.
         */
        // if ( ! class_exists( 'ComposerAutoloaderInitTPShippingManager' ) ) {
        require_once dirname( __FILE__ ) . '/vendor/autoload.php';
        // }
    }

    /**
     * Singleton Instance
     */
    static function get_instance() {

        if ( !self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Plugin Start
 */
TPSucureAdmin::get_instance();
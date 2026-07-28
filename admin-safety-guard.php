<?php
/*
Plugin Name: Admin Safety Guard
Plugin URI: http://themepaste.com/product/themepaste-secure-admin-pro/
Description: Secure your WordPress login with Admin safety guard to ensure secured access with limit login attempts, 2FA, reCaptcha, IP Blocking, Disable XML-RPC and activity tracking.
Version: 1.3.0
Author: Themepaste Team
Author URI: http://themepaste.com/
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: admin-safety-guard
Requires at least: 5.8
Requires PHP: 8.0
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
        define( 'TPSA_PLUGIN_FILE', __FILE__ );
        define( 'TPSA_PREFIX', 'tpsa' );
        define( 'TPSA_PLUGIN_VERSION', '1.3.0' );
        define( 'TPSA_PLUGIN_BASENAME', plugin_basename( TPSA_PLUGIN_FILE ) );
        define( 'TPSA_PLUGIN_DIR', plugin_dir_path( TPSA_PLUGIN_FILE ) );
        define( 'TPSA_ASSETS_URL', plugins_url( 'assets', TPSA_PLUGIN_FILE ) );

        // Cache-bust assets per release; SCRIPT_DEBUG busts on every load so
        // local development picks up rebuilt bundles without a hard refresh.
        define(
            'TPSA_ASSETS_VERSION',
            ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? (string) time() : TPSA_PLUGIN_VERSION
        );
    }

    /**
     * Include all needed files
     */
    private function include() {
        // Include custom helper functions from the inc/functions.php file
        require_once dirname( __FILE__ ) . '/inc/functions.php';

        require_once dirname( __FILE__ ) . '/vendor/autoload.php';
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
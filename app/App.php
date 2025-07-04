<?php
/**
 * Main application class for initializing the plugin.
 *
 * @package ThemePaste\SecureAdmin
 */

namespace ThemePaste\SecureAdmin;

defined( 'ABSPATH' ) || exit;
/**
 * Final Class App
 *
 * Responsible for bootstrapping all necessary plugin components.
 */
final class App {

    /**
     * Holds the instances of the loaded classes.
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Initialize the plugin.
     *
     * @return void
     */
    public static function init() {
        self::hooks();
    }

    /**
     * Get an instance of a class.
     *
     * @param string $class The class name to retrieve.
     *
     * @return object The class instance.
     */
    public static function get( $class ) {
        if ( ! isset( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new $class();
        }

        return self::$instances[ $class ];
    }

    /**
     * Initialize all plugin hooks and core components.
     *
     * This method sets up both frontend and backend functionalities.
     *
     * @return void
     */
    public static function hooks() {
        // Register activation-related setup such as DB installation, version check, etc.
        self::get( Classes\Install::class );

        // Load common functionality (AJAX, scripts, etc.)
        self::get( Classes\Common::class );

        // Load all features
        self::get( Classes\FeatureManager::class );

        // Register admin-specific hooks and classes.
        if ( is_admin() ) {
            self::get( Classes\Admin::class );
        }

        // Register frontend-specific hooks and classes.
        if ( ! is_admin() ) {
            // self::get( Classes\Front::class );
        }
    }
}

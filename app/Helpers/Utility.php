<?php

namespace ThemePaste\SecureAdmin\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Utility class with static helper functions for general use throughout the plugin.
 */
class Utility {

    /**
     * Includes a template file from the 'views' directory.
     *
     * @param string $template The template file name, relative to views/.
     * @param array  $args     Optional. Variables made available to the template as $args.
     *
     * @return string The rendered template, or an empty string when it does not exist.
     */
    public static function get_template( $template, $args = array() ) {
        // Templates are addressed by hard-coded relative paths, but normalise
        // anyway so a future caller cannot traverse out of views/.
        $template = ltrim( str_replace( array( '..', "\0" ), '', (string) $template ), '/' );
        $path = TPSA_PLUGIN_DIR . 'views/' . $template;

        if ( !file_exists( $path ) ) {
            return '';
        }

        // Templates read both $args and the individual keys. EXTR_SKIP keeps a
        // stray key from clobbering $path / $template / $args themselves.
        if ( !empty( $args ) && is_array( $args ) ) {
            extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        }

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    /**
     * Includes a template file from the 'admin-safety-guard-pro/views' directory.
     *
     * This method is used to load a view/template file specifically from the pro version
     * of the plugin. It supports passing variables to the template via an associative array.
     *
     * @param string $template The relative path to the template file inside the 'admin-safety-guard-pro/views/' directory.
     * @param array  $args     Optional. An associative array of variables to extract into the template's scope.
     *
     * @return string The output of the template file, or an empty string if unavailable.
     */
    public static function get_pro_template( $template, $args = array() ) {
        // tp_is_pro_active() loads wp-admin/includes/plugin.php on demand;
        // calling is_plugin_active() directly is a fatal error on the front end.
        // TPASG_PRO_REAL_PATH is defined by the pro plugin, so it must be
        // checked before use rather than assumed.
        if ( !tp_is_pro_active() || !defined( 'TPASG_PRO_REAL_PATH' ) ) {
            return '';
        }

        $template = ltrim( str_replace( array( '..', "\0" ), '', (string) $template ), '/' );
        $path = TPASG_PRO_REAL_PATH . '/admin-safety-guard-pro/views/' . $template;

        if ( !file_exists( $path ) ) {
            return '';
        }

        if ( !empty( $args ) && is_array( $args ) ) {
            extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        }

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    /**
     * Read a routing/UI query var.
     *
     * @param string $var The query var name.
     * @return string Sanitized value, or an empty string when absent.
     */
    public static function get_screen( $var = '' ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing/UI value, no state change.
        return isset( $_GET[$var] ) ? sanitize_key( wp_unslash( $_GET[$var] ) ) : '';
    }

}
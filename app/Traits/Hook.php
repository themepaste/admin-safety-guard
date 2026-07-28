<?php

namespace ThemePaste\SecureAdmin\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Thin wrappers around the WordPress hook API.
 *
 * Every registration is guarded by is_callable() so a typo in a callback name
 * fails quietly at registration time instead of fataling when the hook fires.
 */
trait Hook {

    /**
     * Registers an action hook.
     *
     * @param string   $tag           Hook name.
     * @param callable $callback      Callback.
     * @param int      $priority      Hook priority.
     * @param int      $accepted_args Number of arguments the callback accepts.
     *
     * @return void
     */
    public function action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        if ( is_callable( $callback ) ) {
            add_action( $tag, $callback, $priority, $accepted_args );
        }
    }

    /**
     * Registers a filter hook.
     *
     * @param string   $tag           Hook name.
     * @param callable $callback      Callback.
     * @param int      $priority      Hook priority.
     * @param int      $accepted_args Number of arguments the callback accepts.
     *
     * @return void
     */
    public function filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        if ( is_callable( $callback ) ) {
            add_filter( $tag, $callback, $priority, $accepted_args );
        }
    }

    /**
     * Registers an AJAX action for logged-in users.
     *
     * @param string   $action   AJAX action name (without the wp_ajax_ prefix).
     * @param callable $callback Callback.
     *
     * @return void
     */
    public function ajax_priv( $action, $callback ) {
        if ( is_callable( $callback ) ) {
            add_action( 'wp_ajax_' . $action, $callback );
        }
    }

    /**
     * Registers the plugin activation hook.
     *
     * @param callable $callback Callback.
     *
     * @return void
     */
    public function activation( $callback ) {
        if ( is_callable( $callback ) ) {
            register_activation_hook( TPSA_PLUGIN_FILE, $callback );
        }
    }
}

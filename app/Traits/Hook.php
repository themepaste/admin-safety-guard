<?php 

namespace ThemePaste\SecureAdmin\Traits;

defined( 'ABSPATH' ) || exit;

trait Hook {

	/**
	 * Registers an action hook.
	 */
	public function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		if ( is_callable( $callback ) ) {
			add_action( $tag, $callback, $priority, $accepted_args );
		}
	}
	public function action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add_action( $tag, $callback, $priority, $accepted_args );
	}

	/**
	 * Registers a filter hook.
	 */
	public function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		if ( is_callable( $callback ) ) {
			add_filter( $tag, $callback, $priority, $accepted_args );
		}
	}
	public function filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add_filter( $tag, $callback, $priority, $accepted_args );
	}

	/**
	 * Registers a shortcode.
	 */
	public function add_shortcode( $tag, $callback ) {
		if ( is_callable( $callback ) ) {
			add_shortcode( $tag, $callback );
		}
	}
	public function shortcode( $tag, $callback ) {
		$this->add_shortcode( $tag, $callback );
	}

	/**
	 * Registers an AJAX action for logged-in users.
	 */
	public function ajax_priv( $action, $callback ) {
		if ( is_callable( $callback ) ) {
			add_action( 'wp_ajax_' . $action, $callback );
		}
	}

	/**
	 * Registers an AJAX action for non-logged-in users.
	 */
	public function ajax_nopriv( $action, $callback ) {
		if ( is_callable( $callback ) ) {
			add_action( 'wp_ajax_nopriv_' . $action, $callback );
		}
	}

	/**
	 * Registers both logged-in and non-logged-in AJAX actions.
	 */
	public function ajax( $action, $callback ) {
		if ( is_callable( $callback ) ) {
			$this->ajax_priv( $action, $callback );
			$this->ajax_nopriv( $action, $callback );
		}
	}

    /**
     * Register Activation Hook
     */
    public function register_activation_hook( $callback ){
        if( is_callable( $callback ) ) {
            register_activation_hook( TPSA_PLUGIN_FILE, $callback );
        }
    }
    public function activation( $callback ) {
        $this->register_activation_hook( $callback );
    }

}
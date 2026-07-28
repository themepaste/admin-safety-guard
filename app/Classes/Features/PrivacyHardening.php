<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Classes\ThreatLog;
use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: PrivacyHardening
 *
 * Closes the information leaks and legacy entry points that attackers use to
 * profile a WordPress site before attacking it.
 *
 * Each option is a single hook registered only when it is switched on, so an
 * unused option costs nothing at runtime.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class PrivacyHardening implements FeatureInterface {

    use Hook;

    /**
     * Settings screen slug.
     *
     * @var string
     */
    private $features_id = 'privacy-hardening';

    /**
     * Cached settings.
     *
     * @var array|null
     */
    private $settings = null;

    public function register_hooks() {
        // One option read, then only the hooks that are actually needed.
        if ( $this->on( 'xml-rpc-enable' ) ) {
            $this->disable_xmlrpc();
        }

        if ( $this->on( 'block-author-enum' ) ) {
            $this->action( 'template_redirect', [$this, 'block_author_enumeration'], 0 );
            $this->filter( 'rest_endpoints', [$this, 'restrict_user_endpoints'] );
        }

        if ( $this->on( 'hide-version' ) ) {
            $this->hide_version();
        }

        if ( $this->on( 'remove-meta' ) ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }

        if ( $this->on( 'disable-pingback' ) ) {
            $this->filter( 'xmlrpc_methods', [$this, 'remove_pingback_methods'] );
            $this->filter( 'wp_headers', [$this, 'remove_pingback_header'] );
            $this->action( 'pre_ping', [$this, 'block_self_pings'] );
        }

        if ( $this->on( 'generic-login-errors' ) ) {
            $this->filter( 'login_errors', [$this, 'generic_login_error'] );
        }

        if ( $this->on( 'disable-file-edit' ) ) {
            $this->action( 'init', [$this, 'disable_file_editing'] );
        }

        if ( $this->on( 'security-headers' ) ) {
            $this->filter( 'wp_headers', [$this, 'add_security_headers'] );
            $this->action( 'send_headers', [$this, 'send_security_headers'] );
        }

        if ( $this->on( 'disable-app-passwords' ) ) {
            $this->filter( 'wp_is_application_passwords_available', '__return_false' );
        }
    }

    /* ---------------------------------------------------------------------
     * XML-RPC
     * ------------------------------------------------------------------- */

    /**
     * Turn XML-RPC off completely.
     *
     * The filter alone is not enough: xmlrpc.php still responds, so the
     * endpoint is answered with a 403 as well. The advertising header and RSD
     * link are removed so the endpoint is not announced in the first place.
     *
     * @return void
     */
    private function disable_xmlrpc() {
        $this->filter( 'xmlrpc_enabled', '__return_false' );
        $this->filter( 'xmlrpc_methods', '__return_empty_array' );
        $this->filter( 'wp_headers', [$this, 'remove_pingback_header'] );

        remove_action( 'wp_head', 'rsd_link' );

        $this->action( 'init', [$this, 'reject_xmlrpc_requests'], 0 );
    }

    /**
     * Answer a direct xmlrpc.php request with a 403.
     *
     * @return void
     */
    public function reject_xmlrpc_requests() {
        if ( !defined( 'XMLRPC_REQUEST' ) || !XMLRPC_REQUEST ) {
            return;
        }

        ThreatLog::report( 'xmlrpc_blocked' );

        status_header( 403 );
        nocache_headers();
        header( 'Content-Type: text/plain; charset=utf-8' );

        echo esc_html__( 'XML-RPC services are disabled on this site.', 'admin-safety-guard' );
        exit;
    }

    /**
     * Strip the pingback methods from the XML-RPC method list.
     *
     * @param array $methods Registered methods.
     * @return array
     */
    public function remove_pingback_methods( $methods ) {
        unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );

        return $methods;
    }

    /**
     * Remove the X-Pingback response header.
     *
     * @param array $headers Response headers.
     * @return array
     */
    public function remove_pingback_header( $headers ) {
        unset( $headers['X-Pingback'] );

        return $headers;
    }

    /**
     * Stop the site pinging itself.
     *
     * @param array $links Links to ping.
     * @return void
     */
    public function block_self_pings( &$links ) {
        $home = home_url();

        foreach ( array_keys( (array) $links ) as $key ) {
            if ( 0 === strpos( $links[$key], $home ) ) {
                unset( $links[$key] );
            }
        }
    }

    /* ---------------------------------------------------------------------
     * Username enumeration
     * ------------------------------------------------------------------- */

    /**
     * Block ?author=N scanning.
     *
     * Requesting /?author=1 normally redirects to /author/admin/, handing an
     * attacker a real username to brute-force. This is the single most common
     * reconnaissance step against WordPress.
     *
     * @return void
     */
    public function block_author_enumeration() {
        if ( is_admin() || is_user_logged_in() ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public query var.
        $author = isset( $_GET['author'] ) ? sanitize_text_field( wp_unslash( $_GET['author'] ) ) : '';

        // Only numeric probes are enumeration; a real /author/name/ archive is
        // a legitimate page and is left alone.
        if ( '' === $author || !is_numeric( $author ) ) {
            return;
        }

        ThreatLog::report( 'author_enum', 'author=' . $author );

        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }

    /**
     * Require authentication for the REST user endpoints.
     *
     * /wp-json/wp/v2/users lists every account's name and slug to anonymous
     * visitors by default, which is enumeration by another route.
     *
     * @param array $endpoints REST endpoints.
     * @return array
     */
    public function restrict_user_endpoints( $endpoints ) {
        if ( is_user_logged_in() ) {
            return $endpoints;
        }

        foreach ( ['/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)'] as $route ) {
            if ( isset( $endpoints[$route] ) ) {
                unset( $endpoints[$route] );
            }
        }

        return $endpoints;
    }

    /* ---------------------------------------------------------------------
     * Version disclosure
     * ------------------------------------------------------------------- */

    /**
     * Hide the WordPress version from the page source, feeds and asset URLs.
     *
     * The version tells an attacker exactly which published exploits to try.
     *
     * @return void
     */
    private function hide_version() {
        remove_action( 'wp_head', 'wp_generator' );

        $this->filter( 'the_generator', '__return_empty_string' );
        $this->filter( 'style_loader_src', [$this, 'strip_version_query'], 9999 );
        $this->filter( 'script_loader_src', [$this, 'strip_version_query'], 9999 );
    }

    /**
     * Remove ?ver=x.y.z from core asset URLs.
     *
     * Only core's own version is stripped: plugin and theme assets keep their
     * version string, because that is what busts their caches on update.
     *
     * @param string $src Asset URL.
     * @return string
     */
    public function strip_version_query( $src ) {
        if ( !is_string( $src ) || false === strpos( $src, 'ver=' ) ) {
            return $src;
        }

        global $wp_version;

        if ( false === strpos( $src, 'ver=' . $wp_version ) ) {
            return $src;
        }

        return remove_query_arg( 'ver', $src );
    }

    /* ---------------------------------------------------------------------
     * Misc
     * ------------------------------------------------------------------- */

    /**
     * Replace login errors with a single generic message.
     *
     * By default WordPress says "The username X is not registered" versus
     * "The password you entered is incorrect", which confirms which accounts
     * exist. One message for both reveals nothing.
     *
     * @param string $error Original error markup.
     * @return string
     */
    public function generic_login_error( $error ) {
        // Leave our own lockout and two-factor messages intact: those are
        // deliberate feedback, not account disclosure.
        if ( is_string( $error ) && false !== strpos( $error, 'tpsa-' ) ) {
            return $error;
        }

        return __( '<strong>Error:</strong> The username or password you entered is incorrect.', 'admin-safety-guard' );
    }

    /**
     * Turn off the built-in theme and plugin file editors.
     *
     * A stolen administrator session can otherwise be turned into arbitrary
     * PHP execution in two clicks.
     *
     * @return void
     */
    public function disable_file_editing() {
        if ( !defined( 'DISALLOW_FILE_EDIT' ) ) {
            define( 'DISALLOW_FILE_EDIT', true );
        }
    }

    /* ---------------------------------------------------------------------
     * Security headers
     * ------------------------------------------------------------------- */

    /**
     * The headers we send, and why.
     *
     * Deliberately excludes Content-Security-Policy: a useful CSP has to be
     * written per-site, and a generic one breaks page builders, embeds and
     * analytics. These four are safe on essentially any site.
     *
     * @return array<string, string>
     */
    private function header_list() {
        return [
            // Stop the site being framed by another origin (clickjacking).
            'X-Frame-Options'        => 'SAMEORIGIN',
            // Stop browsers guessing a different content type than declared,
            // which is how an uploaded "image" gets executed as script.
            'X-Content-Type-Options' => 'nosniff',
            // Do not leak the full URL of admin pages to external sites.
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            // Deny access to hardware no WordPress page needs by default.
            'Permissions-Policy'     => 'geolocation=(), microphone=(), camera=(), interest-cohort=()',
        ];
    }

    /**
     * Add the headers to WordPress's own response header list.
     *
     * @param array $headers Existing headers.
     * @return array
     */
    public function add_security_headers( $headers ) {
        foreach ( $this->header_list() as $name => $value ) {
            // Never clobber a header the server or another plugin already set.
            if ( !isset( $headers[$name] ) ) {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * Also send them on responses that bypass the wp_headers filter, such as
     * the admin screens.
     *
     * @return void
     */
    public function send_security_headers() {
        if ( headers_sent() ) {
            return;
        }

        $existing = [];

        foreach ( headers_list() as $header ) {
            $parts = explode( ':', $header, 2 );
            $existing[strtolower( trim( $parts[0] ) )] = true;
        }

        foreach ( $this->header_list() as $name => $value ) {
            if ( !isset( $existing[strtolower( $name )] ) ) {
                header( $name . ': ' . $value, false );
            }
        }
    }

    /* ---------------------------------------------------------------------
     * Settings
     * ------------------------------------------------------------------- */

    /**
     * Whether an option is switched on.
     *
     * @param string $key Option key.
     * @return bool
     */
    private function on( $key ) {
        $settings = $this->get_settings();

        return !empty( $settings[$key] );
    }

    private function get_settings() {
        if ( null === $this->settings ) {
            $settings = get_option( get_tpsa_settings_option_name( $this->features_id ), [] );
            $this->settings = is_array( $settings ) ? $settings : [];
        }

        return $this->settings;
    }
}

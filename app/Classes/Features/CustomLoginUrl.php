<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class CustomLoginUrl implements FeatureInterface {
    use Hook;

    private $features_id = 'custom-login-url';
    private $custom_login_slug = '';
    private $redirect_slug = '';
    private $logout_url = '';

    public function register_hooks() {
        $settings = $this->get_settings();

        // Get the custom login slug from settings, fallback to empty string.
        // sanitize_title() guarantees a URL-safe slug: without it a value such as
        // "log.in" or "a|b" would be injected straight into the rewrite rule and
        // into the regex used by force_custom_login_url().
        $this->custom_login_slug = !empty( $settings['login-url'] ) ? sanitize_title( trim( $settings['login-url'], '/' ) ) : '';
        $this->redirect_slug = !empty( $settings['redirect-url'] ) ? trim( $settings['redirect-url'], '/' ) : '';
        $this->logout_url = !empty( $settings['logout-url'] ) ? trim( $settings['logout-url'], '/' ) : '';

        if ( $this->is_enabled( $settings ) && !empty( $this->custom_login_slug ) ) {
            $this->action( 'init', [$this, 'rewrite_url'] );
            $this->action( 'init', [$this, 'show_404'] );
            $this->action( 'init', [$this, 'redirect_wp_admin'] );
            $this->action( 'template_redirect', [$this, 'force_custom_login_url'] );

            $this->filter( 'site_url', [$this, 'override_site_url'], 10, 4 );
        }

        if ( $this->is_enabled( $settings ) && !empty( $this->logout_url ) ) {
            $this->filter( 'logout_redirect', [$this, 'logout_redirect'], 10, 3 );
        }

        $this->action( 'updated_option', function ( $option_name, $old_value, $new_value ) {
            if ( $option_name === 'tpsa_custom-login-url_settings' ) {
                $keys_to_check = ['login-url', 'redirect-url', 'logout-url'];

                foreach ( $keys_to_check as $key ) {
                    if ( isset( $old_value[$key] ) && isset( $new_value[$key] ) && $old_value[$key] !== $new_value[$key] ) {
                        flush_rewrite_rules();
                        break; // No need to continue checking once we've flushed
                    }
                }
            }
        }, 10, 3 );

        $this->filter( 'tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_logout-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_redirect-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
    }

    /**
     * The requested path, relative to this site's root.
     *
     * REQUEST_URI is absolute to the domain, so on a sub-directory install
     * (example.com/blog/) or a sub-directory multisite network
     * (example.com/site2/) it carries a prefix that has to be removed before the
     * login slug can be matched. Without this, the custom login URL silently
     * never matches on either of those very common setups.
     *
     * Query string is dropped: only the path takes part in slug matching.
     *
     * @return string Path beginning with a single slash.
     */
    private function get_request_path() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
        : '';

        if ( '' === $request_uri ) {
            return '';
        }

        $path = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
        $path = '/' . ltrim( $path, '/' );

        $home_path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
        $home_path = '/' . trim( $home_path, '/' );

        if ( '/' !== $home_path ) {
            // This site lives under a base path, so only requests inside that
            // path can be its login URL. Anything else belongs to a different
            // site on the network (or outside WordPress) and must not match.
            if ( 0 !== strpos( $path, $home_path . '/' ) && $path !== $home_path ) {
                return '';
            }

            $path = '/' . ltrim( substr( $path, strlen( $home_path ) ), '/' );
        }

        return $path;
    }

    /**
     * Override all WordPress-generated login URLs (login, register, lost password, etc.)
     */
    public function override_site_url( $url, $path, $scheme, $blog_id ) {
        if ( strpos( $url, 'wp-login.php' ) !== false ) {
            $url = str_replace( 'wp-login.php', $this->custom_login_slug, $url );
        }
        return $url;
    }

    /**
     * Redirect /wp-admin to home if not logged in
     */
    public function redirect_wp_admin() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $admin_path  = wp_parse_url( admin_url(), PHP_URL_PATH );

        if ( ! empty( $request_uri ) && strpos( $request_uri, $admin_path ) === 0 && ! is_user_logged_in() ) {
            $redirect_url = ! empty( $this->redirect_slug ) ? home_url( $this->redirect_slug ) : home_url( '/' );
            wp_safe_redirect( $redirect_url );
            exit();
        }
    }

    /**
     * Show 404 if /wp-login.php is accessed
     */
    public function show_404() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        // Only proceed if custom slug is set
        if (
            ! empty( $this->custom_login_slug ) &&
            ! empty( $request_uri ) &&
            strpos( $request_uri, 'wp-login.php' ) !== false &&
            strpos( $request_uri, '/' . $this->custom_login_slug ) === false
        ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();

            $template_404 = get_404_template();
            if ( $template_404 && file_exists( $template_404 ) ) {
                include $template_404;
            } else {
                // Fallback plain 404 message
                wp_die( '404 - Page not found.' );
            }

            exit;
        }
    }

    /**
     * Rewrite /habib-login → wp-login.php with all query args
     */
    public function rewrite_url() {
        add_rewrite_rule( '^' . preg_quote( $this->custom_login_slug, '/' ) . '/?$', 'wp-login.php', 'top' );
    }

    /**
     * Force WordPress to parse /habib-login as wp-login.php
     */
    public function force_custom_login_url() {
        // Normalize custom login slug match. preg_quote() stops any regex
        // metacharacter that survives sanitisation from altering the pattern.
        if ( preg_match( '#^/' . preg_quote( $this->custom_login_slug, '#' ) . '(/|$)#', $this->get_request_path() ) ) {

            // Define missing expected variables to avoid PHP warnings
            global $user_login, $error;

            // Set them to empty string if not set
            if ( !isset( $user_login ) ) {
                $user_login = '';
            }

            if ( !isset( $error ) ) {
                $error = '';
            }

            require ABSPATH . 'wp-login.php';
            exit;
        }
    }

    public function logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
        // The filter is only registered when a logout URL is configured, so the
        // already-resolved property is authoritative here.
        return !empty( $this->logout_url ) ? home_url( $this->logout_url ) : $redirect_to;
    }

    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->features_id );
        return get_option( $option_name, [] );
    }

    private function is_enabled( $settings ) {
        return isset( $settings['enable'] ) && (int) $settings['enable'] === 1;
    }

    /**
     * Add site URL in login/logout input
     */
    public function modify_the_custom_login_logout_url_field( $template, $args ) {
        $site_url = get_site_url();
        $template = str_replace(
            '<input type="text" id="%2$s" name="%2$s" value="%3$s">',
            '<div class="tp-site-url-input"><span>' . $site_url . '/</span><input type="text" id="%2$s" name="%2$s" value="%3$s"></div>',
            $template
        );
        return $template;
    }
}
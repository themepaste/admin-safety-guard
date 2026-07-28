<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: CustomLoginUrl
 *
 * Moves the sign-in screen to a private slug and closes every default entry
 * point to it.
 *
 * Environment support: the request path is always resolved relative to the
 * site's own home path, so this behaves identically on a root install, on
 * WordPress in a sub-folder (example.com/blog/), and on both sub-domain and
 * sub-directory multisite networks.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class CustomLoginUrl implements FeatureInterface {

    use Hook;

    private $features_id = 'custom-login-url';
    private $custom_login_slug = '';
    private $redirect_slug = '';
    private $logout_url = '';

    /**
     * Set when the current request is for the custom login URL.
     *
     * @var bool
     */
    private $is_login_request = false;

    /**
     * Slugs that must never be used as the login URL, because they collide
     * with a real WordPress path and would make the site unreachable.
     *
     * @var string[]
     */
    private $reserved = [
        'wp-admin', 'wp-login', 'wp-login-php', 'wp-content', 'wp-includes',
        'wp-json', 'wp-cron', 'wp-signup', 'wp-activate', 'wp-trackback',
        'wp-comments-post', 'xmlrpc', 'index-php', 'feed', 'admin',
    ];

    public function register_hooks() {
        $settings = $this->get_settings();

        // sanitize_title() guarantees a URL-safe slug; without it a value like
        // "log.in" or "a|b" would end up in a rewrite rule and in a regex.
        $this->custom_login_slug = !empty( $settings['login-url'] ) ? sanitize_title( trim( (string) $settings['login-url'], '/' ) ) : '';
        $this->redirect_slug = !empty( $settings['redirect-url'] ) ? trim( (string) $settings['redirect-url'], '/' ) : '';
        $this->logout_url = !empty( $settings['logout-url'] ) ? trim( (string) $settings['logout-url'], '/' ) : '';

        // Settings-screen decorations always run so the fields render correctly.
        $this->filter( 'tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_logout-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_redirect-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );

        if ( !$this->is_enabled( $settings ) ) {
            return;
        }

        // A slug that is empty or collides with a core path would lock everyone
        // out, so refuse to activate and tell the administrator why.
        if ( '' === $this->custom_login_slug || $this->is_reserved( $this->custom_login_slug ) ) {
            $this->action( 'admin_notices', [$this, 'render_invalid_slug_notice'] );
            return;
        }

        // Intercept before WordPress decides what this request is.
        // pluggable.php is loaded at wp-settings.php:604, i.e. before
        // plugins_loaded fires, so redirects and auth checks are available here.
        $this->action( 'plugins_loaded', [$this, 'intercept_request'], 1 );

        // Serve the login screen once WordPress is fully loaded.
        $this->action( 'wp_loaded', [$this, 'serve_login_screen'] );

        // Keep logged-out visitors out of wp-admin without exposing the login URL.
        $this->action( 'init', [$this, 'protect_admin_area'] );

        // Rewrite every URL WordPress generates for the login screen.
        $this->filter( 'site_url', [$this, 'override_site_url'], 10, 4 );
        $this->filter( 'network_site_url', [$this, 'override_network_site_url'], 10, 3 );
        $this->filter( 'wp_redirect', [$this, 'override_redirect'], 10, 2 );

        if ( !empty( $this->logout_url ) ) {
            $this->filter( 'logout_redirect', [$this, 'logout_redirect'], 10, 3 );
        }

        // Confirm the active address after saving, so nobody loses their way in.
        $this->action( 'admin_notices', [$this, 'render_active_url_notice'] );
    }

    /* ---------------------------------------------------------------------
     * Request interception
     * ------------------------------------------------------------------- */

    /**
     * Decide, as early as possible, whether this request is the custom login
     * screen or an attempt to reach a default one.
     *
     * @return void
     */
    public function intercept_request() {
        // Never interfere with machine traffic.
        if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }

        $path = $this->get_request_path();

        if ( '' === $path ) {
            return;
        }

        $slug = trim( $path, '/' );

        // 1) The custom login URL: pretend we are wp-login.php from here on, so
        //    conditional tags and other plugins that key off $pagenow behave
        //    exactly as they would on the real login screen.
        if ( $slug === $this->custom_login_slug ) {
            $this->is_login_request = true;
            $GLOBALS['pagenow'] = 'wp-login.php';
            return;
        }

        // 2) A default login entry point. Everything below here is blocked.
        if ( !$this->is_default_login_path( $slug ) ) {
            return;
        }

        // Post-password forms post to wp-login.php?action=postpass and have
        // nothing to do with signing in; blocking them breaks every
        // password-protected post on the site.
        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( 'postpass' === $action ) {
            return;
        }

        // Let a signed-in user log out through the old URL rather than
        // stranding them with a 404.
        if ( 'logout' === $action && is_user_logged_in() ) {
            return;
        }

        $this->deny_default_login();
    }

    /**
     * Render the real login screen at the custom address.
     *
     * Done on wp_loaded so WordPress is in exactly the state wp-login.php
     * expects. Serving it later (on template_redirect) meant $pagenow was wrong
     * and the theme had already begun loading.
     *
     * @return void
     */
    public function serve_login_screen() {
        if ( !$this->is_login_request ) {
            return;
        }

        // wp-login.php expects these globals to exist.
        global $error, $interim_login, $action, $user_login;

        if ( !isset( $error ) ) {
            $error = '';
        }
        if ( !isset( $user_login ) ) {
            $user_login = '';
        }

        require_once ABSPATH . 'wp-login.php';
        exit;
    }

    /**
     * Whether a path is one of WordPress's built-in login entry points.
     *
     * @param string $slug Request path without surrounding slashes.
     * @return bool
     */
    private function is_default_login_path( $slug ) {
        $slug = strtolower( $slug );

        // Trailing slash variants and the legacy wp-register.php alias.
        $defaults = ['wp-login.php', 'wp-login', 'wp-register.php', 'wp-register'];

        return in_array( $slug, $defaults, true );
    }

    /**
     * Close a default login entry point.
     *
     * Default behaviour is a 404, which reveals nothing: to a scanner the login
     * screen simply does not exist at that address.
     *
     * @return void
     */
    private function deny_default_login() {
        \ThemePaste\SecureAdmin\Classes\ThreatLog::report( 'login_url_probe' );

        /**
         * How to handle a request to the old login URL: '404' or 'home'.
         *
         * @since 1.3.0
         *
         * @param string $behaviour Either '404' or 'home'.
         */
        $behaviour = apply_filters( 'tpsa_default_login_behaviour', '404' );

        if ( 'home' === $behaviour ) {
            wp_safe_redirect( home_url( '/' ), 302 );
            exit;
        }

        // A plain 404 that does not depend on the theme's template hierarchy,
        // which is not ready this early in the request.
        status_header( 404 );
        nocache_headers();

        wp_die(
            esc_html__( 'This page could not be found.', 'admin-safety-guard' ),
            esc_html__( '404 Not Found', 'admin-safety-guard' ),
            ['response' => 404]
        );
    }

    /**
     * Send logged-out visitors away from wp-admin without revealing the login
     * URL.
     *
     * @return void
     */
    public function protect_admin_area() {
        if ( is_user_logged_in() ) {
            return;
        }

        // These endpoints live under wp-admin but are legitimate for logged-out
        // visitors: admin-ajax.php powers front-end AJAX for most plugins, and
        // admin-post.php receives nopriv form posts and OAuth callbacks.
        // Redirecting them would break large parts of a typical site.
        if ( wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }

        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return;
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return;
        }

        $current = isset( $GLOBALS['pagenow'] ) ? (string) $GLOBALS['pagenow'] : '';
        $allowed = ['admin-ajax.php', 'admin-post.php', 'async-upload.php'];

        if ( in_array( $current, $allowed, true ) ) {
            return;
        }

        if ( !is_admin() ) {
            return;
        }

        $redirect = !empty( $this->redirect_slug ) ? home_url( $this->redirect_slug ) : home_url( '/' );

        wp_safe_redirect( $redirect, 302 );
        exit;
    }

    /* ---------------------------------------------------------------------
     * URL rewriting
     * ------------------------------------------------------------------- */

    /**
     * Replace wp-login.php in any site_url() result.
     *
     * @param string $url     Generated URL.
     * @param string $path    Requested path.
     * @param string $scheme  URL scheme.
     * @param int    $blog_id Site ID.
     *
     * @return string
     */
    public function override_site_url( $url, $path = '', $scheme = null, $blog_id = null ) {
        return $this->swap_login_url( $url );
    }

    /**
     * Same for network_site_url(), which multisite uses for password-reset and
     * new-user emails. Without this those links still point at wp-login.php.
     *
     * @param string $url    Generated URL.
     * @param string $path   Requested path.
     * @param string $scheme URL scheme.
     *
     * @return string
     */
    public function override_network_site_url( $url, $path = '', $scheme = null ) {
        return $this->swap_login_url( $url );
    }

    /**
     * Catch redirects that core builds without going through site_url().
     *
     * @param string $location Target URL.
     * @param int    $status   HTTP status.
     *
     * @return string
     */
    public function override_redirect( $location, $status = 302 ) {
        return $this->swap_login_url( $location );
    }

    /**
     * Swap wp-login.php for the custom slug inside a URL, preserving the query
     * string so redirect_to, action=lostpassword, reset keys and interim-login
     * all keep working.
     *
     * @param string $url URL to rewrite.
     * @return string
     */
    private function swap_login_url( $url ) {
        if ( !is_string( $url ) || false === strpos( $url, 'wp-login.php' ) ) {
            return $url;
        }

        return str_replace( 'wp-login.php', $this->custom_login_slug, $url );
    }

    public function logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
        return !empty( $this->logout_url ) ? home_url( $this->logout_url ) : $redirect_to;
    }

    /* ---------------------------------------------------------------------
     * Path resolution
     * ------------------------------------------------------------------- */

    /**
     * The requested path, relative to this site's root.
     *
     * REQUEST_URI is absolute to the domain, so on a sub-directory install
     * (example.com/blog/) or a sub-directory multisite network
     * (example.com/site2/) it carries a prefix that has to be removed before the
     * login slug can be matched. Without this, the custom login URL silently
     * never matches on either of those very common setups.
     *
     * Query string is dropped: only the path takes part in slug matching, so
     * appending "?redirect_to=/my-login" cannot be used to slip past the block
     * on the default login URL.
     *
     * @return string Path beginning with a single slash, or '' when the request
     *                does not belong to this site.
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
     * Whether a slug collides with a reserved WordPress path.
     *
     * @param string $slug Sanitised slug.
     * @return bool
     */
    private function is_reserved( $slug ) {
        return in_array( strtolower( $slug ), $this->reserved, true );
    }

    /* ---------------------------------------------------------------------
     * Admin notices
     * ------------------------------------------------------------------- */

    /**
     * Warn when the feature is on but the configured slug cannot be used.
     *
     * @return void
     */
    public function render_invalid_slug_notice() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>';
        esc_html_e( 'Admin Safety Guard:', 'admin-safety-guard' );
        echo '</strong> ';
        esc_html_e( 'Custom Login URL is enabled but the address is empty or uses a reserved WordPress path, so it has not been applied. Pick a different slug — for example "my-secure-login".', 'admin-safety-guard' );
        echo '</p></div>';
    }

    /**
     * Show the live login address right after the settings are saved, so an
     * administrator always leaves the screen knowing how to get back in.
     *
     * @return void
     */
    public function render_active_url_notice() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI flag.
        if ( empty( $_GET['settings-saved'] ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI flag.
        $screen = isset( $_GET['tpsa-setting'] ) ? sanitize_key( wp_unslash( $_GET['tpsa-setting'] ) ) : '';
        if ( $this->features_id !== $screen ) {
            return;
        }

        echo '<div class="notice notice-success"><p><strong>';
        esc_html_e( 'Your sign-in address is now:', 'admin-safety-guard' );
        echo '</strong> <code>' . esc_html( home_url( '/' . $this->custom_login_slug ) ) . '</code><br>';
        esc_html_e( 'Bookmark it. wp-login.php now returns 404 for everyone.', 'admin-safety-guard' );
        echo '</p></div>';
    }

    /* ---------------------------------------------------------------------
     * Settings screen
     * ------------------------------------------------------------------- */

    /**
     * Show the site URL in front of the slug input.
     */
    public function modify_the_custom_login_logout_url_field( $template, $args ) {
        $site_url = trailingslashit( home_url() );

        return str_replace(
            '<input type="text" id="%2$s" name="%2$s" value="%3$s">',
            '<div class="tp-site-url-input"><span>' . esc_html( $site_url ) . '</span><input type="text" id="%2$s" name="%2$s" value="%3$s"></div>',
            $template
        );
    }

    private function get_settings() {
        $settings = get_option( get_tpsa_settings_option_name( $this->features_id ), [] );

        return is_array( $settings ) ? $settings : [];
    }

    private function is_enabled( $settings ) {
        return isset( $settings['enable'] ) && (int) $settings['enable'] === 1;
    }
}

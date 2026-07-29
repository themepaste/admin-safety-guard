<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Classes\ThreatLog;
use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: SessionSecurity
 *
 * Protects the period AFTER sign-in.
 *
 * Every other feature in Security Core guards the login form. Once someone is
 * through it, WordPress keeps that session valid for two days — or fourteen
 * with "Remember Me" — no matter what happens next. An unattended screen, a
 * stolen laptop or a copied cookie stays usable for that whole window.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.4.0
 */
class SessionSecurity implements FeatureInterface {

    use Hook;

    /**
     * Settings screen slug.
     *
     * @var string
     */
    private $features_id = 'session-security';

    /**
     * User meta holding the last-seen timestamp.
     *
     * @var string
     */
    const LAST_SEEN_META = '_tpsa_last_seen';

    /**
     * How often the last-seen timestamp is written, in seconds.
     *
     * Writing on every request would add a database write to every page load
     * for every signed-in user.
     *
     * @var int
     */
    const TOUCH_INTERVAL = 60;

    /**
     * Cached settings.
     *
     * @var array|null
     */
    private $settings = null;

    public function register_hooks() {
        if ( !$this->on( 'enable' ) ) {
            return;
        }

        // Shorten how long a session is valid for at all.
        $this->filter( 'auth_cookie_expiration', [$this, 'cookie_lifetime'], 10, 3 );

        // Idle timeout.
        if ( $this->idle_minutes() > 0 ) {
            $this->action( 'init', [$this, 'enforce_idle_timeout'], 1 );
        }

        // Invalidate every other session when the password changes, so a
        // stolen session cannot survive the response to it.
        if ( $this->on( 'logout-on-password-change' ) ) {
            $this->action( 'after_password_reset', [$this, 'destroy_other_sessions'], 10, 1 );
            $this->action( 'profile_update', [$this, 'maybe_destroy_on_profile_update'], 10, 2 );
        }

        // Refuse to serve a session from a different address than it was
        // issued to.
        if ( $this->on( 'bind-to-ip' ) ) {
            $this->action( 'init', [$this, 'enforce_ip_binding'], 2 );
        }
    }

    /* ---------------------------------------------------------------------
     * Session lifetime
     * ------------------------------------------------------------------- */

    /**
     * Cap how long an auth cookie stays valid.
     *
     * @param int  $length   Default length in seconds.
     * @param int  $user_id  User ID.
     * @param bool $remember Whether "Remember Me" was ticked.
     *
     * @return int
     */
    public function cookie_lifetime( $length, $user_id = 0, $remember = false ) {
        $hours = $remember ? $this->remember_hours() : $this->session_hours();

        if ( $hours <= 0 ) {
            return $length;
        }

        return $hours * HOUR_IN_SECONDS;
    }

    /* ---------------------------------------------------------------------
     * Idle timeout
     * ------------------------------------------------------------------- */

    /**
     * Sign out a session that has been idle too long.
     *
     * @return void
     */
    public function enforce_idle_timeout() {
        if ( !is_user_logged_in() || $this->is_exempt_request() ) {
            return;
        }

        $user_id = get_current_user_id();
        $limit = $this->idle_minutes() * MINUTE_IN_SECONDS;
        $last = (int) get_user_meta( $user_id, self::LAST_SEEN_META, true );
        $now = time();

        if ( $last && ( $now - $last ) > $limit ) {
            delete_user_meta( $user_id, self::LAST_SEEN_META );
            wp_logout();

            // Send them to the login screen with an explanation rather than a
            // silent bounce to the home page.
            wp_safe_redirect( add_query_arg( 'tpsa_timeout', '1', wp_login_url() ) );
            exit;
        }

        // Throttled write: at most one update per TOUCH_INTERVAL.
        if ( !$last || ( $now - $last ) > self::TOUCH_INTERVAL ) {
            update_user_meta( $user_id, self::LAST_SEEN_META, $now );
        }
    }

    /* ---------------------------------------------------------------------
     * IP binding
     * ------------------------------------------------------------------- */

    /**
     * End a session that arrives from a different address than it started on.
     *
     * Off by default: mobile networks and some ISPs change address mid-session,
     * which would sign those users out repeatedly. It is a strong control on a
     * site whose administrators work from fixed connections.
     *
     * @return void
     */
    public function enforce_ip_binding() {
        if ( !is_user_logged_in() || $this->is_exempt_request() ) {
            return;
        }

        $manager = \WP_Session_Tokens::get_instance( get_current_user_id() );
        $token = wp_get_session_token();

        if ( !$token ) {
            return;
        }

        $session = $manager->get( $token );

        if ( !$session ) {
            return;
        }

        $current_ip = tpsa_get_client_ip();

        // First request on this session: remember where it came from.
        if ( empty( $session['tpsa_ip'] ) ) {
            $session['tpsa_ip'] = $current_ip;
            $manager->update( $token, $session );

            return;
        }

        if ( hash_equals( (string) $session['tpsa_ip'], $current_ip ) ) {
            return;
        }

        ThreatLog::report( 'session_ip_change', 'was ' . $session['tpsa_ip'] );

        $manager->destroy( $token );
        wp_logout();

        wp_safe_redirect( add_query_arg( 'tpsa_timeout', 'ip', wp_login_url() ) );
        exit;
    }

    /* ---------------------------------------------------------------------
     * Password changes
     * ------------------------------------------------------------------- */

    /**
     * Drop every other session for a user after a password reset.
     *
     * @param \WP_User $user User whose password changed.
     * @return void
     */
    public function destroy_other_sessions( $user ) {
        $user_id = ( $user instanceof \WP_User ) ? (int) $user->ID : (int) $user;

        if ( $user_id <= 0 ) {
            return;
        }

        \WP_Session_Tokens::get_instance( $user_id )->destroy_all();
    }

    /**
     * Same, when the password is changed from the profile screen.
     *
     * @param int       $user_id       User ID.
     * @param \WP_User  $old_user_data Data before the update.
     *
     * @return void
     */
    public function maybe_destroy_on_profile_update( $user_id, $old_user_data = null ) {
        if ( !( $old_user_data instanceof \WP_User ) ) {
            return;
        }

        $new = get_userdata( $user_id );

        if ( !$new || $new->user_pass === $old_user_data->user_pass ) {
            return;
        }

        // Keep the session that made the change; end all the others.
        if ( (int) $user_id === get_current_user_id() ) {
            wp_destroy_other_sessions();

            return;
        }

        \WP_Session_Tokens::get_instance( (int) $user_id )->destroy_all();
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    /**
     * Requests that must never be interrupted mid-flight.
     *
     * @return bool
     */
    private function is_exempt_request() {
        return wp_doing_cron()
        || wp_doing_ajax()
        || ( defined( 'WP_CLI' ) && WP_CLI )
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE );
    }

    private function idle_minutes() {
        $settings = $this->get_settings();
        $value = isset( $settings['idle-minutes'] ) ? (int) $settings['idle-minutes'] : 0;

        return $value > 0 ? max( 5, min( 1440, $value ) ) : 0;
    }

    private function session_hours() {
        $settings = $this->get_settings();
        $value = isset( $settings['session-hours'] ) ? (int) $settings['session-hours'] : 0;

        return $value > 0 ? max( 1, min( 720, $value ) ) : 0;
    }

    private function remember_hours() {
        $settings = $this->get_settings();
        $value = isset( $settings['remember-hours'] ) ? (int) $settings['remember-hours'] : 0;

        return $value > 0 ? max( 1, min( 2160, $value ) ) : 0;
    }

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

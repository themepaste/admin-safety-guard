<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: TwoFactorAuth
 *
 * Email one-time-password second factor for the WordPress sign-in.
 *
 * The pending challenge is keyed by a random single-use token rather than the
 * user ID, so the verification step cannot be attacked on its own, and every
 * authentication path that cannot present a code (XML-RPC, Application
 * Passwords) is refused rather than silently skipping the second factor.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class TwoFactorAuth implements FeatureInterface {

    use Hook;

    /**
     * Settings screen slug.
     *
     * @var string
     */
    private $feature_id = 'two-factor-auth';

    /**
     * Transient prefix for a pending challenge.
     *
     * @var string
     */
    const PENDING_PREFIX = 'tpsa_2fa_';

    /**
     * Priority for the authenticate filter.
     *
     * Runs after core (20) and reCAPTCHA (21) so credentials are already
     * proven, and after LimitLoginAttempts (25) so a locked-out address never
     * causes an OTP email to be sent.
     *
     * @var int
     */
    const AUTH_PRIORITY = 30;

    /**
     * Cached settings.
     *
     * @var array|null
     */
    private $settings = null;

    /**
     * Register hooks.
     *
     * @return void
     */
    public function register_hooks() {
        $this->action( 'admin_init', [$this, 'maybe_purge_legacy_otp_meta'] );

        // Filters for the email templates always apply.
        $this->filter( 'tpsa_otp_email_message', [$this, 'email_message'], 10, 2 );
        $this->filter( 'tpsa_otp_email_subject', [$this, 'tpsa_otp_email_subject'], 10, 3 );

        if ( !$this->is_enabled() ) {
            return;
        }

        // Issue the challenge once credentials check out.
        $this->filter( 'authenticate', [$this, 'intercept_login'], self::AUTH_PRIORITY, 3 );

        // Verify before anything is rendered.
        $this->action( 'login_init', [$this, 'handle_challenge_submission'] );

        // Render the code entry screen.
        $this->action( 'login_form', [$this, 'render_challenge_form'] );
        $this->filter( 'login_message', [$this, 'challenge_message'] );

        // Close the paths that cannot present a second factor. Without this the
        // REST API and XML-RPC accept a password alone and 2FA is decorative.
        $this->filter( 'wp_is_application_passwords_available_for_user', [$this, 'disable_application_passwords'], 10, 2 );
    }

    /* ---------------------------------------------------------------------
     * Issuing the challenge
     * ------------------------------------------------------------------- */

    /**
     * After valid credentials, hold the sign-in and demand a code.
     *
     * @param \WP_User|\WP_Error|null $user     Result so far.
     * @param string                  $username Username.
     * @param string                  $password Password.
     *
     * @return \WP_User|\WP_Error|null
     */
    public function intercept_login( $user, $username = '', $password = '' ) {
        // The verification request itself must pass straight through.
        if ( isset( $_POST['tpsa_2fa_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return $user;
        }

        // Only act once WordPress has confirmed the credentials.
        if ( is_wp_error( $user ) || !( $user instanceof \WP_User ) ) {
            return $user;
        }

        if ( !$this->user_requires_2fa( $user ) ) {
            return $user;
        }

        // A client that cannot render an interactive form cannot complete the
        // second factor, so refuse rather than letting it through.
        if ( $this->is_non_interactive_request() ) {
            \ThemePaste\SecureAdmin\Classes\ThreatLog::report( 'twofa_blocked', $user->user_login );

            return new \WP_Error(
                'tpsa_2fa_required',
                __( 'This account requires a second factor, which this connection method does not support. Sign in through the website instead.', 'admin-safety-guard' )
            );
        }

        // Throttle issuance so valid credentials cannot be used to spam a
        // mailbox, and so a shared inbox is not flooded during an attack.
        if ( !$this->can_issue_challenge( $user->ID ) ) {
            return new \WP_Error(
                'tpsa_2fa_throttled',
                __( 'A sign-in code was just sent. Please check your inbox, or wait a moment before trying again.', 'admin-safety-guard' )
            );
        }

        $token = $this->create_challenge( $user, !empty( $_POST['rememberme'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ( is_wp_error( $token ) ) {
            return $token;
        }

        wp_safe_redirect( $this->challenge_url( $token ) );
        exit;
    }

    /**
     * Create a pending challenge and email the code.
     *
     * @param \WP_User $user     Authenticated user.
     * @param bool     $remember Whether "remember me" was ticked.
     *
     * @return string|\WP_Error Challenge token, or an error when the mail failed.
     */
    private function create_challenge( $user, $remember ) {
        $otp = $this->generate_code();

        // The token is the secret that binds this browser to the credential
        // step; the user ID is never trusted from the request.
        $token = wp_generate_password( 32, false );

        if ( !$this->send_mail( $user, $otp ) ) {
            return new \WP_Error(
                'tpsa_2fa_mail_failed',
                __( 'We could not send your sign-in code. Please contact the site administrator.', 'admin-safety-guard' )
            );
        }

        set_transient(
            self::PENDING_PREFIX . $token,
            [
                'user'     => (int) $user->ID,
                'hash'     => wp_hash_password( $otp ),
                'remember' => $remember ? 1 : 0,
                'tries'    => 0,
                'sent'     => time(),
            ],
            $this->otp_validity()
        );

        return $token;
    }

    /**
     * Generate a numeric code of the configured length.
     *
     * @return string
     */
    private function generate_code() {
        $length = $this->otp_length();
        $min = (int) ( '1' . str_repeat( '0', $length - 1 ) );
        $max = (int) str_repeat( '9', $length );

        return (string) wp_rand( $min, $max );
    }

    /* ---------------------------------------------------------------------
     * Verifying the challenge
     * ------------------------------------------------------------------- */

    /**
     * Handle a submitted code, or a request for a new one.
     *
     * @return void
     */
    public function handle_challenge_submission() {
        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';

        if ( 'POST' !== $method || !isset( $_POST['tpsa_2fa_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return;
        }

        $token = $this->read_token( wp_unslash( $_POST['tpsa_2fa_token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $pending = $token ? get_transient( self::PENDING_PREFIX . $token ) : false;

        if ( !$token || !is_array( $pending ) || empty( $pending['hash'] ) ) {
            $this->redirect_with_error( '', 'expired' );
        }

        // "Send me a new code".
        if ( isset( $_POST['tpsa_2fa_resend'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $this->resend_code( $token, $pending );
        }

        $submitted = isset( $_POST['tpsa_2fa_code'] ) ? preg_replace( '/\D/', '', (string) wp_unslash( $_POST['tpsa_2fa_code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ( '' === $submitted ) {
            $this->redirect_with_error( $token, 'empty' );
        }

        // Guessing budget for this challenge.
        if ( (int) $pending['tries'] >= $this->max_tries() ) {
            delete_transient( self::PENDING_PREFIX . $token );
            $this->redirect_with_error( '', 'toomany' );
        }

        if ( !wp_check_password( $submitted, $pending['hash'] ) ) {
            $pending['tries'] = (int) $pending['tries'] + 1;
            set_transient( self::PENDING_PREFIX . $token, $pending, $this->otp_validity() );

            /**
             * Fires when a two-factor code is rejected, so failures can be
             * surfaced in the monitoring log or an external SIEM.
             *
             * @since 1.4.0
             *
             * @param int $user_id    Account being challenged.
             * @param int $tries_used Failed attempts against this challenge.
             */
            do_action( 'tpsa_2fa_failed', (int) $pending['user'], (int) $pending['tries'] );

            \ThemePaste\SecureAdmin\Classes\ThreatLog::report( 'twofa_fail', '', '' );

            $this->redirect_with_error( $token, 'invalid' );
        }

        $user = get_userdata( (int) $pending['user'] );

        if ( !$user ) {
            delete_transient( self::PENDING_PREFIX . $token );
            $this->redirect_with_error( '', 'expired' );
        }

        // Single use: burn the challenge before establishing the session.
        delete_transient( self::PENDING_PREFIX . $token );

        // The password was already verified when the challenge was issued, so
        // the session is established directly and the password is never handled
        // a second time.
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, !empty( $pending['remember'] ), is_ssl() );
        do_action( 'wp_login', $user->user_login, $user );

        $redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $destination = $redirect_to ? $redirect_to : admin_url();

        wp_safe_redirect( apply_filters( 'login_redirect', $destination, $redirect_to, $user ) );
        exit;
    }

    /**
     * Issue a replacement code for an existing challenge.
     *
     * @param string $token   Challenge token.
     * @param array  $pending Stored challenge.
     *
     * @return void
     */
    private function resend_code( $token, $pending ) {
        $cooldown = (int) apply_filters( 'tpsa_2fa_resend_cooldown', 60 );

        if ( ( time() - (int) $pending['sent'] ) < $cooldown ) {
            $this->redirect_with_error( $token, 'wait' );
        }

        $user = get_userdata( (int) $pending['user'] );

        if ( !$user ) {
            delete_transient( self::PENDING_PREFIX . $token );
            $this->redirect_with_error( '', 'expired' );
        }

        $otp = $this->generate_code();

        if ( !$this->send_mail( $user, $otp ) ) {
            $this->redirect_with_error( $token, 'mail' );
        }

        // A replacement code resets the guessing budget along with the secret.
        $pending['hash'] = wp_hash_password( $otp );
        $pending['tries'] = 0;
        $pending['sent'] = time();

        set_transient( self::PENDING_PREFIX . $token, $pending, $this->otp_validity() );

        $this->redirect_with_error( $token, 'resent' );
    }

    /* ---------------------------------------------------------------------
     * Screen
     * ------------------------------------------------------------------- */

    /**
     * Replace the credentials form with the code entry form.
     *
     * @return void
     */
    public function render_challenge_form() {
        $token = $this->current_token();

        if ( !$token ) {
            return;
        }

        $pending = get_transient( self::PENDING_PREFIX . $token );

        if ( !is_array( $pending ) || empty( $pending['hash'] ) ) {
            return;
        }

        $user = get_userdata( (int) $pending['user'] );

        if ( !$user ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
        ?>
<style>
#user_login, #user_pass, label[for="user_login"], label[for="user_pass"],
.wp-hide-pw, .forgetmenot, .submit { display: none !important; }
#tpsa-2fa { margin-bottom: 16px; }
#tpsa-2fa-code {
    font-size: 26px; letter-spacing: 10px; text-align: center;
    padding: 10px 8px; width: 100%; box-sizing: border-box;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
}
#tpsa-2fa-submit {
    width: 100%; padding: 10px; margin-top: 12px; background: #2271b1;
    border: 0; color: #fff; font-weight: 600; border-radius: 3px; cursor: pointer;
}
#tpsa-2fa-submit:hover { background: #135e96; }
.tpsa-2fa-sent { color: #1f7a45; font-size: 13px; margin: 0 0 12px; }
.tpsa-2fa-resend {
    background: none; border: 0; padding: 0; margin-top: 12px;
    color: #2271b1; text-decoration: underline; cursor: pointer; font-size: 13px;
}
</style>

<div id="tpsa-2fa">
    <p class="tpsa-2fa-sent">
        <?php
printf(
            /* translators: %s: masked email address. */
            esc_html__( 'We sent a sign-in code to %s', 'admin-safety-guard' ),
            esc_html( $this->mask_email( $user->user_email ) )
        );
?>
    </p>

    <label for="tpsa-2fa-code"><?php esc_html_e( 'Sign-in code', 'admin-safety-guard' ); ?></label>
    <input type="text" name="tpsa_2fa_code" id="tpsa-2fa-code" class="input"
        inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*"
        maxlength="<?php echo esc_attr( $this->otp_length() ); ?>" required autofocus>

    <input type="hidden" name="tpsa_2fa_token" value="<?php echo esc_attr( $token ); ?>">
    <?php if ( $redirect_to ) : ?>
    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
    <?php endif; ?>

    <button type="submit" id="tpsa-2fa-submit"><?php esc_html_e( 'Verify and sign in', 'admin-safety-guard' ); ?></button>

    <button type="submit" name="tpsa_2fa_resend" value="1" class="tpsa-2fa-resend">
        <?php esc_html_e( 'Send a new code', 'admin-safety-guard' ); ?>
    </button>
</div>
<?php
}

    /**
     * Status and error text above the form.
     *
     * @param string $message Existing markup.
     * @return string
     */
    public function challenge_message( $message ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $code = isset( $_GET['tpsa_2fa_msg'] ) ? sanitize_key( wp_unslash( $_GET['tpsa_2fa_msg'] ) ) : '';

        if ( '' === $code ) {
            return $message;
        }

        $notices = [
            'invalid' => ['error', __( 'That code is not correct. Please try again.', 'admin-safety-guard' )],
            'empty'   => ['error', __( 'Please enter the code from your email.', 'admin-safety-guard' )],
            'expired' => ['error', __( 'Your sign-in code has expired. Please sign in again.', 'admin-safety-guard' )],
            'toomany' => ['error', __( 'Too many incorrect codes. Please sign in again.', 'admin-safety-guard' )],
            'mail'    => ['error', __( 'We could not send the code. Please contact the site administrator.', 'admin-safety-guard' )],
            'wait'    => ['message', __( 'A code was just sent. Please wait a moment before requesting another.', 'admin-safety-guard' )],
            'resent'  => ['message', __( 'A new code is on its way to your inbox.', 'admin-safety-guard' )],
        ];

        if ( !isset( $notices[$code] ) ) {
            return $message;
        }

        list( $type, $text ) = $notices[$code];

        return $message . '<div id="login_' . ( 'error' === $type ? 'error' : 'message' ) . '">'
        . esc_html( $text ) . '</div>';
    }

    /* ---------------------------------------------------------------------
     * Policy
     * ------------------------------------------------------------------- */

    /**
     * Whether an account must complete the second factor.
     *
     * @param \WP_User $user User.
     * @return bool
     */
    private function user_requires_2fa( $user ) {
        $roles = $this->required_roles();

        // Empty selection means "every account", which is the safe default.
        $required = empty( $roles ) || (bool) array_intersect( (array) $user->roles, $roles );

        /**
         * Filter whether a given user must pass two-factor authentication.
         *
         * @since 1.4.0
         *
         * @param bool     $required Whether the second factor applies.
         * @param \WP_User $user     The user signing in.
         */
        return (bool) apply_filters( 'tpsa_2fa_required_for_user', $required, $user );
    }

    /**
     * Whether this request cannot present an interactive code form.
     *
     * @return bool
     */
    private function is_non_interactive_request() {
        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            return true;
        }

        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return true;
        }

        return false;
    }

    /**
     * Turn off Application Passwords for accounts that require a second factor.
     *
     * Application Passwords authenticate on `determine_current_user` and never
     * reach the `authenticate` filter, so they would otherwise accept a single
     * secret and skip 2FA completely.
     *
     * @param bool     $available Whether application passwords are available.
     * @param \WP_User $user      User being checked.
     *
     * @return bool
     */
    public function disable_application_passwords( $available, $user = null ) {
        if ( !$available || !( $user instanceof \WP_User ) ) {
            return $available;
        }

        return $this->user_requires_2fa( $user ) ? false : $available;
    }

    /**
     * Rate-limit challenge issuance per account and per address.
     *
     * @param int $user_id User ID.
     * @return bool
     */
    private function can_issue_challenge( $user_id ) {
        $key = 'tpsa_2fa_issue_' . md5( $user_id . '|' . tpsa_get_client_ip() );

        if ( false !== get_transient( $key ) ) {
            return false;
        }

        set_transient( $key, 1, (int) apply_filters( 'tpsa_2fa_issue_cooldown', 30 ) );

        return true;
    }

    /* ---------------------------------------------------------------------
     * Mail
     * ------------------------------------------------------------------- */

    /**
     * Send the code.
     *
     * @param \WP_User $user User.
     * @param string   $otp  Code.
     *
     * @return bool Whether the message was accepted for delivery.
     */
    private function send_mail( $user, $otp ) {
        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        $minutes = (int) round( $this->otp_validity() / MINUTE_IN_SECONDS );

        $subject = sprintf(
            /* translators: 1: site name, 2: one-time code. */
            __( '%1$s sign-in code: %2$s', 'admin-safety-guard' ),
            $site_name,
            $otp
        );
        $subject = apply_filters( 'tpsa_otp_email_subject', $subject, $otp, $user );

        $message = '<div style="max-width:420px;margin:32px auto;padding:24px;background:#f7f9fc;'
        . 'font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial,sans-serif;text-align:center;border-radius:8px;">'
        . '<div style="font-size:20px;font-weight:600;color:#333;margin-bottom:16px;">'
        . esc_html__( 'Your sign-in code', 'admin-safety-guard' ) . '</div>'
        . '<div style="background:#0073aa;color:#fff;font-size:30px;font-weight:700;padding:14px 32px;'
        . 'border-radius:6px;letter-spacing:8px;display:inline-block;">' . esc_html( $otp ) . '</div>'
        . '<p style="font-size:13px;color:#666;margin-top:18px;">'
        . esc_html(
            sprintf(
                /* translators: %d: minutes the code stays valid. */
                _n( 'This code is valid for %d minute.', 'This code is valid for %d minutes.', $minutes, 'admin-safety-guard' ),
                $minutes
            )
        )
        . '</p><p style="font-size:12px;color:#999;">'
        . esc_html__( 'If you did not try to sign in, you can ignore this email - your password is still safe.', 'admin-safety-guard' )
        . '</p></div>';

        $message = apply_filters( 'tpsa_otp_email_message', $message, $otp, $user );

        return (bool) wp_mail(
            $user->user_email,
            $subject,
            $message,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    /**
     * Apply the administrator's custom body template.
     *
     * @param string $message Default body.
     * @param string $otp     Code.
     *
     * @return string
     */
    public function email_message( $message, $otp ) {
        $settings = $this->get_settings();

        if ( empty( $settings['email-body'] ) ) {
            return $message;
        }

        return str_replace(
            ['{otp}', '{site_name}'],
            [$otp, wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )],
            (string) $settings['email-body']
        );
    }

    /**
     * Apply the administrator's custom subject template.
     *
     * @param string $subject Default subject.
     * @param string $otp     Code.
     *
     * @return string
     */
    public function tpsa_otp_email_subject( $subject, $otp ) {
        $settings = $this->get_settings();

        if ( empty( $settings['email-subject'] ) ) {
            return $subject;
        }

        return str_replace(
            ['{otp}', '{site_name}'],
            [$otp, wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )],
            (string) $settings['email-subject']
        );
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    /**
     * Token from the current request, if any.
     *
     * @return string
     */
    private function current_token() {
        // phpcs:ignore WordPress.Security.NonceVerification
        if ( isset( $_POST['tpsa_2fa_token'] ) ) {
            return $this->read_token( wp_unslash( $_POST['tpsa_2fa_token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['tpsa_2fa'] ) ) {
            return $this->read_token( wp_unslash( $_GET['tpsa_2fa'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }

        return '';
    }

    /**
     * Normalise a token: 32 alphanumerics, nothing else.
     *
     * @param mixed $raw Raw value.
     * @return string Empty when malformed.
     */
    private function read_token( $raw ) {
        $token = is_scalar( $raw ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) $raw ) : '';

        return 32 === strlen( $token ) ? $token : '';
    }

    /**
     * URL of the code entry screen.
     *
     * @param string $token Challenge token.
     * @return string
     */
    private function challenge_url( $token ) {
        $args = ['tpsa_2fa' => $token];

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !empty( $_POST['redirect_to'] ) ) {
            $args['redirect_to'] = rawurlencode( esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        }

        return add_query_arg( $args, wp_login_url() );
    }

    /**
     * Bounce back to the challenge screen with a status code.
     *
     * @param string $token Challenge token, or '' to restart the sign-in.
     * @param string $code  Message key.
     *
     * @return void
     */
    private function redirect_with_error( $token, $code ) {
        $args = ['tpsa_2fa_msg' => $code];

        if ( $token ) {
            $args['tpsa_2fa'] = $token;
        }

        wp_safe_redirect( add_query_arg( $args, wp_login_url() ) );
        exit;
    }

    /**
     * Partially hide an email address for display.
     *
     * @param string $email Address.
     * @return string
     */
    private function mask_email( $email ) {
        $parts = explode( '@', (string) $email );

        if ( count( $parts ) !== 2 ) {
            return __( 'your email address', 'admin-safety-guard' );
        }

        $local = $parts[0];
        $visible = strlen( $local ) > 2 ? substr( $local, 0, 2 ) : substr( $local, 0, 1 );

        return $visible . str_repeat( '*', max( 3, strlen( $local ) - strlen( $visible ) ) ) . '@' . $parts[1];
    }

    /**
     * Remove legacy `_tpsa_otp_code` user meta left behind by earlier versions,
     * which may contain plaintext passwords. Runs once per site.
     *
     * @return void
     */
    public function maybe_purge_legacy_otp_meta() {
        if ( get_option( 'tpsa_2fa_legacy_otp_purged' ) ) {
            return;
        }

        delete_metadata( 'user', 0, '_tpsa_otp_code', '', true );

        update_option( 'tpsa_2fa_legacy_otp_purged', 1, false );
    }

    /* ---------------------------------------------------------------------
     * Settings accessors
     * ------------------------------------------------------------------- */

    private function get_settings() {
        if ( null === $this->settings ) {
            $settings = get_option( get_tpsa_settings_option_name( $this->feature_id ), [] );
            $this->settings = is_array( $settings ) ? $settings : [];
        }

        return $this->settings;
    }

    private function is_enabled() {
        $settings = $this->get_settings();

        return !empty( $settings['otp-email'] );
    }

    /**
     * Roles that must use the second factor. Empty means every account.
     *
     * @return string[]
     */
    private function required_roles() {
        $settings = $this->get_settings();

        return isset( $settings['otp-roles'] ) && is_array( $settings['otp-roles'] )
        ? array_map( 'strval', $settings['otp-roles'] )
        : [];
    }

    /**
     * Code length, clamped to a sane range.
     *
     * @return int
     */
    private function otp_length() {
        $settings = $this->get_settings();
        $length = isset( $settings['otp-length'] ) ? (int) $settings['otp-length'] : 6;

        return max( 4, min( 8, $length ) );
    }

    /**
     * Code lifetime in seconds.
     *
     * @return int
     */
    private function otp_validity() {
        $settings = $this->get_settings();
        $minutes = isset( $settings['otp-validity'] ) ? (int) $settings['otp-validity'] : 5;
        $minutes = max( 1, min( 30, $minutes ) );

        return $minutes * MINUTE_IN_SECONDS;
    }

    /**
     * Attempts allowed against one challenge.
     *
     * @return int
     */
    private function max_tries() {
        $settings = $this->get_settings();
        $tries = isset( $settings['otp-max-tries'] ) ? (int) $settings['otp-max-tries'] : 5;

        return max( 1, min( 10, $tries ) );
    }
}

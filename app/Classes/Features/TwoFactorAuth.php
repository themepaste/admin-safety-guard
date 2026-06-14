<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: TwoFactorAuth
 *
 * Implements Two Factor Authentication via email OTP.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class TwoFactorAuth implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference.
     *
     * @since 1.0.0
     * @var string
     */
    private $feature_id = 'two-factor-auth';

    /**
     * Lifetime of a pending OTP, in seconds.
     *
     * @since 1.3.0
     * @var int
     */
    const OTP_TTL = 300; // 5 minutes.

    /**
     * Maximum number of OTP verification attempts before the code is invalidated.
     *
     * @since 1.3.0
     * @var int
     */
    const OTP_MAX_TRIES = 5;

    /**
     * Build the transient key that holds the pending OTP for a user.
     *
     * @param int $user_id User ID.
     * @return string
     */
    private function otp_key( $user_id ) {
        return 'tpsa_otp_' . (int) $user_id;
    }

    /**
     * Register hooks for the feature.
     *
     * @return void
     */
    public function register_hooks() {
        $this->action( 'init', [$this, 'email_otp_authentication'] );

        // One-time cleanup: older versions stored plaintext credentials in the
        // `_tpsa_otp_code` user meta. Scrub any residual rows on upgrade.
        $this->action( 'admin_init', [$this, 'maybe_purge_legacy_otp_meta'] );
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

        // Delete the meta key for every user in a single query.
        delete_metadata( 'user', 0, '_tpsa_otp_code', '', true );

        update_option( 'tpsa_2fa_legacy_otp_purged', 1, false );
    }

    /**
     * Setup OTP email authentication hooks if enabled.
     *
     * @return void
     */
    public function email_otp_authentication() {
        $settings = $this->get_settings();

        if ( $this->is_enabled( $settings, 'otp-email' ) ) {
            // Process OTP submission as early as possible (before HTML output).
            $this->action( 'login_init', [$this, 'check_otp_submission'] );

            // Render OTP UI on the login form.
            $this->action( 'login_form', [$this, 'render_otp_input'] );

            // Surface an "expired/restart" notice on the standard login screen.
            $this->filter( 'login_message', [$this, 'otp_login_message'] );

            // Intercept username/password login to send OTP first.
            $this->filter( 'authenticate', [$this, 'intercept_login_with_otp'], 30, 3 );
        }

        $this->filter( 'tpsa_otp_email_message', [$this, 'email_message'], 10, 2 );
        $this->filter( 'tpsa_otp_email_subject', [$this, 'tpsa_otp_email_subject'], 10, 3 );
    }

    /**
     * Show the "code expired, please log in again" notice on the standard login form.
     *
     * @param string $message Existing login message markup.
     * @return string
     */
    public function otp_login_message( $message ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI flag, no state change.
        $code = isset( $_GET['tpsa_otp_error'] ) ? sanitize_key( wp_unslash( $_GET['tpsa_otp_error'] ) ) : '';

        // The OTP screen renders its own "invalid" notice; only handle the restart case here.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI flag, no state change.
        if ( 'expired' === $code && !isset( $_GET['tpsa_verify_email_otp'] ) ) {
            $message .= '<div id="login_error">' . esc_html__( 'Your login code has expired. Please log in again.', 'admin-safety-guard' ) . '</div>';
        }

        return $message;
    }

    public function email_message( $message, $otp ) {
        $settings = $this->get_settings();
        if ( isset( $settings['email-body'] ) ) {
            $message = $settings['email-body'];
            $message = str_replace( '{otp}', $otp, $message );
        }
        return $message;
    }

    public function tpsa_otp_email_subject( $subject, $otp ) {
        $settings = $this->get_settings();
        if ( isset( $settings['email-subject'] ) ) {
            $subject = $settings['email-subject'];
            $subject = str_replace( '{otp}', $otp, $subject );
            $subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $subject );
        }
        return $subject;
    }

    /**
     * Render OTP input field on login form if OTP pending.
     *
     * @return void
     */
    public function render_otp_input() {
        // The user ID can arrive either via the verification URL (GET) or a failed
        // re-submission (POST). Never trust it beyond confirming a pending OTP exists.
        $user_id = 0;
        if ( isset( $_GET['tpsa_verify_email_otp'] ) ) {
            $user_id = absint( wp_unslash( $_GET['tpsa_verify_email_otp'] ) );
        } elseif ( isset( $_POST['tpsa_otp_verify'], $_POST['tpsa_user_id'] ) ) {
            $user_id = absint( wp_unslash( $_POST['tpsa_user_id'] ) );
        }

        if ( !$user_id ) {
            return;
        }

        // Only render the OTP UI when a pending OTP actually exists for this user.
        $pending = get_transient( $this->otp_key( $user_id ) );
        if ( empty( $pending ) ) {
            return;
        }

        $user = get_userdata( $user_id );
        if ( !$user ) {
            return;
        }

        // Surface verification errors carried back via the redirect.
        $error_code = isset( $_GET['tpsa_otp_error'] ) ? sanitize_key( wp_unslash( $_GET['tpsa_otp_error'] ) ) : '';
        if ( 'invalid' === $error_code ) {
            echo '<div id="login_error">' . esc_html__( 'Invalid OTP. Please try again.', 'admin-safety-guard' ) . '</div>';
        }

        // Keep failed submissions on the OTP screen by pointing the login form back
        // at the verification URL (the standard form action drops our query var).
        $otp_action = wp_login_url() . '?tpsa_verify_email_otp=' . $user_id;
        ?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('loginform');
    if (form) {
        form.setAttribute('action', <?php echo wp_json_encode( esc_url_raw( $otp_action ) ); ?>);
    }
});
</script>
<style type="text/css">
#user_login,
#user_pass,
label[for="user_login"],
label[for="user_pass"],
.wp-hide-pw,
.forgetmenot,
.submit {
    display: none !important;
}

#tpsa_otp_field {
    font-size: 16px;
    line-height: 1.5;
    height: auto;
    padding: 3px 8px;
    width: 100%;
    box-sizing: border-box;
}

#tpsa_verify_btn {
    width: 100%;
    padding: 8px 10px;
    background: #2271b1;
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
}

#tpsa_verify_btn:hover {
    background: #165a96;
}
</style>

<div id="tpsa_otp_wrap">
    <label for="tpsa_otp_field">
        <?php echo esc_html__( 'One Time Password', 'admin-safety-guard' ); ?>
    </label>
    <input type="hidden" name="tpsa_user_id" value="<?php echo esc_attr( $user_id ); ?>">
    <input type="hidden" name="tpsa_otp_verify" value="1">
    <input type="text" name="tpsa_otp" id="tpsa_otp_field" class="input"
        placeholder="<?php echo esc_attr__( 'Enter OTP', 'admin-safety-guard' ); ?>" required autocomplete="off">
    <?php $this->sent_email_message( $user ); ?>
</div>
<button type="submit" id="tpsa_verify_btn">
    <?php echo esc_html__( 'Verify OTP', 'admin-safety-guard' ); ?>
</button>
<?php
}

    /**
     * Check OTP submission on login (runs on login_init).
     *
     * @return void
     */
    public function check_otp_submission() {
        $request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
        if ( 'POST' !== $request_method || !isset( $_POST['tpsa_otp_verify'] ) ) {
            return;
        }

        $user_id = isset( $_POST['tpsa_user_id'] ) ? absint( wp_unslash( $_POST['tpsa_user_id'] ) ) : 0;
        $otp_input = isset( $_POST['tpsa_otp'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_otp'] ) ) : '';

        if ( !$user_id || '' === $otp_input ) {
            return;
        }

        $key = $this->otp_key( $user_id );
        $data = get_transient( $key );

        // No pending OTP (never issued or expired). Send the user back to a fresh login.
        if ( empty( $data ) || empty( $data['hash'] ) ) {
            $this->redirect_to_otp_login( 0, 'expired' );
        }

        // Throttle brute-force guessing of the OTP.
        if ( (int) $data['tries'] >= self::OTP_MAX_TRIES ) {
            delete_transient( $key );
            $this->redirect_to_otp_login( 0, 'expired' );
        }

        // Compare against the stored hash in constant time (wp_check_password).
        if ( !wp_check_password( $otp_input, $data['hash'] ) ) {
            $data['tries'] = (int) $data['tries'] + 1;
            set_transient( $key, $data, self::OTP_TTL );
            $this->redirect_to_otp_login( $user_id, 'invalid' );
        }

        $user = get_userdata( $user_id );
        if ( !$user ) {
            delete_transient( $key );
            $this->redirect_to_otp_login( 0, 'expired' );
        }

        // OTP verified — establish the authenticated session WITHOUT ever handling
        // the password. WordPress already validated the credentials before the OTP
        // was issued, so we set the auth cookie directly.
        $remember = !empty( $data['remember'] );
        delete_transient( $key );

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, $remember, is_ssl() );
        do_action( 'wp_login', $user->user_login, $user );

        wp_safe_redirect( admin_url() );
        exit;
    }

    /**
     * Redirect back to the OTP step (or a fresh login) with an error flag.
     *
     * @param int    $user_id User ID to keep on the OTP screen, or 0 for a fresh login.
     * @param string $code    Error code: 'invalid' or 'expired'.
     *
     * @return void
     */
    private function redirect_to_otp_login( $user_id, $code ) {
        $url = wp_login_url();
        if ( $user_id ) {
            $url .= '?tpsa_verify_email_otp=' . (int) $user_id;
        }
        $url = add_query_arg( 'tpsa_otp_error', sanitize_key( $code ), $url );

        wp_safe_redirect( $url );
        exit;
    }

    /**
     * Intercept login to trigger OTP sending instead of direct login.
     *
     * @param \WP_User|\WP_Error|null $user     The WP_User or WP_Error object returned from authentication.
     * @param string                  $username Username.
     * @param string                  $password Password.
     *
     * @return \WP_User|\WP_Error|null
     */
    public function intercept_login_with_otp( $user, $username, $password ) {
        // If we are in the OTP verification step, let WordPress handle normally.
        if ( isset( $_POST['tpsa_otp_verify'] ) ) {
            return $user;
        }

        // Only proceed once WordPress has confirmed the credentials are valid.
        if ( is_wp_error( $user ) || !( $user instanceof \WP_User ) ) {
            return $user;
        }

        // Capture the "Remember me" choice from the original login form.
        $remember = !empty( $_POST['rememberme'] );

        // Generate a 6-digit OTP and persist ONLY its hash (never the password).
        // The credentials were already validated above, so they are not needed again.
        $otp = (string) wp_rand( 100000, 999999 );

        set_transient(
            $this->otp_key( $user->ID ),
            [
                'hash'     => wp_hash_password( $otp ),
                'remember' => $remember ? 1 : 0,
                'tries'    => 0,
            ],
            self::OTP_TTL
        );

        // Send OTP email (replace or extend for SMS if needed).
        $this->send_mail( $user, $otp );

        // Redirect to OTP verification page.
        wp_safe_redirect( wp_login_url() . '?tpsa_verify_email_otp=' . intval( $user->ID ) );
        exit();
    }

    /**
     * Send OTP email.
     *
     * @param \WP_User $user User object.
     * @param int      $otp  One-time password.
     *
     * @return void
     */
    private function send_mail( $user, $otp ) {
        $user_email = $user->user_email;
        $site_name = get_bloginfo( 'name' );

        $subject = sprintf( '%s - Your OTP is: %s', $site_name, $otp );
        $subject = apply_filters( 'tpsa_otp_email_subject', $subject, $otp );

        $message = '
        <html>
        <head>
        <style>
            .email-container {
                max-width: 400px;
                margin: 35px auto;
                padding: 20px;
                background-color: #f7f9fc;
                font-family: Arial, sans-serif;
                text-align: center;
                border-radius: 8px;
            }
            .email-title {
                font-size: 28px;
                font-weight: 600;
                margin-bottom: 20px;
                color: #333333;
            }
            .otp-box {
                background-color: #0073aa;
                color: #ffffff;
                font-size: 32px;
                font-weight: bold;
                padding: 15px 40px;
                border-radius: 6px;
                user-select: all;
                display: inline-block;
                letter-spacing: 6px;
            }
        </style>
        </head>
        <body>
        <div class="email-container">
            <div class="email-title">Your Login Code is:</div>
            <div class="otp-box">' . esc_html( $otp ) . '</div>
        </div>
        </body>
        </html>
        ';

        $message = apply_filters( 'tpsa_otp_email_message', $message, $otp );

        // Set content-type header for HTML email.
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail( $user_email, $subject, $message, $headers );
    }

    /**
     * Show "email sent" message under OTP field.
     *
     * @param \WP_User $user
     *
     * @return void
     */
    private function sent_email_message( $user ) {
        if ( !$user ) {
            return;
        }

        $email = $user->user_email;
        $email_parts = explode( '@', $email );
        $local = $email_parts[0];
        $domain = $email_parts[1] ?? '';
        $last_chars = substr( $local, -3 );
        $masked_email = '....' . $last_chars . '@' . $domain;
        ?>
<p style="color: green; font-weight: 600; margin-bottom: 20px;">
    <?php
printf(
            /* translators: %s is the user's masked email address */
            esc_html__( 'OTP code sent to your email address %s. Please check your inbox or spam folder.', 'admin-safety-guard' ),
            esc_html( $masked_email )
        );

        ?>
</p>
<?php
}

    /**
     * Get plugin settings for this feature.
     *
     * @return array
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->feature_id );
        return get_option( $option_name, [] );
    }

    /**
     * Check if a feature key is enabled.
     *
     * @param array  $settings Settings array.
     * @param string $key      Setting key to check.
     *
     * @return bool
     */
    private function is_enabled( $settings, $key ) {
        return isset( $settings[$key] ) && (int) $settings[$key] === 1;
    }
}
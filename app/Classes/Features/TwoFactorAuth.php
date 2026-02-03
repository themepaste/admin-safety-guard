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
     * Register hooks for the feature.
     *
     * @return void
     */
    public function register_hooks() {
        $this->action( 'init', [$this, 'email_otp_authentication'] );
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

            // Intercept username/password login to send OTP first.
            $this->filter( 'authenticate', [$this, 'intercept_login_with_otp'], 30, 3 );
        }
    }

    /**
     * Render OTP input field on login form if OTP pending.
     *
     * @return void
     */
    public function render_otp_input() {
        if ( !isset( $_GET['tpsa_verify_email_otp'] ) ) {
            return;
        }

        $user_id = intval( $_GET['tpsa_verify_email_otp'] );
        $user = get_userdata( $user_id );

        if ( !$user ) {
            return;
        }

        $stored_data = get_user_meta( $user_id, '_tpsa_otp_code', true );
        $username = isset( $stored_data['username'] ) ? $stored_data['username'] : '';
        $password = isset( $stored_data['password'] ) ? $stored_data['password'] : '';

        if ( !empty( $username ) && !empty( $password ) ) {
            ?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var loginInput = document.getElementById('user_login');
    var passInput = document.getElementById('user_pass');

    if (loginInput && passInput) {
        loginInput.value = <?php echo wp_json_encode( $username ); ?>;
        passInput.value = <?php echo wp_json_encode( $password ); ?>;
    }
});
</script>
<?php
}
        ?>
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
        if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) || !isset( $_POST['tpsa_otp_verify'] ) ) {
            return;
        }

        $user_id = isset( $_POST['tpsa_user_id'] ) ? intval( $_POST['tpsa_user_id'] ) : 0;
        $otp_input = isset( $_POST['tpsa_otp'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_otp'] ) ) : '';

        if ( !$user_id || empty( $otp_input ) ) {
            return;
        }

        $stored_data = get_user_meta( $user_id, '_tpsa_otp_code', true );
        $stored_otp = isset( $stored_data['otp'] ) ? $stored_data['otp'] : '';

        if ( $otp_input !== $stored_otp ) {
            // Display error message above the form.
            add_action(
                'login_message',
                function () {
                    echo '<div style="color:red; margin-bottom:10px;">' .
                    esc_html__( 'Invalid OTP. Please try again.', 'admin-safety-guard' ) .
                        '</div>';
                }
            );
            return;
        }

        // OTP is correct – now perform a proper WordPress login using wp_signon().
        $username = isset( $stored_data['username'] ) ? $stored_data['username'] : '';
        $password = isset( $stored_data['password'] ) ? $stored_data['password'] : '';
        $remember = !empty( $stored_data['remember'] );

        // Clean up OTP data.
        delete_user_meta( $user_id, '_tpsa_otp_code' );

        if ( empty( $username ) || empty( $password ) ) {
            add_action(
                'login_message',
                function () {
                    echo '<div style="color:red; margin-bottom:10px;">' .
                    esc_html__( 'Login data missing. Please try logging in again.', 'admin-safety-guard' ) .
                        '</div>';
                }
            );
            return;
        }

        $creds = [
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ];

        // Let WordPress handle auth, cookies, tokens, Remember Me, etc.
        $secure_cookie = is_ssl();
        $user = wp_signon( $creds, $secure_cookie );

        if ( is_wp_error( $user ) ) {
            add_action(
                'login_message',
                function () {
                    echo '<div style="color:red; margin-bottom:10px;">' .
                    esc_html__( 'Login failed after OTP verification. Please try again.', 'admin-safety-guard' ) .
                        '</div>';
                }
            );
            return;
        }

        // Successful login, redirect to admin.
        wp_safe_redirect( admin_url() );
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

        if ( is_wp_error( $user ) ) {
            return $user;
        }

        // Capture the "Remember me" choice from the original login form.
        $remember = !empty( $_POST['rememberme'] );

        // Generate and store OTP + remember flag.
        $otp = rand( 1000, 99999 );

        update_user_meta(
            $user->ID,
            '_tpsa_otp_code',
            [
                'username' => $username,
                'password' => $password,
                'otp'      => strval( $otp ),
                'remember' => $remember ? 1 : 0,
            ]
        );

        // Send OTP email (replace or extend for SMS if needed).
        $this->send_mail( $user, $otp );

        // Redirect to OTP verification page.
        wp_redirect( wp_login_url() . '?tpsa_verify_email_otp=' . intval( $user->ID ) );
        exit;
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
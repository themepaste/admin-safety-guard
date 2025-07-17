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
        $this->action( 'init', [ $this, 'email_otp_authentication' ] );
    }

    /**
     * Setup OTP email authentication hooks if enabled.
     *
     * @return void
     */
    public function email_otp_authentication() {
        $settings = $this->get_settings();

        if ( $this->is_enabled( $settings, 'otp-email' ) ) {
            $this->action( 'login_form', [ $this, 'check_otp_submission' ] );
            $this->action( 'login_form', [ $this, 'render_otp_input' ] );
            $this->filter( 'authenticate', [ $this, 'intercept_login_with_otp' ], 30, 3 );
        }
    }

    /**
     * Render OTP input field on login form if OTP pending.
     *
     * @return void
     */
    public function render_otp_input() {
        if ( ! isset( $_GET['tpsa_pending'] ) ) {
            return;
        }

        $user_id     = intval( $_GET['tpsa_pending'] );
        $user        = get_userdata($user_id);

        $stored_data = get_user_meta( $user_id, '_tpsa_otp_code', true );
        $username = isset( $stored_data['username'] ) ? $stored_data['username'] : '';
        $password = isset( $stored_data['password'] ) ? $stored_data['password'] : '';

        

        if ( ! empty( $username ) && ! empty( $password ) ) {
            ?>
            <script type="text/javascript">
                document.addEventListener( 'DOMContentLoaded', function () {
                    var loginInput = document.getElementById( 'user_login' );
                    var passInput = document.getElementById( 'user_pass' );

                    if ( loginInput && passInput ) {
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
            <label for="tpsa_otp_field"><?php echo esc_html__( 'One Time Password', 'tp-secure-plugin' ); ?></label>
            <input type="hidden" name="tpsa_user_id" value="<?php echo esc_attr( $user_id ); ?>">
            <input type="hidden" name="tpsa_otp_verify" value="1">
            <input
                type="text"
                name="tpsa_otp"
                id="tpsa_otp_field"
                class="input"
                placeholder="<?php echo esc_attr__( 'Enter OTP', 'tp-secure-plugin' ); ?>"
                required
                autocomplete="off"
            >
            <?php $this->sent_email_message( $user ); ?>
        </div>
        <button type="submit" id="tpsa_verify_btn"><?php echo esc_html__( 'Verify OTP', 'tp-secure-plugin' ); ?></button>
        <?php
    }

    /**
     * Check OTP submission on login form.
     *
     * @return void
     */
    public function check_otp_submission() {
        if ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['tpsa_otp_verify'] ) ) {
            $user_id   = isset( $_POST['tpsa_user_id'] ) ? intval( $_POST['tpsa_user_id'] ) : 0;
            $otp_input = isset( $_POST['tpsa_otp'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_otp'] ) ) : '';

            if ( ! $user_id || empty( $otp_input ) ) {
                return;
            }

            $stored_data = get_user_meta( $user_id, '_tpsa_otp_code', true );
            $stored_otp  = isset( $stored_data['otp'] ) ? $stored_data['otp'] : '';

            if ( $otp_input === $stored_otp ) {
                delete_user_meta( $user_id, '_tpsa_otp_code' );
                wp_set_auth_cookie( $user_id );
                wp_redirect( admin_url() );
                exit;
            } else {
                // Display error message above the form.
                add_action( 'login_message', function() {
                    echo '<div style="color:red; margin-bottom:10px;">' . esc_html__( 'Invalid OTP. Please try again.', 'tp-secure-plugin' ) . '</div>';
                });
            }
        }
    }

    /**
     * Intercept login to trigger OTP sending instead of direct login.
     *
     * @param \WP_User|\WP_Error|null $user     The WP_User or WP_Error object returned from authentication.
     * @param string                 $username Username.
     * @param string                 $password Password.
     *
     * @return \WP_User|\WP_Error|null
     */
    public function intercept_login_with_otp( $user, $username, $password ) {
        if ( isset( $_POST['tpsa_otp_verify'] ) ) {
            // Let OTP verify handler manage login
            return $user;
        }

        if ( is_wp_error( $user ) ) {
            return $user;
        }

        // Generate and store OTP
        $otp = rand( 1000, 99999 );

        update_user_meta( $user->ID, '_tpsa_otp_code', [
            'username' => $username,
            'password' => $password,
            'otp'      => strval( $otp ),
        ] );

        

        // Send OTP email (replace or extend for SMS if needed)
        $this->send_mail( $user, $otp );

        // Redirect to OTP verification page
        wp_redirect( wp_login_url() . '?tpsa_pending=' . intval( $user->ID ) );
        exit;
    }

    private function send_mail( $user, $otp ) {

        $user_email = $user->user_email;
        $site_name = get_bloginfo( 'name' );
        $subject = sprintf( '%s OTP is', $site_name );

        $message = '
        <html>
        <head>
        <style>
            .email-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f9fc;
            font-family: Arial, sans-serif;
            text-align: center;
            border-radius: 8px;
            }
            .email-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333333;
            }
            .otp-box {
            background-color: #0073aa; /* WordPress blue */
            color: #ffffff;
            font-size: 32px;
            font-weight: bold;
            padding: 15px 30px;
            border-radius: 6px;
            user-select: all; /* Easy to select and copy */
            display: inline-block;
            letter-spacing: 6px;
            }
        </style>
        </head>
        <body>
        <div class="email-container">
            <div class="email-title">' . esc_html( $site_name ) . ' OTP is</div>
            <div class="otp-box">' . esc_html( $otp ) . '</div>
        </div>
        </body>
        </html>
        ';

        // Set content-type header for HTML email
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $user_email, $subject, $message, $headers );
    }

    private function sent_email_message( $user ) {
        if ( ! $user ) return;
        $email          = $user->user_email;
        $email_parts    = explode( '@', $email );
        $local          = $email_parts[0];
        $domain         = $email_parts[1] ?? '';
        $last_chars     = substr($local, -3);
        $masked_email   = '....' . $last_chars . '@' . $domain;

        ?>
            <p style="color: green; font-weight: 600; margin-bottom: 20px;">
                <?php 
                printf(
                    __( 'OTP code sent to your email address %s. Please check your inbox or spam folder.', 'tp-secure-plugin '), 
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
        return isset( $settings[ $key ] ) && (int) $settings[ $key ] === 1;
    }
}

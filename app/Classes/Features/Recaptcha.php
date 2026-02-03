<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Traits\Hook;
use WP_Error;

/**
 * Feature: Recaptcha
 *
 * Adds Google reCAPTCHA to login and registration forms.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class Recaptcha implements FeatureInterface {

    use Hook;
    use Asset;

    /**
     * Feature ID for settings and admin slug.
     *
     * @var string
     */
    private $features_id = 'recaptcha';

    /**
     * Plugin settings.
     *
     * @var array
     */
    private $settings = array();

    /**
     * Registers WordPress hooks.
     */
    public function register_hooks() {
        $this->action( 'init', array( $this, 'recaptcha_security' ) );
    }

    /**
     * Boot feature if enabled and keys are provided.
     */
    public function recaptcha_security() {
        $this->settings = $this->get_settings();

        if ( !$this->is_enabled( $this->settings ) ) {
            return;
        }

        // If it's email verification page then return (routing only).
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query var, no state change.
        if ( isset( $_GET['tpsa_verify_email_otp'] ) ) {
            return;
        }

        if ( empty( $this->settings['site-key'] ) || empty( $this->settings['secret-key'] ) ) {
            add_action( 'login_form', array( $this, 'show_recaptcha_error' ) );
            add_action( 'register_form', array( $this, 'show_recaptcha_error' ) );
            return;
        }

        /**
         * Default WordPress login & registration
         */
        $this->action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        $this->action( 'login_form', array( $this, 'show_recaptcha' ) );
        $this->action( 'register_form', array( $this, 'show_recaptcha' ) );

        $this->filter( 'authenticate', array( $this, 'verify_recaptcha_on_login' ), 21, 3 );
        $this->filter( 'registration_errors', array( $this, 'verify_recaptcha_on_register' ), 10, 1 );

        /**
         * WooCommerce login & registration
         */
        if ( class_exists( 'WooCommerce' ) ) {
            // Enqueue scripts on frontend as well (for My Account / Woo forms).
            $this->action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            // Output reCAPTCHA box in Woo login & register forms.
            $this->action( 'woocommerce_login_form', array( $this, 'show_recaptcha' ) );
            $this->action( 'woocommerce_register_form', array( $this, 'show_recaptcha' ) );

            // Validate on Woo login.
            $this->filter(
                'woocommerce_process_login_errors',
                array( $this, 'wc_verify_recaptcha_on_login' ),
                10,
                3
            );

            // Validate on Woo registration.
            $this->filter(
                'woocommerce_process_registration_errors',
                array( $this, 'wc_verify_recaptcha_on_register' ),
                10,
                4
            );
        }
    }

    /**
     * Enqueue Google reCAPTCHA script.
     */
    public function enqueue_scripts() {
        $site_key = isset( $this->settings['site-key'] ) ? (string) $this->settings['site-key'] : '';
        $site_key = sanitize_text_field( $site_key );

        $version = isset( $this->settings['version'] ) ? sanitize_key( $this->settings['version'] ) : 'v2';

        if ( 'v3' === $version ) {
            wp_enqueue_script(
                'google-recaptcha-v3',
                'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ),
                array(),
                null,
                true
            );

            // Escape site key for safe JS string usage.
            $site_key_js = esc_js( $site_key );

            $inline_script = "
				document.addEventListener('DOMContentLoaded', function () {
					if (typeof grecaptcha !== 'undefined') {
						grecaptcha.ready(function () {
							grecaptcha.execute('{$site_key_js}', {action: 'login_register'}).then(function (token) {
								var forms = document.querySelectorAll('form');
								if (forms.length) {
									forms.forEach(function(form) {
										if (!form.querySelector('input[name=\"g-recaptcha-response\"]')) {
											var input = document.createElement('input');
											input.type = 'hidden';
											input.name = 'g-recaptcha-response';
											input.value = token;
											form.appendChild(input);
										}
									});
								}
							});
						});
					}
				});
			";

            wp_add_inline_script( 'google-recaptcha-v3', $inline_script );

        } else {
            wp_enqueue_script(
                'google-recaptcha-v2',
                'https://www.google.com/recaptcha/api.js',
                array(),
                null,
                true
            );

            $this->enqueue_style(
                'google-recaptcha-v2',
                TPSA_ASSETS_URL . '/login/css/recaptcha.css'
            );
        }
    }

    /**
     * Display reCAPTCHA box in form.
     */
    public function show_recaptcha() {
        $version = isset( $this->settings['version'] ) ? sanitize_key( $this->settings['version'] ) : 'v2';

        if ( 'v2' === $version ) {
            $site_key = isset( $this->settings['site-key'] ) ? sanitize_text_field( (string) $this->settings['site-key'] ) : '';
            $theme = isset( $this->settings['theme'] ) ? sanitize_key( (string) $this->settings['theme'] ) : 'light';

            echo '<div class="g-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '" data-theme="' . esc_attr( $theme ) . '"></div>';
        }
    }

    /**
     * Show error if keys are missing.
     */
    public function show_recaptcha_error() {
        echo '<div style="color: red; margin: 10px 0;">';
        esc_html_e( 'reCAPTCHA keys are not configured properly. Please contact the site administrator.', 'admin-safety-guard' );
        echo '</div>';
    }

    /**
     * Verify reCAPTCHA on WP login.
     *
     * @param \WP_User|\WP_Error $user
     * @param string             $username
     * @param string             $password
     * @return \WP_User|\WP_Error
     */
    public function verify_recaptcha_on_login( $user, $username, $password ) {

        // Avoid undefined index warning.
        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        if ( 'POST' !== $method ) {
            return $user;
        }

        // NOTE: WordPress login form does not include a nonce that we can rely on here.
        // reCAPTCHA token verification + WP core login nonce protections are sufficient.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Login form POST is verified by reCAPTCHA token and WP core auth flow.

        $token = isset( $_POST['g-recaptcha-response'] )
        ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
        : '';

        if ( empty( $token ) ) {
            return new WP_Error( 'recaptcha_missing', __( 'reCAPTCHA verification missing.', 'admin-safety-guard' ) );
        }

        $response = $this->verify_recaptcha( $token );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return $user;
    }

    /**
     * Verify reCAPTCHA on WP registration.
     *
     * @param \WP_Error $errors
     * @return \WP_Error
     */
    public function verify_recaptcha_on_register( $errors ) {

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        if ( 'POST' !== $method ) {
            return $errors;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Registration form nonce is not always available; reCAPTCHA token is verified.
        $token = isset( $_POST['g-recaptcha-response'] )
        ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
        : '';

        $result = $this->verify_recaptcha( $token );

        if ( is_wp_error( $result ) ) {
            $errors->add( 'recaptcha_error', esc_html( $result->get_error_message() ) );
        }

        return $errors;
    }

    /**
     * WooCommerce: Verify reCAPTCHA on login.
     *
     * @param \WP_Error $errors
     * @param string    $username
     * @param string    $password
     *
     * @return \WP_Error
     */
    public function wc_verify_recaptcha_on_login( $errors, $username, $password ) {

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        if ( 'POST' !== $method ) {
            return $errors;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Woo login form nonce is not consistently available; reCAPTCHA token is verified.
        $token = isset( $_POST['g-recaptcha-response'] )
        ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
        : '';

        $result = $this->verify_recaptcha( $token );

        if ( is_wp_error( $result ) ) {
            $errors->add( 'recaptcha_error', esc_html( $result->get_error_message() ) );
        }

        return $errors;
    }

    /**
     * WooCommerce: Verify reCAPTCHA on registration.
     *
     * @param \WP_Error $errors
     * @param string    $username
     * @param string    $password
     * @param string    $email
     *
     * @return \WP_Error
     */
    public function wc_verify_recaptcha_on_register( $errors, $username, $password, $email ) {

        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        if ( 'POST' !== $method ) {
            return $errors;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Woo registration nonce is not consistently available; reCAPTCHA token is verified.
        $token = isset( $_POST['g-recaptcha-response'] )
        ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
        : '';

        $result = $this->verify_recaptcha( $token );

        if ( is_wp_error( $result ) ) {
            $errors->add( 'recaptcha_error', esc_html( $result->get_error_message() ) );
        }

        return $errors;
    }

    /**
     * reCAPTCHA token verification.
     *
     * @param string $token
     * @return true|\WP_Error
     */
    private function verify_recaptcha( $token ) {

        $secret_key = isset( $this->settings['secret-key'] ) ? sanitize_text_field( (string) $this->settings['secret-key'] ) : '';
        $version = isset( $this->settings['version'] ) ? sanitize_key( (string) $this->settings['version'] ) : 'v2';

        $token = is_scalar( $token ) ? sanitize_text_field( (string) $token ) : '';

        if ( empty( $secret_key ) ) {
            return new WP_Error( 'recaptcha_missing_secret', __( 'reCAPTCHA secret key is missing.', 'admin-safety-guard' ) );
        }

        if ( empty( $token ) ) {
            return new WP_Error( 'recaptcha_missing', __( 'reCAPTCHA token is missing.', 'admin-safety-guard' ) );
        }

        $remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
        : '';

        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            array(
                'timeout' => 20,
                'body'    => array(
                    'secret'   => $secret_key,
                    'response' => $token,
                    'remoteip' => $remote_ip,
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            // Avoid error_log() in production to pass WP coding standards.
            // If you need debugging, use WP_DEBUG + a dedicated logger, or trigger an action for developers.
            return new WP_Error( 'recaptcha_failed', __( 'Could not contact reCAPTCHA server.', 'admin-safety-guard' ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['success'] ) ) {
            return new WP_Error( 'recaptcha_invalid', __( 'reCAPTCHA verification failed.', 'admin-safety-guard' ) );
        }

        if ( 'v3' === $version ) {

            $score = isset( $body['score'] ) ? (float) $body['score'] : 0.0;
            $action = isset( $body['action'] ) ? sanitize_text_field( (string) $body['action'] ) : '';

            if ( $score < 0.5 ) {
                return new WP_Error( 'recaptcha_low_score', __( 'reCAPTCHA score too low. Try again.', 'admin-safety-guard' ) );
            }

            if ( 'login_register' !== $action ) {
                return new WP_Error( 'recaptcha_action_mismatch', __( 'reCAPTCHA action mismatch.', 'admin-safety-guard' ) );
            }
        }

        return true;
    }

    /**
     * Get plugin settings.
     *
     * @return array
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->features_id );
        $settings = get_option( $option_name, array() );

        return is_array( $settings ) ? $settings : array();
    }

    /**
     * Check if the feature is enabled.
     *
     * @param array $settings
     * @return bool
     */
    private function is_enabled( $settings ) {
        return !empty( $settings['enable'] ) && (int) $settings['enable'] === 1;
    }
}
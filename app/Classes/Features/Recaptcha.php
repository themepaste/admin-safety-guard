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
    private $settings = [];

    /**
     * Registers WordPress hooks.
     */
    public function register_hooks() {
        $this->action( 'init', [$this, 'recaptcha_security'] );
    }

    /**
     * Boot feature if enabled and keys are provided.
     */
    public function recaptcha_security() {
        $this->settings = $this->get_settings();

        if ( !$this->is_enabled( $this->settings ) ) {
            return;
        }

        // If it's email verification page then return.
        if ( isset( $_GET['tpsa_verify_email_otp'] ) ) {
            return;
        }

        if ( empty( $this->settings['site-key'] ) || empty( $this->settings['secret-key'] ) ) {
            add_action( 'login_form', [$this, 'show_recaptcha_error'] );
            add_action( 'register_form', [$this, 'show_recaptcha_error'] );
            return;
        }

        /**
         * Default WordPress login & registration
         */
        $this->action( 'login_enqueue_scripts', [$this, 'enqueue_scripts'] );
        $this->action( 'login_form', [$this, 'show_recaptcha'] );
        $this->action( 'register_form', [$this, 'show_recaptcha'] );

        $this->filter( 'authenticate', [$this, 'verify_recaptcha_on_login'], 21, 3 );
        $this->filter( 'registration_errors', [$this, 'verify_recaptcha_on_register'], 10, 1 );

        /**
         * WooCommerce login & registration
         */
        if ( class_exists( 'WooCommerce' ) ) {
            // Enqueue scripts on frontend as well (for My Account / Woo forms).
            $this->action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );

            // Output reCAPTCHA box in Woo login & register forms.
            $this->action( 'woocommerce_login_form', [$this, 'show_recaptcha'] );
            $this->action( 'woocommerce_register_form', [$this, 'show_recaptcha'] );

            // Validate on Woo login.
            $this->filter(
                'woocommerce_process_login_errors',
                [$this, 'wc_verify_recaptcha_on_login'],
                10,
                3
            );

            // Validate on Woo registration.
            $this->filter(
                'woocommerce_process_registration_errors',
                [$this, 'wc_verify_recaptcha_on_register'],
                10,
                4
            );
        }
    }

    /**
     * Enqueue Google reCAPTCHA script.
     */
    public function enqueue_scripts() {
        $site_key = isset( $this->settings['site-key'] ) ? esc_attr( $this->settings['site-key'] ) : '';
        $version = isset( $this->settings['version'] ) ? $this->settings['version'] : 'v2';

        if ( 'v3' === $version ) {
            wp_enqueue_script(
                'google-recaptcha-v3',
                'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ),
                [],
                null,
                true
            );

            $inline_script = "
				document.addEventListener('DOMContentLoaded', function () {
					if (typeof grecaptcha !== 'undefined') {
						grecaptcha.ready(function () {
							grecaptcha.execute('{$site_key}', {action: 'login_register'}).then(function (token) {
								var input = document.createElement('input');
								input.type = 'hidden';
								input.name = 'g-recaptcha-response';
								input.value = token;
								var forms = document.querySelectorAll('form');
								if (forms.length) {
									forms.forEach(function(form) {
										if (!form.querySelector('input[name=\"g-recaptcha-response\"]')) {
											form.appendChild(input.cloneNode(true));
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
                [],
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
        $version = $this->settings['version'] ?? 'v2';

        if ( 'v2' === $version ) {
            $site_key = esc_attr( $this->settings['site-key'] );
            $theme = esc_attr( $this->settings['theme'] ?? 'light' );

            echo '<div class="g-recaptcha" data-sitekey="' . $site_key . '" data-theme="' . $theme . '"></div>';
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
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return $user;
        }

        if ( empty( $_POST['g-recaptcha-response'] ) ) {
            return new WP_Error( 'recaptcha_missing', __( 'reCAPTCHA verification missing.', 'admin-safety-guard' ) );
        }

        $response = $this->verify_recaptcha( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) );

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
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return $errors;
        }

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
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            return $errors;
        }

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
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            return $errors;
        }

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
        $secret_key = sanitize_text_field( $this->settings['secret-key'] );
        $version = $this->settings['version'] ?? 'v2';

        if ( empty( $token ) ) {
            return new WP_Error( 'recaptcha_missing', __( 'reCAPTCHA token is missing.', 'admin-safety-guard' ) );
        }

        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'body' => [
                    'secret'   => $secret_key,
                    'response' => $token,
                    'remoteip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            error_log( '[TPSA reCAPTCHA] wp_remote_post error: ' . $response->get_error_message() );
            error_log( '[TPSA reCAPTCHA] error code: ' . $response->get_error_code() );

            return new WP_Error( 'recaptcha_failed', __( 'Could not contact reCAPTCHA server.', 'admin-safety-guard' ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['success'] ) ) {
            return new WP_Error( 'recaptcha_invalid', __( 'reCAPTCHA verification failed.', 'admin-safety-guard' ) );
        }

        if ( 'v3' === $version ) {
            if ( empty( $body['score'] ) || $body['score'] < 0.5 ) {
                return new WP_Error( 'recaptcha_low_score', __( 'reCAPTCHA score too low. Try again.', 'admin-safety-guard' ) );
            }

            if ( empty( $body['action'] ) || 'login_register' !== $body['action'] ) {
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
        return get_option( $option_name, [] );
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
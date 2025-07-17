<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;
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
		$this->action( 'init', [ $this, 'recaptcha_security' ] );
	}

	/**
	 * Boot feature if enabled and keys are provided.
	 */
	public function recaptcha_security() {
		$this->settings = $this->get_settings();

		if ( ! $this->is_enabled( $this->settings ) ) {
			return;
		}

		//If its email verification page than return
		if ( isset( $_GET['tpsa_verify_email_otp'] ) ) {
            return;
        }

		if ( empty( $this->settings['site-key'] ) || empty( $this->settings['secret-key'] ) ) {
			add_action( 'login_form', [ $this, 'show_recaptcha_error' ] );
			add_action( 'register_form', [ $this, 'show_recaptcha_error' ] );
			return;
		}

		$this->action( 'login_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		$this->action( 'login_form', [ $this, 'show_recaptcha' ] );
		$this->action( 'register_form', [ $this, 'show_recaptcha' ] );

		$this->filter( 'authenticate', [ $this, 'verify_recaptcha_on_login' ], 21, 3 );
		$this->filter( 'registration_errors', [ $this, 'verify_recaptcha_on_register' ], 10, 1 );
	}

	/**
	 * Enqueue Google reCAPTCHA script.
	 */
	public function enqueue_scripts() {
		$site_key = isset( $this->settings['site-key'] ) ? esc_attr( $this->settings['site-key'] ) : '';
		$version  = isset( $this->settings['version'] ) ? $this->settings['version'] : 'v2';

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
								var form = document.querySelector('form');
								if (form) {
									form.appendChild(input);
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
			$theme    = esc_attr( $this->settings['theme'] ?? 'light' );

			echo '<div class="g-recaptcha" data-sitekey="' . $site_key . '" data-theme="' . $theme . '"></div>';
		}
	}

	/**
	 * Show error if keys are missing.
	 */
	public function show_recaptcha_error() {
		echo '<div style="color: red; margin: 10px 0;">';
		esc_html_e( 'reCAPTCHA keys are not configured properly. Please contact the site administrator.', 'tp-secure-plugin' );
		echo '</div>';
	}

	/**
	 * Verify reCAPTCHA on login.
	 *
	 * @param WP_User|WP_Error $user
	 * @param string           $username
	 * @param string           $password
	 * @return WP_User|WP_Error
	 */
	public function verify_recaptcha_on_login( $user, $username, $password ) {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return $user;
		}

		if ( empty( $_POST['g-recaptcha-response'] ) ) {
			return new WP_Error( 'recaptcha_missing', __( 'reCAPTCHA verification missing.', 'tp-secure-plugin' ) );
		}

		$response = $this->verify_recaptcha( sanitize_text_field( $_POST['g-recaptcha-response'] ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $user;
	}

	/**
	 * Verify reCAPTCHA on registration.
	 *
	 * @param WP_Error $errors
	 * @return WP_Error
	 */
	public function verify_recaptcha_on_register( $errors ) {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return $errors;
		}

		$token  = $_POST['g-recaptcha-response'] ?? '';
		$result = $this->verify_recaptcha( sanitize_text_field( $token ) );

		if ( is_wp_error( $result ) ) {
			$errors->add( 'recaptcha_error', esc_html( $result->get_error_message() ) );
		}

		return $errors;
	}

	/**
	 * reCAPTCHA token verification.
	 *
	 * @param string $token
	 * @return true|WP_Error
	 */
	private function verify_recaptcha( $token ) {
		$secret_key = sanitize_text_field( $this->settings['secret-key'] );
		$version    = $this->settings['version'] ?? 'v2';

		if ( empty( $token ) ) {
			return new WP_Error( 'recaptcha_missing', __( 'reCAPTCHA token is missing.', 'tp-secure-plugin' ) );
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
			return new WP_Error( 'recaptcha_failed', __( 'Could not contact reCAPTCHA server.', 'tp-secure-plugin' ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) ) {
			return new WP_Error( 'recaptcha_invalid', __( 'reCAPTCHA verification failed.', 'tp-secure-plugin' ) );
		}

		if ( 'v3' === $version ) {
			if ( empty( $body['score'] ) || $body['score'] < 0.5 ) {
				return new WP_Error( 'recaptcha_low_score', __( 'reCAPTCHA score too low. Try again.', 'tp-secure-plugin' ) );
			}

			if ( empty( $body['action'] ) || 'login_register' !== $body['action'] ) {
				return new WP_Error( 'recaptcha_action_mismatch', __( 'reCAPTCHA action mismatch.', 'tp-secure-plugin' ) );
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
		return ! empty( $settings['enable'] ) && (int) $settings['enable'] === 1;
	}
}

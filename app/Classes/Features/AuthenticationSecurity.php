<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined('ABSPATH') || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;
use WP_Error;

/**
 * Feature: AuthenticationSecurity
 *
 * Adds Google reCAPTCHA to login and registration forms.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class AuthenticationSecurity implements FeatureInterface {

    use Hook;

    private $features_id = 'authentication-security';

    private $settings = [];

    public function register_hooks() {
        $this->action('init', [$this, 'authentication_security']);
    }

    public function authentication_security() {
        $this->settings = $this->get_settings();
        if (!$this->is_enabled($this->settings)) {
            return;
        }

        if (empty($this->settings['site-key']) || empty($this->settings['secret-key'])) {
            add_action('login_form', [$this, 'show_recaptcha_error']);
            add_action('register_form', [$this, 'show_recaptcha_error']);
            return;
        }

        $this->action('login_enqueue_scripts', [$this, 'enqueue_scripts']);
        $this->action('login_form', [$this, 'show_recaptcha']);
        $this->action('register_form', [$this, 'show_recaptcha']);

        $this->filter('authenticate', [$this, 'verify_recaptcha_on_login'], 21, 3);
        $this->filter('registration_errors', [$this, 'verify_recaptcha_on_register'], 10, 1);
    }

    public function enqueue_scripts() {
        $site_key = esc_attr($this->settings['site-key']);
        $version  = $this->settings['version'] ?? 'v2';

        if ($version === 'v3') {
            echo "<script src='https://www.google.com/recaptcha/api.js?render={$site_key}'></script>";
            echo "<script>
                grecaptcha.ready(function() {
                    grecaptcha.execute('{$site_key}', {action: 'login_register'}).then(function(token) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'g-recaptcha-response';
                        input.value = token;
                        document.forms[0].appendChild(input);
                    });
                });
            </script>";
        } else {
            echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        }
    }

    public function show_recaptcha() {
        $version = $this->settings['version'] ?? 'v2';

        if ($version === 'v2') {
            $site_key = esc_attr($this->settings['site-key']);
            $theme    = esc_attr($this->settings['theme'] ?? 'light');

            echo '<div class="g-recaptcha" data-sitekey="' . $site_key . '" data-theme="' . $theme . '"></div>';
        }
    }

    public function show_recaptcha_error() {
        echo '<div style="color: red; margin: 10px 0;">';
        esc_html_e('reCAPTCHA keys are not configured properly. Please contact the site administrator.', 'tp-secure-plugin');
        echo '</div>';
    }

    public function verify_recaptcha_on_login($user, $username, $password) {
        if (!isset($_POST['g-recaptcha-response'])) {
            return new WP_Error('recaptcha_missing', __('reCAPTCHA verification missing.', 'tp-secure-plugin'));
        }

        $response = $this->verify_recaptcha(sanitize_text_field($_POST['g-recaptcha-response']));
        if (is_wp_error($response)) {
            return $response;
        }

        return $user;
    }

    public function verify_recaptcha_on_register($errors) {
        $token  = $_POST['g-recaptcha-response'] ?? '';
        $result = $this->verify_recaptcha(sanitize_text_field($token));

        if (is_wp_error($result)) {
            $errors->add('recaptcha_error', esc_html($result->get_error_message()));
        }

        return $errors;
    }

    private function verify_recaptcha($token) {
        $secret_key = sanitize_text_field($this->settings['secret-key']);
        $version    = $this->settings['version'] ?? 'v2';

        if (empty($token)) {
            return new WP_Error('recaptcha_missing', __('reCAPTCHA token is missing.', 'tp-secure-plugin'));
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $secret_key,
                'response' => $token,
                'remoteip' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('recaptcha_failed', __('Could not contact reCAPTCHA server.', 'tp-secure-plugin'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['success'])) {
            return new WP_Error('recaptcha_invalid', __('reCAPTCHA verification failed.', 'tp-secure-plugin'));
        }

        if ($version === 'v3') {
            if (empty($body['score']) || $body['score'] < 0.5) {
                return new WP_Error('recaptcha_low_score', __('reCAPTCHA score too low. Try again.', 'tp-secure-plugin'));
            }

            if (empty($body['action']) || $body['action'] !== 'login_register') {
                return new WP_Error('recaptcha_action_mismatch', __('reCAPTCHA action mismatch.', 'tp-secure-plugin'));
            }
        }

        return true;
    }

    private function get_settings() {
        $option_name = get_tpsa_settings_option_name($this->features_id);
        return get_option($option_name, []);
    }

    private function is_enabled($settings) {
        return !empty($settings['enable']) && (int) $settings['enable'] === 1;
    }
}

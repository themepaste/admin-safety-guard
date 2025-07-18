<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined('ABSPATH') || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class PasswordProtection implements FeatureInterface {

    use Hook;

    private $features_id = 'password-protection';

    public function register_hooks() {
        $this->filter( 'tpsa_password-protection_password-expiry', [$this, 'modify_the_password_expiry_field'], 10, 2 );
        $this->action('template_redirect', [$this, 'password_protection'], 0);
    }

    public function modify_the_password_expiry_field( $template, $args ) {
        $template = str_replace(
            '<input type="number" id="%2$s" name="%2$s" value="%3$s">',
            '<input type="number" id="%2$s" name="%2$s" value="%3$s">' . ' Days',
            $template
        );
        return $template;
    }

    public function password_protection() {
        // Skip for logged in users
        if ( is_user_logged_in() ) {
            return;
        }

        $settings = $this->get_settings();

        if ( ! $this->is_enabled( $settings ) ) {
            return;
        }

        // Password set in the settings (fallback to 'tpsm')
        $password = isset( $settings['password'] ) ? trim( $settings['password'] ) : 'tpsm';
        $password_expiry   = isset( $settings['password-expiry'] ) ? trim( $settings['password-expiry'] ) : 15;
        // $password_second = $password_expiry * 86400;
        $password_second = 10;

        // Cookie name
        $cookie_name = 'tpsa_site_password';

        // If password form is submitted
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['tpsa_site_password'] ) ) {
            if ( trim( $_POST['tpsa_site_password'] ) === $password) {
                setcookie( $cookie_name, md5( $password ), time() + $password_second, COOKIEPATH, COOKIE_DOMAIN);
                wp_redirect( $_SERVER['REQUEST_URI'] );
                exit;
            } else {
                $GLOBALS['tpsa_password_error'] = 'Incorrect password.';
            }
        }

        // If cookie not set or incorrect
        if ( !isset( $_COOKIE[$cookie_name] ) || $_COOKIE[$cookie_name] !== md5( $password ) ) {
            $this->render_password_form();
            exit;
        }
    }

    private function render_password_form() {
        $error = isset( $GLOBALS['tpsa_password_error'] ) ? '<div style="color:red;">' . esc_html( $GLOBALS['tpsa_password_error'] ) . '</div>' : '';
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="robots" content="noindex, nofollow">
            <title><?php bloginfo( 'name' ) . esc_html_e( ' - Protected', 'tp-secure-plugin' ); ?></title>
            <?php wp_head(); ?>
        </head>
        <body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#f9f9f9;">
            <form method="post" style="background:#fff; padding:2rem; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom:1rem;"><?php esc_html_e( 'Enter Password to Access', 'tp-secure-plugin' ) ?></h2>
                <?php echo $error; ?>
                <input type="password" name="tpsa_site_password" style="padding:10px; width:100%; margin-bottom:1rem;" required>
                <button type="submit" style="padding:10px 20px; background:#0073aa; color:#fff; border:none; cursor:pointer;"><?php esc_html_e( 'Submit', 'tp-secure-plugin' ); ?></button>
            </form>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }

    private function get_settings() {
        $option_name = get_tpsa_settings_option_name($this->features_id);
        return get_option($option_name, []);
    }

    private function is_enabled($settings) {
        return isset($settings['enable']) && $settings['enable'] == 1;
    }
}

<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Class PasswordProtection
 *
 * Provides site-wide password protection for non-logged-in users.
 * Displays a password form on all front-end pages and sets a cookie on correct entry.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class PasswordProtection implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings and filtering.
     *
     * @var string
     */
    private $features_id = 'password-protection';

    /**
     * Register hooks for the feature.
     *
     * @return void
     */
    public function register_hooks() {
        $this->filter( 'tpsa_password-protection_password-expiry', [$this, 'modify_the_password_expiry_field'], 10, 2 );
        $this->action( 'template_redirect', [$this, 'password_protection'], 0 );
    }

    /**
     * Modify the password expiry input field in the settings UI.
     *
     * @param string $template HTML template for the field.
     * @param array  $args     Arguments passed to the field.
     *
     * @return string Modified HTML template.
     */
    public function modify_the_password_expiry_field( $template, $args ) {
        $template = str_replace(
            '<input type="number" id="%2$s" name="%2$s" value="%3$s">',
            '<input type="number" id="%2$s" name="%2$s" value="%3$s">' . ' Days',
            $template
        );

        return $template;
    }

    /**
     * Main password protection logic.
     * Blocks access to all front-end pages unless password is submitted or user is logged in.
     *
     * @return void
     */
    public function password_protection() {

        $current_user = wp_get_current_user();
        $current_user_roles = (array) $current_user->roles; // roles is an array

        // Assuming single-role users (most common)
        $current_user_role_key = $current_user_roles[0] ?? null;

        $settings = $this->get_settings();
        $exclude_users = $settings['exclude'] ?? [];

        if ( in_array( 'all-login-user', $exclude_users ) ) {
            if ( is_user_logged_in() ) {
                return;
            }
        }

        if ( $current_user_role_key && in_array( $current_user_role_key, $exclude_users ) ) {
            return; // Exit early if the current user's role is in the exclude list
        }

        if ( array_intersect( $current_user_roles, $exclude_users ) ) {
            return;
        }

        if ( in_array( get_current_user_id(), $exclude_users ) ) {
            return;
        }

        // Skip if the feature is not enabled.
        if ( !$this->is_enabled( $settings ) ) {
            return;
        }

        // Get password from settings, fallback to 'tpsm'.
        $password = isset( $settings['password'] ) ? trim( $settings['password'] ) : 'tpsm';

        // Get expiry days and convert to seconds.
        $password_expiry = isset( $settings['password-expiry'] ) ? (int) $settings['password-expiry'] : 15;
        $password_second = $password_expiry * DAY_IN_SECONDS;

        // Cookie key used to store password hash.
        $cookie_name = 'tpsa_site_password';

        // Handle form submission.
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['tpsa_site_password'] ) ) {
            if ( trim( $_POST['tpsa_site_password'] ) === $password ) {
                setcookie( $cookie_name, md5( $password ), time() + $password_second, COOKIEPATH, COOKIE_DOMAIN );
                wp_redirect( $_SERVER['REQUEST_URI'] );
                exit;
            } else {
                $GLOBALS['tpsa_password_error'] = __( 'Incorrect password.', 'admin-safety-guard' );
            }
        }

        // If the cookie is not set or invalid, show password form.
        if ( !isset( $_COOKIE[$cookie_name] ) || $_COOKIE[$cookie_name] !== md5( $password ) ) {
            $this->render_password_form();
            exit;
        }
    }

    /**
     * Render the password entry form.
     *
     * @return void
     */
    private function render_password_form() {
        $error = isset( $GLOBALS['tpsa_password_error'] )
        ? '<div style="color:red;">' . esc_html( $GLOBALS['tpsa_password_error'] ) . '</div>'
        : '';
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <meta name="robots" content="noindex, nofollow">
            <title><?php bloginfo( 'name' ); ?><?php esc_html_e( ' - Protected', 'admin-safety-guard' ); ?></title>
            <?php wp_head(); ?>
        </head>
        <body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#f9f9f9;">
            <form method="post" style="background:#fff; padding:2rem; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom:1rem;"><?php esc_html_e( 'Enter Password to Access', 'admin-safety-guard' ); ?></h2>
                <?php echo $error; ?>
                <input type="password" name="tpsa_site_password" style="padding:10px; width:100%; margin-bottom:1rem;" required>
                <button type="submit" style="padding:10px 20px; background:#0073aa; color:#fff; border:none; cursor:pointer;"><?php esc_html_e( 'Submit', 'admin-safety-guard' ); ?></button>
            </form>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
}

    /**
     * Retrieve feature settings from the database.
     *
     * @return array Settings array.
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->features_id );

        return get_option( $option_name, [] );
    }

    /**
     * Check if the feature is enabled in the settings.
     *
     * @param array $settings Settings array.
     *
     * @return bool
     */
    private function is_enabled( $settings ) {
        return isset( $settings['enable'] ) && (int) $settings['enable'] === 1;
    }
}

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

        $settings = $this->get_settings();

        // Skip if the feature is not enabled. This has to come first: the
        // exclusion checks below are pure overhead on every front-end request
        // when the feature is off.
        if ( !$this->is_enabled( $settings ) ) {
            return;
        }

        // The stored value can be a string when the option was never saved as a
        // multi-check, so normalise before any in_array() call.
        $exclude_users = isset( $settings['exclude'] ) && is_array( $settings['exclude'] )
        ? array_map( 'strval', $settings['exclude'] )
        : [];

        if ( is_user_logged_in() && in_array( 'all-login-user', $exclude_users, true ) ) {
            return;
        }

        // Strict comparison throughout: a loose in_array() would match the
        // integer user ID against arbitrary role strings.
        $current_user_roles = array_map( 'strval', (array) wp_get_current_user()->roles );

        if ( array_intersect( $current_user_roles, $exclude_users ) ) {
            return;
        }

        if ( is_user_logged_in() && in_array( (string) get_current_user_id(), $exclude_users, true ) ) {
            return;
        }

        // Password from settings. There is deliberately no fallback default:
        // a shipped default would be identical on every install and therefore
        // publicly known. With no password configured the gate cannot be passed
        // by anyone, so the feature stays inactive instead of locking the site.
        $password = isset( $settings['password'] ) ? trim( (string) $settings['password'] ) : '';

        if ( '' === $password ) {
            return;
        }

        // Get expiry days and convert to seconds.
        $password_expiry = isset( $settings['password-expiry'] ) ? max( 1, (int) $settings['password-expiry'] ) : 15;
        $password_second = $password_expiry * DAY_IN_SECONDS;

        // Cookie key used to store the access token.
        $cookie_name = 'tpsa_site_password';

        // Signed, site-secret-keyed token. Unlike a bare md5(password), this cannot
        // be precomputed/forged without the site's auth keys.
        $token = $this->get_cookie_token( $password );

        // Handle form submission.
        $request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
        if ( 'POST' === $request_method && isset( $_POST['tpsa_site_password'] ) ) {
            $nonce = isset( $_POST['tpsa_pp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_pp_nonce'] ) ) : '';
            if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tpsa_password_protection' ) ) {
                $GLOBALS['tpsa_password_error'] = __( 'Security check failed.', 'admin-safety-guard' );
                $this->render_password_form();
                exit();
            }

            $submitted_password = trim( sanitize_text_field( wp_unslash( $_POST['tpsa_site_password'] ) ) );

            // Constant-time comparison to avoid leaking the password via timing.
            if ( hash_equals( $password, $submitted_password ) ) {
                $this->set_access_cookie( $cookie_name, $token, time() + $password_second );

                $redirect_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
                wp_safe_redirect( $redirect_uri );
                exit();
            } else {
                $GLOBALS['tpsa_password_error'] = __( 'Incorrect password.', 'admin-safety-guard' );
            }
        }

        // If the cookie is not set or does not match the signed token, show the form.
        $cookie_value = isset( $_COOKIE[$cookie_name] ) ? (string) wp_unslash( $_COOKIE[$cookie_name] ) : '';
        if ( '' === $cookie_value || ! hash_equals( $token, $cookie_value ) ) {
            $this->render_password_form();
            exit();
        }
    }

    /**
     * Build the signed access-cookie token for a given site password.
     *
     * Uses wp_hash() so the token is keyed with the site's secret auth salts and
     * cannot be reproduced by anyone who does not already know the password AND
     * have the site secret.
     *
     * @param string $password The configured site password.
     *
     * @return string
     */
    private function get_cookie_token( $password ) {
        return wp_hash( 'tpsa_pp|' . $password );
    }

    /**
     * Set the access cookie with secure attributes (HttpOnly, Secure, SameSite).
     *
     * @param string $name    Cookie name.
     * @param string $value   Cookie value (signed token).
     * @param int    $expires Expiry timestamp.
     *
     * @return void
     */
    private function set_access_cookie( $name, $value, $expires ) {
        setcookie(
            $name,
            $value,
            array(
                'expires'  => $expires,
                'path'     => COOKIEPATH ? COOKIEPATH : '/',
                'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            )
        );
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
        <?php echo wp_kses_post( $error ); ?>
        <?php wp_nonce_field( 'tpsa_password_protection', 'tpsa_pp_nonce' ); ?>
        <input type="password" name="tpsa_site_password" style="padding:10px; width:100%; margin-bottom:1rem;" required>
        <button type="submit"
            style="padding:10px 20px; background:#0073aa; color:#fff; border:none; cursor:pointer;"><?php esc_html_e( 'Submit', 'admin-safety-guard' ); ?></button>
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
<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: Customize
 *
 * Brands the WordPress sign-in screen: logo, colours, background, and which of
 * the default links are shown.
 *
 * Everything is emitted as a single inline stylesheet built once per request,
 * so a fully branded login page costs no extra HTTP requests and no database
 * work beyond the one option read.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class Customize implements FeatureInterface {

    use Hook;

    /**
     * Settings screen slug.
     *
     * @var string
     */
    private $features_id = 'customize';

    /**
     * Cached settings.
     *
     * @var array|null
     */
    private $settings = null;

    public function register_hooks() {
        // login_enqueue_scripts fires for every wp-login.php view — sign in,
        // register, lost password and reset — so one hook covers them all.
        // The old code also hooked register_form, which printed a <style> block
        // in the middle of the form and registered the header filter too late
        // to have any effect.
        $this->action( 'login_enqueue_scripts', [$this, 'print_login_styles'] );

        if ( !$this->is_enabled() ) {
            return;
        }

        $this->filter( 'login_headerurl', [$this, 'login_header_url'] );
        $this->filter( 'login_headertext', [$this, 'login_header_text'] );

        $settings = $this->get_settings();

        if ( !empty( $settings['remember-me'] ) ) {
            $this->action( 'login_footer', [$this, 'precheck_remember_me'] );
        }
    }

    /**
     * Build and print the login stylesheet.
     *
     * @return void
     */
    public function print_login_styles() {
        if ( !$this->is_enabled() ) {
            return;
        }

        $css = $this->build_css();

        if ( '' === trim( $css ) ) {
            return;
        }

        // wp_add_inline_style needs a registered handle; login-styles is the
        // stylesheet WordPress itself enqueues on wp-login.php.
        wp_add_inline_style( 'login', $css );
    }

    /**
     * Compose the stylesheet from the saved options.
     *
     * @return string
     */
    private function build_css() {
        $s = $this->get_settings();
        $out = [];

        // --- Logo -----------------------------------------------------------
        $logo = isset( $s['logo'] ) ? esc_url_raw( (string) $s['logo'] ) : '';

        if ( '' !== $logo ) {
            $width = $this->px( $s['logo-width'] ?? 84, 84 );
            $height = $this->px( $s['logo-height'] ?? 84, 84 );

            $out[] = sprintf(
                '#login h1 a, .login h1 a {
    background-image: url(%1$s);
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center center;
    width: %2$dpx;
    height: %3$dpx;
    margin-bottom: 20px;
}',
                esc_url( $logo ),
                $width,
                $height
            );
        }

        // --- Page background -------------------------------------------------
        $body = [];

        $bg_color = $this->color( $s['bg-color'] ?? '' );
        if ( '' !== $bg_color ) {
            $body[] = sprintf( 'background-color: %s;', $bg_color );
        }

        $bg_image = isset( $s['bg-image'] ) ? esc_url_raw( (string) $s['bg-image'] ) : '';
        if ( '' !== $bg_image ) {
            $body[] = sprintf( 'background-image: url(%s);', esc_url( $bg_image ) );
            $body[] = 'background-size: cover;';
            $body[] = 'background-position: center center;';
            $body[] = 'background-repeat: no-repeat;';
            $body[] = 'background-attachment: fixed;';
        }

        if ( $body ) {
            $out[] = "body.login {\n    " . implode( "\n    ", $body ) . "\n}";
        }

        // --- Form card -------------------------------------------------------
        $form = [];

        $form_bg = $this->color( $s['form-bg-color'] ?? '' );
        if ( '' !== $form_bg ) {
            $form[] = sprintf( 'background: %s;', $form_bg );
        }

        if ( !empty( $s['form-rounded'] ) ) {
            $form[] = 'border-radius: 10px;';
        }

        if ( $form ) {
            $out[] = ".login form {\n    " . implode( "\n    ", $form ) . "\n}";
        }

        // --- Text and links ---------------------------------------------------
        $text_color = $this->color( $s['text-color'] ?? '' );
        if ( '' !== $text_color ) {
            $out[] = sprintf(
                '.login form label, .login form, .login #backtoblog a, .login #nav a {
    color: %s;
}',
                $text_color
            );
        }

        $link_color = $this->color( $s['link-color'] ?? '' );
        if ( '' !== $link_color ) {
            $out[] = sprintf(
                '.login #nav a, .login #backtoblog a, .login a {
    color: %1$s;
}
.login #nav a:hover, .login #backtoblog a:hover, .login a:hover {
    color: %1$s;
    opacity: .8;
}',
                $link_color
            );
        }

        // --- Primary button ---------------------------------------------------
        $button_color = $this->color( $s['button-color'] ?? '' );
        if ( '' !== $button_color ) {
            $out[] = sprintf(
                '.login .button-primary {
    background: %1$s;
    border-color: %1$s;
    color: #fff;
    text-shadow: none;
    box-shadow: none;
}
.login .button-primary:hover,
.login .button-primary:focus {
    background: %1$s;
    border-color: %1$s;
    color: #fff;
    opacity: .9;
}
.login input[type="text"]:focus,
.login input[type="password"]:focus,
.login input[type="email"]:focus {
    border-color: %1$s;
    box-shadow: 0 0 0 1px %1$s;
    outline: 2px solid transparent;
}',
                $button_color
            );
        }

        // --- Hide default links ------------------------------------------------
        $hide = [];

        if ( !empty( $s['hide-lost-password'] ) ) {
            $hide[] = '.login #nav a[href*="lostpassword"]';
        }

        if ( !empty( $s['hide-register'] ) ) {
            $hide[] = '.login #nav a[href*="action=register"]';
            $hide[] = '.login #nav a[href*="wp-signup"]';
        }

        if ( !empty( $s['hide-back-to-site'] ) ) {
            $hide[] = '.login #backtoblog';
        }

        if ( !empty( $s['hide-language-switcher'] ) ) {
            $hide[] = '.login .language-switcher';
        }

        if ( !empty( $s['hide-logo'] ) ) {
            $hide[] = '#login h1';
        }

        if ( $hide ) {
            $out[] = implode( ",\n", $hide ) . " {\n    display: none !important;\n}";
        }

        // --- Free-form CSS ------------------------------------------------------
        if ( !empty( $s['custom-css'] ) ) {
            $out[] = $this->sanitize_css( (string) $s['custom-css'] );
        }

        return implode( "\n\n", array_filter( $out ) );
    }

    /* ---------------------------------------------------------------------
     * Header link and text
     * ------------------------------------------------------------------- */

    /**
     * Where the logo links to. Defaults to the site home rather than wordpress.org.
     *
     * @param string $url Current URL.
     * @return string
     */
    public function login_header_url( $url ) {
        $s = $this->get_settings();
        $target = isset( $s['logo-url'] ) ? trim( (string) $s['logo-url'] ) : '';

        if ( '' === $target ) {
            return home_url( '/' );
        }

        $target = esc_url_raw( $target );

        return $target ? $target : home_url( '/' );
    }

    /**
     * Accessible text behind the logo.
     *
     * @param string $text Current text.
     * @return string
     */
    public function login_header_text( $text ) {
        $s = $this->get_settings();
        $custom = isset( $s['logo-text'] ) ? trim( (string) $s['logo-text'] ) : '';

        return '' !== $custom ? esc_html( $custom ) : $text;
    }

    /**
     * Tick "Remember Me" by default.
     *
     * @return void
     */
    public function precheck_remember_me() {
        ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('rememberme');
    if (box) { box.checked = true; }
});
</script>
<?php
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    /**
     * Validate a colour value.
     *
     * Accepts #rgb / #rrggbb, and the rgb()/rgba() forms, so a value pasted
     * from a design tool still works. Anything else is discarded rather than
     * written into the stylesheet.
     *
     * @param mixed $value Raw setting.
     * @return string Safe CSS colour, or '' to skip the rule.
     */
    private function color( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/i', $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * Clamp a pixel dimension.
     *
     * @param mixed $value   Raw setting.
     * @param int   $default Fallback.
     *
     * @return int
     */
    private function px( $value, $default ) {
        $value = (int) $value;

        return ( $value > 0 && $value <= 600 ) ? $value : $default;
    }

    /**
     * Strip anything from the custom CSS box that is not CSS.
     *
     * The value is administrator-authored, but it is printed into a <style>
     * block on a page served to logged-out visitors, so a stray </style> or a
     * javascript: URL must not survive.
     *
     * @param string $css Raw CSS.
     * @return string
     */
    private function sanitize_css( $css ) {
        $css = wp_strip_all_tags( $css );
        $css = preg_replace( '#javascript\s*:#i', '', $css );
        $css = preg_replace( '#expression\s*\(#i', '', $css );
        $css = str_replace( ['<', '>'], '', $css );

        return trim( $css );
    }

    private function get_settings() {
        if ( null === $this->settings ) {
            $settings = get_option( get_tpsa_settings_option_name( $this->features_id ), [] );
            $this->settings = is_array( $settings ) ? $settings : [];
        }

        return $this->settings;
    }

    private function is_enabled() {
        $s = $this->get_settings();

        return isset( $s['enable'] ) && (int) $s['enable'] === 1;
    }
}

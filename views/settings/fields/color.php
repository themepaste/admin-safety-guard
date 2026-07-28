<?php
/**
 * Output a colour picker field.
 *
 * Uses the native colour input with a text field beside it, so a value can be
 * picked visually or pasted as a hex code. Empty means "leave the WordPress
 * default alone" rather than black.
 *
 * @package ThemePaste
 */

defined( 'ABSPATH' ) || exit;

$id_name = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
$value = isset( $args['value'] ) ? trim( (string) $args['value'] ) : '';
$swatch = '' !== $value ? $value : '#ffffff';
?>
<div class="tp-field">
    <div class="tp-field-label">
        <label for="<?php echo esc_attr( $id_name ); ?>"><?php echo esc_html( $args['field']['label'] ); ?></label>
    </div>
    <div class="tp-field-input">
        <div class="tp-color-wrapper">
            <input type="color" class="tp-color-swatch" value="<?php echo esc_attr( $swatch ); ?>"
                data-target="<?php echo esc_attr( $id_name ); ?>"
                aria-label="<?php echo esc_attr( $args['field']['label'] ); ?>">

            <input type="text" id="<?php echo esc_attr( $id_name ); ?>" name="<?php echo esc_attr( $id_name ); ?>"
                class="tp-color-text" value="<?php echo esc_attr( $value ); ?>" placeholder="#ffffff"
                spellcheck="false" autocomplete="off">

            <button type="button" class="tp-color-clear"
                data-target="<?php echo esc_attr( $id_name ); ?>"><?php esc_html_e( 'Clear', 'admin-safety-guard' ); ?></button>
        </div>
        <p class="tp-field-desc"><?php echo wp_kses_post( $args['field']['desc'] ); ?></p>
    </div>
</div>

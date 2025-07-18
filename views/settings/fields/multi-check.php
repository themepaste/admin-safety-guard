<?php
/**
 * Output a checkbox group (multi-select via checkboxes).
 *
 * @package ThemePaste
 */

defined( 'ABSPATH' ) || exit;

$id_base = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );

// Ensure $value is an array.
$value   = isset( $args['value'] ) && is_array( $args['value'] ) ? $args['value'] : [];
$options = isset( $args['field']['options'] ) && is_array( $args['field']['options'] ) ? $args['field']['options'] : [];

$checkboxes_html = '';
foreach ( $options as $option_key => $option_label ) {
    $field_id = $id_base . '_' . sanitize_key( $option_key );
    $checked  = in_array( $option_key, $value ) ? 'checked' : '';

    $checkboxes_html .= sprintf(
        '<label for="%1$s" style="display:block; margin-bottom:4px;">
            <input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s" %4$s />
            %5$s
        </label>',
        esc_attr( $field_id ),         // %1$s: ID
        esc_attr( $id_base ),          // %2$s: name[]
        esc_attr( $option_key ),       // %3$s: value
        $checked,                      // %4$s: checked
        esc_html( $option_label )      // %5$s: label
    );
}

$field_template = '
    <div class="tp-field">
        <div class="tp-field-label">
            <label>%1$s</label>
        </div>
        <div class="tp-field-input">
            %2$s
            <p class="tp-field-desc">%3$s</p>
        </div>
    </div>';

printf(
    $field_template,
    esc_html( $args['field']['label'] ),   // %1$s: Label
    $checkboxes_html,                      // %2$s: Checkboxes
    esc_html( $args['field']['desc'] )     // %3$s: Description
);
?>

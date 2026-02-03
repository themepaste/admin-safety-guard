<?php
/**
 * Output a social-login multi-check using switch UI per row.
 *
 * Saves an array of enabled providers, e.g. ['google','facebook'] to option/meta.
 *
 * @package ThemePaste
 */

defined( 'ABSPATH' ) || exit;

$id_base = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );

// Ensure $value is an array of enabled keys.
$enabled = isset( $args['value'] ) && is_array( $args['value'] ) ? array_map( 'sanitize_key', $args['value'] ) : [];

$providers = isset( $args['field']['options'] ) && is_array( $args['field']['options'] ) ? $args['field']['options'] : [];

if ( empty( $providers ) ) {
    return;
}

$rows_html = '';

foreach ( $providers as $key => $meta ) {
    $key = sanitize_key( $key );
    $label = isset( $meta['label'] ) ? $meta['label'] : ucfirst( $key );
    $desc = isset( $meta['desc'] ) ? $meta['desc'] : '';
    $logo_html = isset( $meta['logo'] ) ? $meta['logo'] : '';
    $field_id = $id_base . '_' . $key;
    $is_checked = in_array( $key, $enabled, true ) ? 'checked' : '';

    // One row = logo + title on the left, switch on the right, desc underneath.
    $rows_html .= sprintf(
        '<div class="tp-field tp-social-row" style="display:flex; align-items:center; justify-content:space-between; gap:16px; padding:12px 0; border-bottom:1px solid #eee;">
            <div class="tp-social-left" style="display:flex; align-items:center; gap:12px;">
                <span class="tp-social-logo" style="display:inline-flex; width:24px; height:24px;">%1$s</span>
                <label for="%2$s" class="tp-social-title" style="font-weight:600;">%3$s</label>
            </div>
            <div class="tp-field-input">
                <div class="tp-switch-wrapper">
                    <input class="tp-switch" type="checkbox" id="%2$s" name="%4$s[]" value="%5$s" %6$s />
                    <label for="%2$s" class="tp-switch-label"></label>
                </div>
            </div>
        </div>
        %7$s',
        $logo_html, // %1$s logo HTML (already escaped/controlled below)
        esc_attr( $field_id ), // %2$s input id
        esc_html( $label ), // %3$s provider title
        esc_attr( $id_base ), // %4$s name base (array)
        esc_attr( $key ), // %5$s value
        $is_checked, // %6$s checked attr
        $desc ? '<p class="tp-field-desc" style="margin:6px 0 14px 36px;">' . esc_html( $desc ) . '</p>' : ''
    );
}

// Wrap in a section block with an overall label/desc if provided.
$section_label = isset( $args['field']['label'] ) ? $args['field']['label'] : '';
$section_desc = isset( $args['field']['desc'] ) ? $args['field']['desc'] : '';

printf(
    '<div class="tp-field">
        <div class="tp-field-label"><label>%1$s</label></div>
        <div class="tp-field-input">
            <div class="tp-social-list">%2$s</div>
            %3$s
        </div>
    </div>',
    esc_html( $section_label ),
    wp_kses_post( $rows_html ),
    $section_desc ? '<p class="tp-field-desc">' . esc_html( $section_desc ) . '</p>' : ''
);
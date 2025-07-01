<?php
    /**
     * Output a text input field.
     *
     * @package ThemePaste
     */

    defined( 'ABSPATH' ) || exit;

    $id_name = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
    
    $field_template = '
        <div class="tp-field">
            <div class="tp-field-label">
                <label>%1$s</label>
            </div>
            <div class="tp-field-input">
                <div class="tp-switch-wrapper">
                    <input type="text" id="%2$s" name="%2$s" value="%3$s">
                </div>
                <p class="tp-field-desc">%4$s</p>
            </div>
        </div>';
    $field_template = apply_filters( $id_name, $field_template, $args );

    printf( $field_template,
        esc_html( $args['value']['label'] ),    // %1$s == Label
        $id_name,                               // %2$s == ID & Name
        esc_attr( $args['value']['id'] ),       // %3$s == value
        esc_html( $args['value']['desc'] )      // %4$s == Description
    );
?>


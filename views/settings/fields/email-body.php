<?php 

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

    $id_name    = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
    $value      = isset( $args['value'] ) && ! empty( $args['value'] ) ? $args['value'] : '';

    $value = str_replace('"', "'", $value);
    
    $field_template = '


        <div class="tp-field">
            <div class="tp-field-label">
                <label>%1$s</label>
            </div>
            <div class="tp-field-input">
                <div class="tp-switch-wrapper">
                    <input type="hidden" id="%2$s" name="%2$s" value="%3$s">
                    <div id="tp-email-body"></div>
                </div>
                <p class="tp-field-desc">%4$s</p>
            </div>
        </div>
    ';


    printf( $field_template,
        esc_html( $args['field']['label'] ),    // %1$s == Label
        $id_name,                               // %2$s == ID & Name
        esc_attr( $value ),                     // %3$s == value
        esc_html( $args['field']['desc'] )      // %4$s == Description
    );
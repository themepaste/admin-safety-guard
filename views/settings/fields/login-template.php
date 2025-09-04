<?php 

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

    $id_name    = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
    $value      = isset( $args['value'] ) && ! empty( $args['value'] ) ? $args['value'] : '';
    
    $field_template = '
        <div class="tp-field">
            <div class="tp-field-label">
                <label>%1$s</label>
            </div>
        </div>
        <div style="background-color: #f7f7f7; height: 100px; border: 1px solid #ccc; width: 800px;">
            <input type="hidden" id="%2$s" name="%2$s" value="%3$s">
            <div id="tp-login-template"></div>
        </div>
    ';


    printf( $field_template,
        esc_html( $args['field']['label'] ),    // %1$s == Label
        $id_name,                               // %2$s == ID & Name
        esc_attr( $value ),                     // %3$s == value
    );
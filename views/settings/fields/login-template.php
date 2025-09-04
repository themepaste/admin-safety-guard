<?php 

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

    $id_name    = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
    $value      = isset( $args['value'] ) && ! empty( $args['value'] ) ? $args['value'] : '';
    
    $field_template = '
        <div class="tp-field">
            <input type="hidden" id="%1$s" name="%1$s" value="%2$s">
            <div id="tp-login-template"></div>
        </div>
    ';


    printf( $field_template,
        $id_name,                               // %2$s == ID & Name
        esc_attr( $value ),                     // %3$s == value
    );
<?php
    /**
     * Output a text input field.
     *
     * @package ThemePaste
     */

    defined( 'ABSPATH' ) || exit;

    $id_name    = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
    $value      = isset( $args['value'] ) && ! empty( $args['value'] ) ? $args['value'] : '';
    
    $field_template =   '<div>
                            <input type="hidden" id="%2$s" name="%2$s" value="%3$s">
                        </div>';
    printf( $field_template,
        $id_name,                               // %2$s == ID & Name
        esc_attr( $value ),                     // %3$s == value
    );
?>


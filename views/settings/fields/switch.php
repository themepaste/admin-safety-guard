<?php 

     /**
     * Output a switch / checkbox input field.
     *
     * @package ThemePaste
     */
    defined( 'ABSPATH' ) || exit;

    printf( '
        <div class="tp-field">
            <div class="tp-field-label">
                <label>%1$s</label>
            </div>
            <div class="tp-field-input">
                <div class="tp-switch-wrapper">
                    <input class="tp-switch" type="checkbox" id="%2$s" name="%2$s" value="%3$s" /><label for="%2$s" class="tp-switch-label"></label>
                </div>
                <p class="tp-field-desc">%4$s</p>
            </div>
        </div>',
        esc_html( $args['value']['label'] ),                                                    // %1$s == Label
        esc_attr( $args['prefix'] . '-' . $args['current_screen_slug'] . '_' . $args['key'] ),  // %2$s == ID & Name
        esc_attr( $args['value']['id'] ),                                                       // %3$s == value
        esc_html( $args['value']['desc'] )                                                     // %4$s == Description
    );
?>


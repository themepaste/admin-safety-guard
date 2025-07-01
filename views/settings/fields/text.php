<?php 
    defined( 'ABSPATH' ) || exit;
 
    printf( '<div class="tp-field">
                <div class="tp-field-label">
                    <label>%1$s</label>
                </div>
                <div class="tp-field-input">
                    <div class="tp-switch-wrapper">
                        <input type="text" id="%2$s" name="%2$s" value="%3$s">
                    </div>
                    <p class="tp-field-desc">%4$s</p>
                </div>
            </div>',
            $args['value']['label'],                                                    // %1$s == Label
            $args['prefix'] . '-' . $args['current_screen_slug'] . '_' . $args['key'],  // %2$s == ID & Name
            $args['value']['id'],                                                       // %3$s == value
            $args['value']['desc']                                                      // %4$s == Description
    );
?>


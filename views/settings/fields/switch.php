<?php 
    defined( 'ABSPATH' ) || exit;

    printf( '<div class="tpsa-field">
                <div class="tpsa-field-label">
                    <label>%1$s</label>
                </div>
                <div class="tpsa-field-input">
                    <div class="tpsa-switch-wrapper">
                        <input class="tpsa-switch" type="checkbox" id="%2$s" name="%2$s" value="%3$s" /><label for="tpsa-shipping-fees-disable-enable" class="tpsa-switch-label"></label>
                    </div>
                    <p class="tpsa-field-desc">%4$s</p>
                </div>
            </div>',
            $args['value']['label'],                                                    // %1$s == Label
            $args['prefix'] . '-' . $args['current_screen_slug'] . '_' . $args['key'],  // %2$s == ID & Name
            $args['value']['id'],                                                       // %3$s == value
            $args['value']['desc']                                                      // %4$s == Description
    );
?>


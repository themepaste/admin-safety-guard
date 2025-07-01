<?php 
    defined( 'ABSPATH' ) || exit; 

    $screen_slug     = $args['current_screen'];
    $settings_option = $args['settings_option'];
    $page_label      = $args['page_label'];
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label ); // page_label; ?></h2>
        <form method="POST">
            <?php //wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
    
            <!-- Switch for enable disable  -->
            <div class="tpsa-setting-row">
                <div class="tpsa-field">
                    <div class="tpsa-field-label">
                        <label><?php esc_html_e( 'Disable/Enable:', 'shipping-manager' ); ?></label>
                    </div>
                    <div class="tpsa-field-input">
                        <div class="tpsa-switch-wrapper">
                            <input class="tpsa-switch" type="checkbox" id="tpsa-shipping-fees-disable-enable" name="tpsa-shipping-fees-disable-enable" <?php echo 'checked'; ?> /><label for="tpsa-shipping-fees-disable-enable" class="tpsa-switch-label"></label>
                        </div>
                        <p class="tpsa-field-desc"><?php esc_html_e( 'To enable/disable this feature.', 'shipping-manager' ); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="tpsa-save-button">
                <button type="submit" name="tpsa-shipping-fees_submit"><?php esc_html_e( 'Save Settings', 'shipping-manager' ); ?></button>
            </div>
        </form>
    </div>
</div>
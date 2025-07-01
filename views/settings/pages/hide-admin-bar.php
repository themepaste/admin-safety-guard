<?php 
    defined( 'ABSPATH' ) || exit;
    
    use ThemePaste\SecureAdmin\Helpers\Utility; 

    $prefix          = $args['prefix'];
    $screen_slug     = $args['current_screen'];
    $settings_option = $args['settings_option'];
    $page_label      = $args['page_label'];
    $submit_button   = $prefix . '-' . $screen_slug . '_submit';
    $current_settings_fields = $args['settings_fields'][$screen_slug]['fields'];
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label ); // page_label; ?></h2>
        <form method="POST">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
    
            <!-- Switch for enable disable  -->
            <div class="tpsa-setting-row">
                <?php 
                    if( is_array( $current_settings_fields ) && ! empty( $current_settings_fields ) ) {
                        foreach ( $current_settings_fields as $key => $value ) {
                            $args =[
                                'prefix'=> $args['prefix'],
                                'key'   => $key,
                                'value' => $value,
                                'current_screen_slug' => $screen_slug,
                            ];
                            $field_name = $value['type'];
                            echo Utility::get_template( 'settings/fields/' . $field_name . '.php', $args );
                        }
                    }
                ?>
            </div>
            
            <div class="tpsa-save-button">
                <?php
                    printf( '<button type="submit" name="%1$s">%2$s</button>',
                        $submit_button,
                        esc_html__( 'Save Settings', 'tp-secure-plugin' ) 
                    );
                ?>
            </div>
        </form>
    </div>
</div>
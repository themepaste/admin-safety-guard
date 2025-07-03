<?php 
    defined( 'ABSPATH' ) || exit;
    
    use ThemePaste\SecureAdmin\Helpers\Utility; 

    $prefix          = $args['prefix'];
    $screen_slug     = $args['current_screen'];
    $settings_option = $args['settings_option'];
    $page_label      = $args['page_label'];
    $submit_button   = $prefix . '-' . $screen_slug . '_submit';
    $option_name     = $args['option_name'];
    $saved_settings  = get_option( $option_name, [] );
    $current_settings_fields = $args['settings_fields'][$screen_slug]['fields'] ?? [];
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label ); // page_label; ?></h2>
        
        <div class="tpsa-setting-row">
            <div id="tpsa-analytics-wrapper"></div>
        </div>

    </div>
</div>
<?php 
    defined( 'ABSPATH' ) || exit; 

    $screen_slug     = $args['current_screen'];
    $settings_option = $args['settings_option'];
    $page_label      = $args['page_label'];
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label ); // page_label; ?></h2>
    </div>
</div>
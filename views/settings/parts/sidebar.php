<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly ?>

<!-- Sidebar wrapper for the Shipping Manager settings -->
<div class="tpsa-siderbar-wrapper">
    <ul>
        <?php
// Loop through each settings option to build the sidebar menu
foreach ( $settings_option as $key => $value ) {

    // Determine if this is the currently active setting
    $active_class = ( $current_screen === $key ) ? 'active' : '';

    // Build the URL for each settings tab using add_query_arg
    $setting_url = esc_url( add_query_arg(
        array(
            'page'         => 'tp-admin-safety-guard',
            'tpsa-setting' => $key,
        ),
        admin_url( 'admin.php' )
    ) );

    // Output the menu item with proper escaping and conditional class
    printf(
        '<li><a class="%1$s" href="%2$s">%3$s<sup style="color: green;"> %4$s</sup></a></li>',
        esc_attr( $active_class ),
        $setting_url,
        esc_html( $value['label'] ),
        $value['is_pro'] ? 'pro' : '',
    );
}
?>
    </ul>
</div>
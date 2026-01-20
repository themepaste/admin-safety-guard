<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Import the Utility helper class
use ThemePaste\SecureAdmin\Helpers\Utility;
?>

<!-- Main content wrapper for the Shipping Manager settings page -->
<div class="tpsa-main-wrapper">
    <?php
// Loop through the available settings options
foreach ( $settings_option as $key => $value ) {

    // Check if the current screen matches the key (active tab)
    if ( $current_screen === $key ) {

    if( !empty( $value ) ) {
        $args = [
            'prefix'              => $prefix,
            'key'                 => $key,
            'field'               => $value,
            'value'               => isset( $saved_settings[$key] ) ? $saved_settings[$key] : $value['default'],
            'current_screen_slug' => $screen_slug,
        ];

        // Attempt to retrieve the template for the active settings page
        $template = Utility::get_template( 'settings/pages/' . $key . '.php', $args );
        $pro_template = Utility::get_pro_template( 'settings/pages/' . $key . '.php', $args );

        // If a valid template is returned, output it
        if ( $template ) {
            echo $template;
        } else if ( $pro_template ) {
            echo $pro_template;
        } else {
            ?>
    <?php
}
    }
}
?>
    <?php
echo Utility::get_template( 'settings/parts/rate-us.php' );
?>
</div>
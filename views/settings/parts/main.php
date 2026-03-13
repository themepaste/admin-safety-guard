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

    if ( isset( $value['sub'] ) ) {

        $features = $value['sub'];
        foreach ( $features as $sub_key => $sub_value ) {
            if ( $current_screen == $sub_key ) {
                $template = Utility::get_template( 'settings/pages/' . $sub_key . '.php', $args );
                if ( isset( $template ) && !empty( $template ) && $template ) {
                    echo $template;
                } else {
                    echo Utility::get_template( 'settings/pages/common.php', $args );
                }
            }
        }
    }

    // Check if the current screen matches the key (active tab)
    if ( $current_screen === $key ) {
        echo Utility::get_template( 'settings/pages/' . $key . '.php', $args );
    }
}
?>
    <?php
echo Utility::get_template( 'settings/parts/rate-us.php' );
?>
</div>
<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Import the Utility helper class
use ThemePaste\SecureAdmin\Helpers\Utility;
?>

<!-- Main content wrapper for the Admin Safety Guard settings page -->
<div class="tpsa-main-wrapper">
    <?php

// Loop through the available settings options
foreach ( $settings_option as $key => $value ) {

    if ( isset( $value['sub'] ) ) {

        $features = $value['sub'];
        foreach ( $features as $sub_key => $sub_value ) {
            if ( $current_screen == $sub_key ) {
                // Feature-specific page if one exists, otherwise the generic
                // field-renderer page. Templates escape their own output.
                $template = Utility::get_template( 'settings/pages/' . $sub_key . '.php', $args );
                echo $template ? $template : Utility::get_template( 'settings/pages/common.php', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
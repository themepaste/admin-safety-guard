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

                // Attempt to retrieve the template for the active settings page
                $template       = Utility::get_template( 'settings/pages/' . $key . '.php', $args );
                $pro_template   = Utility::get_pro_template( 'settings/pages/' . $key . '.php', $args );

                // If a valid template is returned, output it
                if ( $template ) {
                    echo $template;
                } else if( $pro_template ) {
                    echo $pro_template;
                } else {
                    ?>
                    <!-- <div style="text-align: center;">
                        <p style="margin-top: 50px; font-size: 16px;">
                            <?php // esc_html_e( 'To Enable this Feature you need to purchase Pro', 'shipping-manager' ); ?>
                        </p>
                        <button style="padding:10px 35px; background:#f25500; color: #fff; border:none; cursor:pointer; border-radius: 4px;">
                            <?php // esc_html_e( 'Upgrade to Pro', 'shipping-manager' ); ?>
                        </button>
                    </div> -->
                    <?php
                }
            }
        }
        echo Utility::get_template( 'settings/parts/rate-us.php' ); 
    ?>
</div>

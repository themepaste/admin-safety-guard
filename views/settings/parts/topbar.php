<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly ?>
<!-- Top bar wrapper for the Shipping Manager plugin -->
<div class="tpsa-plugin-topbar-wrapper">

    <!-- Logo and title area -->
    <div class="tpsa-logo-title-area">

        <!-- Plugin icon -->
        <div class="tpsa-icons">
            <?php
// Set the path for the top bar icon
$tpsm_icon = TPSA_ASSETS_URL . '/admin/img/plugin-icon.png';

// Output the icon with proper escaping
printf( '<img src="%1$s" >', esc_url( $tpsm_icon ) );
?>
        </div>

        <!-- Plugin title and tagline -->
        <div class="tpsa-titles">
            <h1><?php esc_html_e( 'Admin Safety Guard', 'tp-secure-plugin' ); ?></h1>
            <p style="margin:0; color:#814bfe;">
                <?php esc_html_e( 'Shield Your Site with Confidence', 'tp-secure-plugin' ); ?></p>
        </div>
    </div>

    <!-- Right-aligned topbar info area -->
    <div class="tpsa-topbar-info-area">
        <!-- Link to plugin documentation -->
        <a href="https://themepaste.com/product-doc" target="_blank">
            <?php esc_html_e( 'Documentation', 'tp-secure-plugin' ); ?>
        </a>
    </div>
</div>

<!-- Add space below the top bar -->
<br>
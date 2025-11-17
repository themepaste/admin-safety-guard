<?php

defined( 'ABSPATH' ) || exit;
use ThemePaste\SecureAdmin\Helpers\Utility;
?>

<div class="wrap">
    <!-- Empty h1 for showing the notice -->
    <h1></h1>
    <div class="tp-secure-admin_wrapper">
        <?php echo Utility::get_template( 'settings/parts/topbar.php' ); ?>
        <div class="tp-secure-admin_container">
            <!-- Start support from here -->
            <div class="tpsa-support-box" style="background:#fff; padding:20px; border-radius:8px; max-width:600px;">
                <h2><?php esc_html_e( 'Support Request', 'tp-secure-plugin' ); ?></h2>

                <form method="post">
                    <?php wp_nonce_field( 'tpsa_support_form_nonce' ); ?>

                    <p>
                        <label><?php esc_html_e( 'Your Name', 'tp-secure-plugin' ); ?></label><br>
                        <input type="text" name="tpsa_support_name" class="regular-text" required>
                    </p>

                    <p>
                        <label><?php esc_html_e( 'Your Email', 'tp-secure-plugin' ); ?></label><br>
                        <input type="email" name="tpsa_support_email" class="regular-text" required>
                    </p>

                    <p>
                        <label><?php esc_html_e( 'Your Message', 'tp-secure-plugin' ); ?></label><br>
                        <textarea name="tpsa_support_message" rows="6" style="width:100%;" required></textarea>
                    </p>

                    <p>
                        <button type="submit" name="tpsa_support_submit" class="button button-primary">
                            <?php esc_html_e( 'Send Support Request', 'tp-secure-plugin' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php // echo Utility::get_template( 'settings/parts/guide-me.php', $args ); ?>
    </div>
</div>
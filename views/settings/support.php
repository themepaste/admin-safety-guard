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
            <div class="tpsa-support-box tpsa-setting-wrapper"
                style="background:#fff; padding:20px; border-radius:8px; width:100%;">
                <h2><?php esc_html_e( 'Support Request', 'tp-secure-plugin' ); ?></h2>

                <form method="post">
                    <?php wp_nonce_field( 'tpsa_support_form_nonce' ); ?>

                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label><?php esc_html_e( 'Your Name', 'tp-secure-plugin' ); ?> <span
                                    style="color:red;">*</span> </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <input type="text" name="tpsa_support_name" value="" placeholder="Enter you name"
                                    required>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label><?php esc_html_e( 'Your Email', 'tp-secure-plugin' ); ?> <span
                                    style="color:red;">*</span> </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <input type="email" name="tpsa_support_email" value="" placeholder="Enter you Email"
                                    required>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label><?php esc_html_e( 'Your Message', 'tp-secure-plugin' ); ?> <span
                                    style="color:red;">*</span> </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <textarea name="tpsa_support_message" rows="6" style="width:100%;" required></textarea>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <div class="tp-field">
                        <div class="tp-field-label">

                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <div class="tpsa-save-button">
                                    <button
                                        type="submit"><?php esc_html_e( 'Send Support Request', 'tp-secure-plugin' ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php // echo Utility::get_template( 'settings/parts/guide-me.php', $args ); ?>
    </div>
</div>
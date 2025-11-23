<?php

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

// -----------------------------------------------------
// Handle Support Form Submission
// -----------------------------------------------------

$support_notice = '';
$support_notice_class = 'updated';

if (
    'POST' === $_SERVER['REQUEST_METHOD']
    && isset(
        $_POST['tpsa_support_name'],
        $_POST['tpsa_support_email'],
        $_POST['tpsa_support_message'],
        $_POST['tpsa_support_phone']
    )
) {

    // Verify nonce
    if (
        !isset( $_POST['tpsa_support_form_nonce'] )
        || !wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['tpsa_support_form_nonce'] ) ),
            'tpsa_support_form_nonce'
        )
    ) {
        $support_notice = __( 'Security check failed. Please try again.', 'tp-secure-plugin' );
        $support_notice_class = 'error';
    } else {

        // Sanitize fields
        $name = isset( $_POST['tpsa_support_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_support_name'] ) ) : '';
        $email = isset( $_POST['tpsa_support_email'] ) ? sanitize_email( wp_unslash( $_POST['tpsa_support_email'] ) ) : '';
        $message = isset( $_POST['tpsa_support_message'] ) ? wp_kses_post( wp_unslash( $_POST['tpsa_support_message'] ) ) : '';
        $phone = isset( $_POST['tpsa_support_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_support_phone'] ) ) : '';

        if ( empty( $name ) || empty( $email ) || empty( $message ) || empty( $phone ) ) {
            $support_notice = __( 'Please fill all required fields.', 'tp-secure-plugin' );
            $support_notice_class = 'error';
        } elseif ( !is_email( $email ) ) {
            $support_notice = __( 'Please enter a valid email address.', 'tp-secure-plugin' );
            $support_notice_class = 'error';
        } else {

            // -----------------------------------------------------
            // API call to your support site
            // -----------------------------------------------------

            $api_url = 'https://themepaste.com/wp-json/tpsa-support/v1/ticket';

            // This must match the API key configured on your support site plugin
            $api_key = 'tpsa-support-api';

            $response = wp_remote_post(
                $api_url,
                [
                    'timeout' => 20,
                    'headers' => [
                        'tpsaapikey' => $api_key,
                    ],
                    'body'    => [
                        'name'        => $name,
                        'email'       => $email,
                        'phone'       => $phone,
                        'message'     => $message,
                        // Hardcoded plugin name as requested
                        'plugin_name' => 'Admin Safety Guard',
                    ],
                ]
            );

            if ( is_wp_error( $response ) ) {
                $support_notice = sprintf(
                    /* translators: %s: error message */
                    __( 'Could not send support request. Error: %s', 'tp-secure-plugin' ),
                    $response->get_error_message()
                );
                $support_notice_class = 'error';
            } else {
                $code = wp_remote_retrieve_response_code( $response );
                $body = wp_remote_retrieve_body( $response );
                $data = json_decode( $body, true );

                if ( 200 === $code || 201 === $code ) {
                    $support_notice = __( 'Thank you! Your support request has been sent successfully.', 'tp-secure-plugin' );
                    $support_notice_class = 'updated';

                    // Clear fields after success
                    $_POST['tpsa_support_name'] = '';
                    $_POST['tpsa_support_email'] = '';
                    $_POST['tpsa_support_message'] = '';
                    $_POST['tpsa_support_phone'] = '';
                } else {
                    $error_msg = '';
                    if ( is_array( $data ) && isset( $data['message'] ) ) {
                        $error_msg = ' ' . $data['message'];
                    }
                    $support_notice = sprintf(
                        /* translators: %d: status code */
                        __( 'Support request failed (HTTP %d).%s', 'tp-secure-plugin' ),
                        $code,
                        $error_msg
                    );
                    $support_notice_class = 'error';
                }
            }
        }
    }
}

?>

<div class="wrap">
    <!-- Empty h1 for showing the notice -->
    <h1></h1>
    <div class="tp-secure-admin_wrapper">
        <?php echo Utility::get_template( 'settings/parts/topbar.php' ); ?>

        <?php if ( !empty( $support_notice ) ): ?>
        <div class="notice <?php echo esc_attr( $support_notice_class ); ?> is-dismissible">
            <p><?php echo esc_html( $support_notice ); ?></p>
        </div>
        <?php endif; ?>

        <div class="tp-secure-admin_container">
            <!-- Start support from here -->
            <div class="tpsa-support-box tpsa-setting-wrapper"
                style="background:#fff; padding:20px; border-radius:8px; width:100%;">
                <h2><?php esc_html_e( 'Support Request', 'tp-secure-plugin' ); ?></h2>

                <form method="post">
                    <?php wp_nonce_field( 'tpsa_support_form_nonce', 'tpsa_support_form_nonce' ); ?>

                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label>
                                <?php esc_html_e( 'Your Name', 'tp-secure-plugin' ); ?>
                                <span style="color:red;">*</span>
                            </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <input type="text" name="tpsa_support_name"
                                    value="<?php echo isset( $_POST['tpsa_support_name'] ) ? esc_attr( wp_unslash( $_POST['tpsa_support_name'] ) ) : ''; ?>"
                                    placeholder="Enter your name" required>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label>
                                <?php esc_html_e( 'Your Email', 'tp-secure-plugin' ); ?>
                                <span style="color:red;">*</span>
                            </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <input type="email" name="tpsa_support_email"
                                    value="<?php echo isset( $_POST['tpsa_support_email'] ) ? esc_attr( wp_unslash( $_POST['tpsa_support_email'] ) ) : ''; ?>"
                                    placeholder="Enter your Email" required>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <!-- New Phone Number Field -->
                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label>
                                <?php esc_html_e( 'Phone Number (with country code)', 'tp-secure-plugin' ); ?>
                                <span style="color:red;">*</span>
                            </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <input type="text" name="tpsa_support_phone"
                                    value="<?php echo isset( $_POST['tpsa_support_phone'] ) ? esc_attr( wp_unslash( $_POST['tpsa_support_phone'] ) ) : ''; ?>"
                                    placeholder="+8801XXXXXXXXX" required>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <div class="tp-field">
                        <div class="tp-field-label">
                            <label>
                                <?php esc_html_e( 'Your Message', 'tp-secure-plugin' ); ?>
                                <span style="color:red;">*</span>
                            </label>
                        </div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <textarea name="tpsa_support_message" rows="6" style="width:100%;" required><?php
echo isset( $_POST['tpsa_support_message'] ) ? esc_textarea( wp_unslash( $_POST['tpsa_support_message'] ) ) : '';
?></textarea>
                            </div>
                            <p class="tp-field-desc"></p>
                        </div>
                    </div>

                    <div class="tp-field">
                        <div class="tp-field-label"></div>
                        <div class="tp-field-input">
                            <div class="tp-switch-wrapper">
                                <div class="tpsa-save-button">
                                    <button type="submit">
                                        <?php esc_html_e( 'Send Support Request', 'tp-secure-plugin' ); ?>
                                    </button>
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
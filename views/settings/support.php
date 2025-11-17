<?php
// Check submit
if ( isset( $_POST['tpsa_support_submit'] ) ) {

    check_admin_referer( 'tpsa_support_form_nonce' );

    $name = isset( $_POST['tpsa_support_name'] ) ? sanitize_text_field( $_POST['tpsa_support_name'] ) : '';
    $email = isset( $_POST['tpsa_support_email'] ) ? sanitize_email( $_POST['tpsa_support_email'] ) : '';
    $message = isset( $_POST['tpsa_support_message'] ) ? sanitize_textarea_field( $_POST['tpsa_support_message'] ) : '';

    if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'All fields are required.', 'tp-secure-plugin' ) . '</p></div>';
    } elseif ( !is_email( $email ) ) {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'Please enter a valid email address.', 'tp-secure-plugin' ) . '</p></div>';
    } else {
        // Send email
        $to = get_option( 'admin_email' );
        $subject = 'Support Request from ' . $name;
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

        wp_mail( $to, $subject, $body );

        echo '<div class="notice notice-success"><p>' . esc_html__( 'Your support request has been sent successfully!', 'tp-secure-plugin' ) . '</p></div>';
    }
}
?>

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
            <label><?php esc_html_e( 'Full Details', 'tp-secure-plugin' ); ?></label><br>
            <textarea name="tpsa_support_message" rows="6" style="width:100%;" required></textarea>
        </p>

        <p>
            <button type="submit" name="tpsa_support_submit" class="button button-primary">
                <?php esc_html_e( 'Send Support Request', 'tp-secure-plugin' ); ?>
            </button>
        </p>
    </form>
</div>
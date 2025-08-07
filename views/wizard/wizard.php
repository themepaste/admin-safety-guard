<?php

defined( 'ABSPATH' ) || exit;

?>

<div class="tpsm-wizard-wrapper">
    <div class="tpsm-wizard-container">
        
        <div class="tpsm-wizard-logo">
            <img src="<?php echo esc_url( TPSA_ASSETS_URL . '/admin/img/plugin-icon.png' ); ?>" alt="Shipping Manager">
        </div>
        <h3>Never miss an important update</h3>
        <p>By opting in, you’ll get notifications about important security patches, new features, helpful tips, and occasional special offers. We’ll also collect a bit of basic information about your WordPress setup. This helps us improve compatibility with your site and ensures the plugin works more effectively for your needs.</p>
        <form action="" method="post">
            <?php wp_nonce_field( 'tpsm-nonce_action', 'tpsm-nonce_name' ); ?>
            <input type="hidden" name="tpsm_optin_submit" value="1">
            <button type="submit" name="tpsm_optin_choice" value="0" class="button button-secondary tpsm-optin-deny">
                <?php esc_html_e( 'Not now', 'shipping-manager' ); ?>
            </button>
            
            <button type="submit" name="tpsm_optin_choice" value="1" class="active button button-primary tpsm-optin-allow">
                <?php esc_html_e( 'Allow & Continue', 'shipping-manager' ); ?>
            </button>
        </form>
    </div>
</div>

<?php 
    if ( ! isset( $_POST['tpsm_optin_submit'] ) ) {
        return;
    }

    if ( ! isset( $_POST['tpsm-nonce_name'] ) || ! wp_verify_nonce( $_POST['tpsm-nonce_name'], 'tpsm-nonce_action' ) ) {
        wp_die( esc_html__( 'Nonce verification failed.', 'shipping-manager' ) );
    }

    // Check capabilities if needed
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized user', 'shipping-manager' ) );
    }

    // Sanitize the choice
    $choice = isset( $_POST['tpsm_optin_choice'] ) ? sanitize_text_field( $_POST['tpsm_optin_choice'] ) : '0';

    // Convert to int and sanitize
    $value = (int) $choice === 1 ? 1 : 0;

    // Save the option
    update_option( 'tpsm_is_setup_wizard', $value );

    // Save remote data if enabled
    if( $value === 1 ) {
        tpsm_saved_remote_data();
    }

    wp_redirect( add_query_arg( 
        array(
            'page'     => 'wc-settings',
            'tab'      => 'shipping',
            'section'  => 'shipping-manager',
        ),
        admin_url( 'admin.php' )
    ) );
    exit;
?>
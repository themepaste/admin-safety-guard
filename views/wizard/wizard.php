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
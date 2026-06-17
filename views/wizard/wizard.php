<?php

defined( 'ABSPATH' ) || exit;

?>

<div class="tpsm-wizard-wrapper">
    <div class="tpsm-wizard-container">

        <div class="tpsm-wizard-logo">
            <img src="<?php echo esc_url( TPSA_ASSETS_URL . '/admin/img/plugin-icon.png' ); ?>" alt="<?php esc_attr_e( 'Admin Safety Guard', 'admin-safety-guard' ); ?>">
        </div>
        <h3><?php esc_html_e( 'Stay protected & up to date', 'admin-safety-guard' ); ?></h3>
        <p><?php esc_html_e( 'Get security patches, new features and helpful tips — straight to your inbox.', 'admin-safety-guard' ); ?></p>

        <div class="tpsm-wizard-consent">
            <?php
            printf(
                /* translators: 1: opening privacy-policy link tag, 2: closing link tag */
                esc_html__( 'We share your name, email and site URL with ThemePaste. No spam, opt out anytime. %1$sPrivacy Policy%2$s', 'admin-safety-guard' ),
                '<a href="' . esc_url( 'https://themepaste.com/privacy-policy' ) . '" target="_blank" rel="noopener noreferrer">',
                '</a>'
            );
            ?>
        </div>

        <form action="" method="post">
            <?php wp_nonce_field( 'tpsm-nonce_action', 'tpsm-nonce_name' ); ?>
            <input type="hidden" name="tpsm_optin_submit" value="1">
            <button type="submit" name="tpsm_optin_choice" value="0" class="button button-secondary tpsm-optin-deny">
                <?php esc_html_e( 'Not now', 'admin-safety-guard' ); ?>
            </button>

            <button type="submit" name="tpsm_optin_choice" value="1" class="active button button-primary tpsm-optin-allow">
                <?php esc_html_e( 'Allow & Continue', 'admin-safety-guard' ); ?>
            </button>
        </form>
    </div>
</div>
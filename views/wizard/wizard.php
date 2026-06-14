<?php

defined( 'ABSPATH' ) || exit;

?>

<div class="tpsm-wizard-wrapper">
    <div class="tpsm-wizard-container">

        <div class="tpsm-wizard-logo">
            <img src="<?php echo esc_url( TPSA_ASSETS_URL . '/admin/img/plugin-icon.png' ); ?>" alt="<?php esc_attr_e( 'Admin Safety Guard', 'admin-safety-guard' ); ?>">
        </div>
        <h3><?php esc_html_e( 'Never miss an important update', 'admin-safety-guard' ); ?></h3>
        <p><?php esc_html_e( 'By opting in, you’ll get notifications about important security patches, new features, helpful tips, and occasional special offers.', 'admin-safety-guard' ); ?></p>

        <div class="tpsm-wizard-consent" style="text-align:left; background:#f6f7fb; border:1px solid #e3e6ef; border-radius:8px; padding:14px 16px; margin:16px 0; font-size:13px; line-height:1.6; color:#4b5563;">
            <strong><?php esc_html_e( 'What we collect if you allow this:', 'admin-safety-guard' ); ?></strong>
            <ul style="margin:8px 0 0; padding-left:18px;">
                <li><?php esc_html_e( 'Your administrator name and email address', 'admin-safety-guard' ); ?></li>
                <li><?php esc_html_e( 'Your site URL', 'admin-safety-guard' ); ?></li>
            </ul>
            <p style="margin:10px 0 0;">
                <?php
                printf(
                    /* translators: 1: company name, 2: opening privacy-policy link tag, 3: closing link tag */
                    esc_html__( 'This information is sent to %1$s so we can send you the updates above. We never sell your data, and you can opt out at any time. See our %2$sPrivacy Policy%3$s for details.', 'admin-safety-guard' ),
                    'ThemePaste (themepaste.com)',
                    '<a href="' . esc_url( 'https://themepaste.com/privacy-policy' ) . '" target="_blank" rel="noopener noreferrer">',
                    '</a>'
                );
                ?>
            </p>
            <p style="margin:10px 0 0;">
                <?php esc_html_e( 'Choosing “Not now” keeps the plugin fully functional — no data is sent.', 'admin-safety-guard' ); ?>
            </p>
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
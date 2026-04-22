<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$screen_slug        = $args['current_screen'];
$page_label         = isset( $args['page_label_forsub'] ) && !empty( $args['page_label_forsub'] ) ? $args['page_label_forsub'] : $args['page_label'];
$current_url        = $args['current_url'];
$current_tab_label  = $args['current_tab_label'];
$is_pro             = $args['is_pro'] ?? false;
$is_valid_license_available = is_valid_license_available();
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><span><a href="<?php echo esc_url( $current_url ); ?>"><?php echo esc_html( $current_tab_label ); ?></a>
                <?php echo esc_html( ' / ' . $page_label . ' Settings' ); ?>
            </span>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p><?php esc_html_e( 'Configure OAuth credentials for each social provider. Providers become active once valid credentials are saved.', 'admin-safety-guard' ); ?>
                    </p>
                </div>
            </div>
        </h2>
    </div>
</div>

<!-- Social Login Credentials (view in free plugin; popup gates access when license is invalid) -->
<?php echo Utility::get_template( 'settings/pages/social-credentials.php', [ 'is_valid_license_available' => $is_valid_license_available ] ); ?>

<!-- PRO POPUP OVERLAY -->
<?php echo $is_pro && !$is_valid_license_available ? Utility::get_template( 'popup/pro-features-popup.php' ) : ''; ?>
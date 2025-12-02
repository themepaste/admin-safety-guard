<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$prefix = $args['prefix'];
$screen_slug = $args['current_screen'];
$settings_option = $args['settings_option'];
$page_label = $args['page_label'];
$submit_button = $prefix . '-' . $screen_slug . '_submit';
$option_name = $args['option_name'];
$saved_settings = get_option( $option_name, [] );
$current_settings_fields = $args['settings_fields'][$screen_slug]['fields'] ?? [];
$is_pro = true;
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label . ' ' . 'Settings' ); // page_label;        ?>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p>This feature integrates visual challenges into your login form, preventing bot access and
                        ensuring only legitimate users gain entry.</p>
                </div>
            </div>
        </h2>
        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
            <input type="hidden" name="action" value="tpsa_process_form">
            <input type="hidden" name="screen_slug" value="<?php echo esc_attr( $screen_slug ); ?>">

            <!-- Switch for enable disable  -->
            <div class="tpsa-setting-row">
                <?php
if ( is_array( $current_settings_fields ) && !empty( $current_settings_fields ) ) {
    foreach ( $current_settings_fields as $key => $field ) {
        $args = [
            'prefix'              => $args['prefix'],
            'key'                 => $key,
            'field'               => $field,
            'value'               => isset( $saved_settings[$key] ) ? $saved_settings[$key] : $field['default'],
            'current_screen_slug' => $screen_slug,
        ];
        $field_name = $field['type'];
        echo Utility::get_template( 'settings/fields/' . $field_name . '.php', $args );
    }
}
?>
            </div>

            <div class="tpsa-save-button">
                <?php
printf( '<button type="submit">%1$s</button>',
    esc_html__( 'Save Settings', 'tp-secure-plugin' )
);
?>
            </div>
        </form>
    </div>
</div>


<!-- PRO POPUP OVERLAY -->
<div class="tpsa-pro-popup-overlay" id="tpsaProPopup">
    <div class="tpsa-pro-popup-box">
        <div class="tpsa-pro-popup-header">
            <h2>Premium Feature</h2>
            <button class="tpsa-pro-close">&times;</button>
        </div>

        <div class="tpsa-pro-popup-body">
            <p>This feature is available only in the <strong>Pro version</strong>.
                Please upgrade to unlock all premium protections and advanced features.</p>

            <div class="tpsa-pro-icon-box">
                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828640.png" alt="Pro Icon">
            </div>

            <div class="tpsa-pro-buttons">
                <a href="https://themepaste.com/" target="_blank" class="tpsa-pro-btn purchase-btn">
                    Purchase Pro
                </a>

                <a href="#" id="openLicenseBox" class="tpsa-pro-btn license-btn">
                    Activate License
                </a>
            </div>

            <div class="tpsa-pro-small-link">
                <a href="#" id="alreadyPurchased">Already Purchased?</a>
            </div>
        </div>
    </div>
</div>
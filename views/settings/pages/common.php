<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$screen_slug = $args['current_screen'];
$current_settings_fields = $args['settings_fields'][$screen_slug]['fields'] ?? [];
$saved_settings = get_option( $args['option_name'], [] );
$page_label = isset( $args['page_label_forsub'] ) && !empty( $args['page_label_forsub'] ) ? $args['page_label_forsub'] : $args['page_label'];
$is_pro = $args['is_pro'] ?? false;
$is_valid_license_available = is_valid_license_available();
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <!-- START HEADING AND PAGINATION  -->
        <h2><span><a
                    href="<?php echo esc_url( $args['current_url'] ); ?>"><?php echo esc_html( $args['current_tab_label'] ); ?></a>
                <?php echo esc_html( ' / ' . $page_label . ' Settings' ); ?>
            </span>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p><?php esc_html_e( 'This feature guards your site from brute-force attacks by restricting failed login attempts and
                        automatically locking out repeated offenders.', 'admin-safety-guard' ); ?></p>
                </div>
            </div>
        </h2>
        <!-- END HEADING AND PAGINATION  -->

        <!-- START SETTINGS FORM -->
        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
            <input type="hidden" name="action" value="tpsa_process_form">
            <input type="hidden" name="screen_tab" value="<?php echo esc_attr( $args['current_tab'] ); ?>">
            <input type="hidden" name="screen_slug" value="<?php echo esc_attr( $screen_slug ); ?>">

            <!-- RENDER ALL FIELDS -->
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
    esc_html__( 'Save Settings', 'admin-safety-guard' )
);
?>
            </div>
        </form>

        <!-- END SETTINGS FORM -->
    </div>
</div>


<!-- PRO POPUP OVERLAY -->
<?php echo $is_pro && !$is_valid_license_available ? Utility::get_template( 'popup/pro-features-popup.php' ) : ''; ?>
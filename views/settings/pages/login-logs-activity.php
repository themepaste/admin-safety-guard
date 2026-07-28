<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$screen_slug             = $args['current_screen'];
$current_settings_fields = $args['settings_fields'][$screen_slug]['fields'] ?? [];
$saved_settings          = get_option( $args['option_name'], [] );
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">

        <!-- Monitoring options: kept above the log so the alerting toggle is
             discoverable rather than hidden on another screen. -->
        <?php if ( !empty( $current_settings_fields ) ) : ?>
        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
            class="tpsa-monitor-settings">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
            <input type="hidden" name="action" value="tpsa_process_form">
            <input type="hidden" name="screen_tab" value="<?php echo esc_attr( $args['current_tab'] ); ?>">
            <input type="hidden" name="screen_slug" value="<?php echo esc_attr( $screen_slug ); ?>">

            <div class="tpsa-setting-row">
                <?php
foreach ( $current_settings_fields as $key => $field ) {
    $field_args = [
        'prefix'              => $args['prefix'],
        'key'                 => $key,
        'field'               => $field,
        'value'               => $saved_settings[$key] ?? $field['default'],
        'current_screen_slug' => $screen_slug,
    ];

    echo Utility::get_template( 'settings/fields/' . sanitize_key( $field['type'] ) . '.php', $field_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Field templates escape their own output.
}
?>
            </div>

            <div class="tpsa-save-button">
                <button type="submit"><?php esc_html_e( 'Save Settings', 'admin-safety-guard' ); ?></button>
            </div>
        </form>
        <?php endif; ?>

        <div class="tpsa-setting-row">
            <div id="tpsa-login-log-activity"></div>
        </div>

    </div>
</div>

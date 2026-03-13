<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

// $prefix = $args['prefix'];
// $screen_slug = $args['current_screen'];
// $settings_option = $args['settings_option'];
// $page_label = $args['page_label'];
// $submit_button = $prefix . '-' . $screen_slug . '_submit';
// $option_name = $args['option_name'];
// $saved_settings = get_option( $option_name, [] );
// $current_settings_fields = $args['settings_fields'][$screen_slug]['fields'] ?? [];
// $current_tab = $args['current_tab'];
// $current_url = $args['current_url'];
// $current_tab_label = $args['current_tab_label'];

echo "<pre>";
print_r( $args );
echo "</pre>";
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <!-- START HEADING AND PAGINATION  -->
        <h2><span><a
                    href="<?php echo esc_url( $args['current_url'] ); ?>"><?php echo esc_html( $args['current_tab_label'] ); ?></a>
                <?php echo esc_html( ' / ' . $args['page_label'] . ' Settings' ); ?>
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
        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
            <input type="hidden" name="action" value="tpsa_process_form">
            <input type="hidden" name="screen_tab" value="<?php echo esc_attr( $current_tab ); ?>">
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
    esc_html__( 'Save Settings', 'admin-safety-guard' )
);
?>
            </div>
        </form>
    </div>
</div>
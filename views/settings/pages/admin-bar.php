<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$screen_slug            = $args['current_screen'];
$current_settings_fields = $args['settings_fields'][$screen_slug]['fields'] ?? [];
$saved_settings         = get_option( $args['option_name'], [] );
$page_label             = isset( $args['page_label_forsub'] ) && !empty( $args['page_label_forsub'] )
    ? $args['page_label_forsub']
    : $args['page_label'];
$is_valid_license       = is_valid_license_available();
$is_pro_active          = tp_is_pro_active();
$upgrade_url            = 'https://themepaste.com/product/admin-safety-guard-pro#pricePlanSection';
$license_url            = add_query_arg( ['page' => 'tp-admin-safety-guard-pro'], admin_url( 'admin.php' ) );
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">

        <h2><span>
            <a href="<?php echo esc_url( $args['current_url'] ); ?>"><?php echo esc_html( $args['current_tab_label'] ); ?></a>
            <?php echo esc_html( ' / ' . $page_label . ' Settings' ); ?>
        </span>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p><?php esc_html_e( 'Hide the WordPress admin bar for selected user roles and apply advanced node-level rules to remove specific items.', 'admin-safety-guard' ); ?></p>
                </div>
            </div>
        </h2>

        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
            <input type="hidden" name="action" value="tpsa_process_form">
            <input type="hidden" name="screen_tab" value="<?php echo esc_attr( $args['current_tab'] ); ?>">
            <input type="hidden" name="screen_slug" value="<?php echo esc_attr( $screen_slug ); ?>">

            <!-- Free fields (enable + exclude roles) -->
            <div class="tpsa-setting-row">
                <?php
                if ( is_array( $current_settings_fields ) && !empty( $current_settings_fields ) ) {
                    foreach ( $current_settings_fields as $key => $field ) {
                        $field_args = [
                            'prefix'              => $args['prefix'],
                            'key'                 => $key,
                            'field'               => $field,
                            'value'               => $saved_settings[$key] ?? $field['default'],
                            'current_screen_slug' => $screen_slug,
                        ];
                        echo Utility::get_template( 'settings/fields/' . $field['type'] . '.php', $field_args );
                    }
                }
                ?>
            </div>

            <!-- Pro: Advanced Admin Bar Rules -->
            <?php if ( $is_pro_active && $is_valid_license ) : ?>
                <?php do_action( 'tpsa_admin_bar_after_settings', $screen_slug ); ?>
            <?php else : ?>
                <div style="margin-top:28px;border:1px dashed #814bfe;border-radius:10px;padding:24px;background:#faf8ff;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                        <h3 style="margin:0;font-size:15px;color:#1d2327;">
                            <?php esc_html_e( 'Advanced Admin Bar Rules', 'admin-safety-guard' ); ?>
                        </h3>
                        <span style="background:#814bfe;color:#fff;font-size:11px;font-weight:700;
                                     padding:2px 8px;border-radius:20px;letter-spacing:.5px;text-transform:uppercase;">
                            <?php esc_html_e( 'Pro', 'admin-safety-guard' ); ?>
                        </span>
                    </div>
                    <p style="color:#5c5c7b;font-size:13px;margin:0 0 18px;">
                        <?php esc_html_e( 'Define granular rules to hide specific admin bar nodes by ID, title, parent, or URL — applied separately on wp-admin and the front-end.', 'admin-safety-guard' ); ?>
                    </p>
                    <!-- Blurred preview of the pro fields -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;
                                opacity:.35;pointer-events:none;filter:blur(1px);">
                        <?php
                        $preview_fields = [
                            __( 'Exact Node IDs', 'admin-safety-guard' ),
                            __( 'ID Prefix', 'admin-safety-guard' ),
                            __( 'Title Contains', 'admin-safety-guard' ),
                            __( 'Href Contains', 'admin-safety-guard' ),
                        ];
                        foreach ( $preview_fields as $label ) :
                        ?>
                        <div style="background:#fff;border:1px solid #e2d8fb;border-radius:6px;padding:12px;">
                            <strong style="font-size:12px;color:#1d2327;display:block;margin-bottom:6px;"><?php echo esc_html( $label ); ?></strong>
                            <div style="height:44px;background:#f1f5f9;border-radius:4px;"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer"
                           style="display:inline-block;background:#814bfe;color:#fff;font-size:13px;font-weight:600;
                                  padding:9px 22px;border-radius:6px;text-decoration:none;">
                            <?php esc_html_e( 'Purchase Pro', 'admin-safety-guard' ); ?>
                        </a>
                        <?php if ( $is_pro_active ) : ?>
                        <a href="<?php echo esc_url( $license_url ); ?>"
                           style="display:inline-block;background:#f1eef9;color:#814bfe;font-size:13px;font-weight:600;
                                  padding:9px 22px;border-radius:6px;text-decoration:none;border:1px solid #814bfe;">
                            <?php esc_html_e( 'Activate License', 'admin-safety-guard' ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tpsa-save-button" style="margin-top:24px;">
                <button type="submit"><?php esc_html_e( 'Save Settings', 'admin-safety-guard' ); ?></button>
            </div>
        </form>

    </div>
</div>

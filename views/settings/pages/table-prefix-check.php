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
$db_prefix = tp_asg_pro_current_prefix();
$is_pro = $settings_option[$screen_slug]['is_pro'] ?? false;

$suggestions = [
    tp_asg_pro_random_prefix( 5 ),
    tp_asg_pro_random_prefix( 6 ),
    tp_asg_pro_random_prefix( 4 ),
    'asgpro_',
];
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label . ' Settings' ); // page_label;       ?>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p><?php esc_html_e( 'This feature allows you to enable or disable social login options for your website. You can select which social networks you want to allow users to log in with.', 'tp-secure-plugin' ); ?>
                    </p>
                </div>
            </div>
        </h2>

        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
            <input type="hidden" name="action" value="tpsa_process_form">
            <input type="hidden" name="screen_slug" value="<?php echo esc_attr( $screen_slug ); ?>">

            <p><strong>Current Prefix:</strong> <code><?php echo esc_html( $db_prefix ); ?></code><sub
                    class="tpsa-prefix-status <?php echo tp_asg_pro_is_prefix_good( $db_prefix ) ? 'green' : 'red'; ?>"><?php echo tp_asg_pro_is_prefix_good( $db_prefix ) ? 'good' : 'bad'; ?></sub>
            </p>
            <h3>Recommended Secure Prefix Styles</h3>
            <p>Use 4–6 random characters ending with an underscore. Avoid real words like <code>secure_</code> or
                <code>admin_</code>.
            </p>

            <p>Suggestions:
                <?php foreach ( $suggestions as $s ): ?>
                <code style="margin-right:8px;"><?php echo esc_html( $s ); ?></code>
                <?php endforeach; ?>
            </p>

            <br>
            <hr>


            <h3>Execute Prefix Change (Danger Zone)</h3>
            <h4>Make a DB + file backup first (recommended)</h4>

            <!-- Switch for enable disable  -->
            <div class="tpsa-setting-row">
                <div class="tp-field">
                    <div class="tp-field-label">
                        <label>New Prefix</label>
                    </div>
                    <div class="tp-field-input">
                        <div class="tp-switch-wrapper">
                            <input type="text" id="tpsa_table-prefix-check_new-prefix"
                                name="tpsa_table-prefix-check_new-prefix">
                        </div>
                        <p class="tp-field-desc">Make a full backup first. The plugin will try to update wp-config.php
                            and references in options/usermeta</p>
                    </div>
                </div>
                <div class="tp-field">
                    <div class="tp-field-label">
                        <label>Type "I UNDERSTAND"</label>
                    </div>
                    <div class="tp-field-input">
                        <div class="tp-switch-wrapper">
                            <input type="text" id="tpsa_table-prefix-check_i-understand"
                                name="tpsa_table-prefix-check_i-understand">
                        </div>
                        <p class="tp-field-desc">Type "I UNDERSTAND" in this field</p>
                    </div>
                </div>
            </div>

            <div class="tpsa-save-button">
                <?php
printf( '<button type="button" id="tpsa_table-prefix-check_submit">%1$s</button>',
    esc_html__( 'Change Prefix', 'tp-secure-plugin' )
);
?>
            </div>
        </form>
    </div>
</div>


<!-- PRO POPUP OVERLAY -->
<?php echo $is_pro ? Utility::get_template( 'popup/pro-features-popup.php' ) : ''; ?>
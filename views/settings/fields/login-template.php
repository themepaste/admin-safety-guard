<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$id_name         = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
$value           = isset( $args['value'] ) && !empty( $args['value'] ) ? $args['value'] : '';
$value           = str_replace( '"', "'", $value );
$is_pro_licensed = is_valid_license_available() && tp_is_pro_active();
$upgrade_url     = 'https://themepaste.com/product/admin-safety-guard-pro#pricePlanSection';
$license_url     = add_query_arg( ['page' => 'tp-admin-safety-guard-pro'], admin_url( 'admin.php' ) );

if ( $is_pro_licensed ) :
    printf(
        '<div class="tp-field"><input type="hidden" id="%1$s" name="%1$s" value="%2$s"><div id="tpsa-login-template"></div></div>',
        $id_name,
        esc_attr( $value )
    );
else :
?>
<div class="tp-field">
    <div style="border:1px dashed #814bfe;border-radius:10px;padding:28px 24px;background:#faf8ff;text-align:center;">

        <div style="margin-bottom:16px;">
            <span style="display:inline-block;background:#814bfe;color:#fff;font-size:11px;font-weight:700;
                         padding:3px 10px;border-radius:20px;letter-spacing:.5px;text-transform:uppercase;">
                <?php esc_html_e( 'Pro Feature', 'admin-safety-guard' ); ?>
            </span>
        </div>

        <h3 style="margin:0 0 8px;font-size:16px;color:#1d2327;">
            <?php esc_html_e( 'Login Template Picker', 'admin-safety-guard' ); ?>
        </h3>
        <p style="color:#5c5c7b;font-size:13px;margin:0 0 20px;max-width:520px;margin-inline:auto;">
            <?php esc_html_e( 'Choose from ready-made login page designs or build a fully custom layout with your own colours, fonts, background, and logo. Templates are applied instantly on the login screen.', 'admin-safety-guard' ); ?>
        </p>

        <!-- Preview mockup -->
        <div style="display:flex;gap:12px;justify-content:center;margin-bottom:24px;flex-wrap:wrap;">
            <?php foreach ( ['#e2d8fb', '#d8f0fb', '#fbd8d8', '#d8fbdf'] as $color ) : ?>
            <div style="width:100px;height:70px;border-radius:8px;background:<?php echo esc_attr( $color ); ?>;
                         border:2px solid rgba(0,0,0,.07);display:flex;align-items:center;justify-content:center;">
                <div style="width:40px;height:28px;background:rgba(255,255,255,.7);border-radius:4px;"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer"
               style="display:inline-block;background:#814bfe;color:#fff;font-size:13px;font-weight:600;
                      padding:9px 22px;border-radius:6px;text-decoration:none;">
                <?php esc_html_e( 'Purchase Pro', 'admin-safety-guard' ); ?>
            </a>
            <?php if ( tp_is_pro_active() ) : ?>
            <a href="<?php echo esc_url( $license_url ); ?>"
               style="display:inline-block;background:#f1eef9;color:#814bfe;font-size:13px;font-weight:600;
                      padding:9px 22px;border-radius:6px;text-decoration:none;border:1px solid #814bfe;">
                <?php esc_html_e( 'Activate License', 'admin-safety-guard' ); ?>
            </a>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php endif; ?>

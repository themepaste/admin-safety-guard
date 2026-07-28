<?php

defined( 'ABSPATH' ) || exit;
use ThemePaste\SecureAdmin\Helpers\Utility;

$settings_option = tpsa_settings_option();

$settings_fields = tpsa_settings_fields();
$current_screen = Utility::get_screen( 'tpsa-setting' );
$current_tab = Utility::get_screen( 'tab' );
$current_tab_label = $settings_option[$current_tab]['label'] ?? '';
$current_url = add_query_arg(
    array(
        'page'         => 'tp-admin-safety-guard',
        'tab'          => $current_tab,
        'tpsa-setting' => $current_tab,
    ),
    admin_url( 'admin.php' )
);

$args = array(
    'settings_option'   => $settings_option,
    'settings_fields'   => $settings_fields,
    'current_screen'    => $current_screen,
    'current_tab'       => $current_tab,
    'current_url'       => $current_url,
    'prefix'            => get_tpsa_prefix(),
    'option_name'       => get_tpsa_settings_option_name( $current_screen ),
    'page_label'        => $settings_option[$current_screen]['label'] ?? '',
    'page_label_forsub' => $settings_option[$current_tab]['sub'][$current_screen]['label'] ?? '',
    'current_tab_label' => $current_tab_label,
    'is_pro'            => $settings_option[$current_tab]['sub'][$current_screen]['is_pro'] ?? false,
);
?>



<div class="wrap">
    <!-- Empty h1 for showing the notice -->
    <h1></h1>
    <div class="tp-secure-admin_wrapper">
        <?php echo Utility::get_template( 'settings/parts/topbar.php' ); ?>
        <div class="tp-secure-admin_container">
            <?php echo Utility::get_template( 'settings/parts/sidebar.php', $args ); ?>
            <?php echo Utility::get_template( 'settings/parts/main.php', $args ); ?>
        </div>
        <?php // echo Utility::get_template( 'settings/parts/guide-me.php', $args ); ?>
    </div>
</div>
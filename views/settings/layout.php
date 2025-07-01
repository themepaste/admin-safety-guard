<?php

defined( 'ABSPATH' ) || exit;
use ThemePaste\SecureAdmin\Helpers\Utility;

$settings_option    = tpsa_settings_option();
$settings_fields    = tpsa_settings_fields();
$current_screen     = Utility::get_screen( 'tpsa-setting' );

$args = array(
    'settings_option' => $settings_option,
    'settings_fields' => $settings_fields,
    'current_screen'  => $current_screen,
    'prefix'          => 'tpsa',
    'page_label'      => $settings_option[$current_screen]['label'],
);
?>

<div class="wrap">
    <div class="tp-secure-admin_wrapper">
        <?php echo Utility::get_template( 'settings/parts/topbar.php' ); ?>
        <div class="tp-secure-admin_container">
            <?php echo Utility::get_template( 'settings/parts/sidebar.php', $args ); ?>
            <?php echo Utility::get_template( 'settings/parts/main.php', $args ); ?>
        </div>
    </div>
</div>
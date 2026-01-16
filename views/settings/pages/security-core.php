<?php
defined( 'ABSPATH' ) || exit;

$page_label = $args['page_label'];
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">
        <h2><?php echo esc_html( $page_label ); // page_label;       ?></h2>

        <div class="tpsa-setting-row">
            <div id="tpsa-security-core-wrapper"></div>
        </div>

    </div>
</div>
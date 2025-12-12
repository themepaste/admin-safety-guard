<?php defined( 'ABSPATH' ) || exit;

$license_url = add_query_arg(
    array(
        'page' => 'tp-admin-safety-guard-pro',
    ),
    admin_url( 'admin.php' )
);

?>

<div class="tpsa-pro-popup-overlay" id="tpsaProPopup">
    <div class="tpsa-pro-popup-box">
        <div class="tpsa-pro-popup-header">
            <h2>Premium Feature</h2>
        </div>

        <div class="tpsa-pro-popup-body">
            <p>This feature is available only in the <strong>Pro version</strong>.
                Please upgrade to unlock all premium protections and advanced features.</p>

            <div class="tpsa-pro-icon-box">
                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828640.png" alt="Pro Icon">
            </div>

            <div class="tpsa-pro-buttons">
                <a href="https://themepaste.com/product/admin-safety-guard-pro#pricePlanSection" target="_blank"
                    class="tpsa-pro-btn purchase-btn">
                    Purchase Pro
                </a>

                <a href="<?php echo esc_url( $license_url ); ?>" id="openLicenseBox" class="tpsa-pro-btn license-btn">
                    Activate License
                </a>
            </div>

            <div class="tpsa-pro-small-link">
                <a href="<?php echo esc_url( $license_url ); ?>" id="alreadyPurchased">Already Purchased?</a>
            </div>
        </div>
    </div>
</div>
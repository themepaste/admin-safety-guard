<?php

namespace ThemePaste\SecureAdmin\Classes\Pro;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class ProFeaturesSettings implements FeatureInterface {

    use Hook;

    public function register_hooks() {
        // Add the Pro sub-tab (Web Application Firewall) under the
        // "Firewall & Malware" section. The field definitions for that tab are
        // registered by Pro\AdvancedFirewall (and by the pro plugin when
        // active), so this class only extends the settings option (tab) list.
        //
        // Malware scanning is intentionally NOT listed here: it is provided by
        // the standalone Deep Malware Cleaner plugin rather than duplicated in
        // this one.
        $this->filter( 'tpsa_settings_option', [$this, 'extend_pro_settings'] );
    }

    public function extend_pro_settings( $settings ) {
        $settings['firewall-malware']['sub'] = [
            'web-application-firewall' => array(
                'label'  => __( 'Web Application Firewall', 'admin-safety-guard' ),
                'is_pro' => true,
            ),
        ];
        return $settings;
    }

}
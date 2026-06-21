<?php

namespace ThemePaste\SecureAdmin\Classes\Pro;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class ProFeaturesSettings implements FeatureInterface {

    use Hook;

    public function register_hooks() {
        // Add the Pro sub-tabs (Advanced Malware Scanner, Web Application Firewall)
        // under the "Firewall & Malware" section. The field definitions for those
        // tabs are registered by their own feature classes (Pro\AdvancedFirewall and
        // the pro plugin), so this class only extends the settings option (tab) list.
        $this->filter( 'tpsa_settings_option', [$this, 'extend_pro_settings'] );
    }

    public function extend_pro_settings( $settings ) {
        $settings['firewall-malware']['sub'] = [
            'advanced-malware-scanner' => array(
                'label'  => __( 'Advanced Malware Scanner', 'admin-safety-guard' ),
                'is_pro' => true,
            ),
            'web-application-firewall' => array(
                'label'  => __( 'Web Application Firewall', 'admin-safety-guard' ),
                'is_pro' => true,
            ),
        ];
        return $settings;
    }

}
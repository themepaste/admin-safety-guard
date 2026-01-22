<?php

namespace ThemePaste\SecureAdmin\Classes\Pro;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class ProFeaturesSettings implements FeatureInterface {

    use Hook;

    public function register_hooks() {
        // 1) Extend settings UI
        $this->filter( 'tpsa_settings_fields', [$this, 'extend_pro_fields'] );

        // Settings label
        $this->filter( 'tpsa_settings_option', [$this, 'extend_pro_settings'] );
    }

    public function extend_pro_settings( $settings ) {
        $settings['firewall-malware']['sub'] = [
            'advanced-malware-scanner' => array(
                'label'  => __( 'Advanced Malware Scanner', 'tp-secure-plugin' ),
                'is_pro' => true,
            ),
            'web-application-firewall' => array(
                'label'  => __( 'Web Application Firewall', 'tp-secure-plugin' ),
                'is_pro' => true,
            ),
        ];
        return $settings;
    }

}
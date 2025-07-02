<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;

class FeatureManager {

    public function __construct() {
        $this->load_features();
    }

    private function load_features() {
        $features = [
            Features\HideAdminBar::class,
        ];

        foreach ( $features as $feature ) {
            if ( class_exists( $feature ) && in_array( FeatureInterface::class, class_implements( $feature ) ) ) {
                $feature_instance = new $feature();
                $feature_instance->register_hooks();
            }
        }
    }
}
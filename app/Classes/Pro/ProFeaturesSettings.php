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

}
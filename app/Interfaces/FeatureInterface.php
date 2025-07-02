<?php

namespace ThemePaste\SecureAdmin\Interfaces;

defined( 'ABSPATH' ) || exit;

interface FeatureInterface {
    public function register_hooks();
}
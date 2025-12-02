<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;

class FeatureManager {

    /**
     * Loads all features.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->load_features();
    }

    /**
     * Loads all features.
     *
     * Iterates over the list of features, checks if the class exists and implements the FeatureInterface.
     * If the class exists and implements the interface, an instance of the class is created and its
     * register_hooks() method is called.
     *
     * @since 1.0.0
     */
    private function load_features() {
        $features = [
            Features\HideAdminBar::class,
            Features\LoginLogout::class,
            Features\LoginLogsActivity::class,
            Features\LimitLoginAttempts::class,
            Features\Recaptcha::class,
            Features\TwoFactorAuth::class,
            Features\PasswordProtection::class,
            Features\PrivacyHardening::class,
            Features\Customize::class,

            // Pro
            Pro\AdvancedFirewall::class,
            Pro\AdvancedMalwareScanner::class,
            Pro\TablePrefixCheck::class,
            Pro\SocialLogin::class,
        ];

        foreach ( $features as $feature ) {
            if ( class_exists( $feature ) && in_array( FeatureInterface::class, class_implements( $feature ) ) ) {
                $feature_instance = new $feature();
                $feature_instance->register_hooks();
            }
        }
    }
}
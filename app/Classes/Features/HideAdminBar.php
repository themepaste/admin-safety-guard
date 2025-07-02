<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class HideAdminBar implements FeatureInterface {

    use Hook;

    private $features_id = 'hide-admin-bar';

    public function register_hooks() {
        $this->action( 'init', [$this, 'hide_admin_bar']);
    }

    public function hide_admin_bar() {
        $option_name    = get_tpsa_settings_option_name( $this->features_id );
        $settings       = get_option( $option_name, [] );
        if( ! empty( $settings ) && is_array( $settings ) ) {
            if( isset( $settings['enable'] ) && $settings['enable'] == 1 ) {
                add_filter( 'show_admin_bar', '__return_false');
            }
        }
    }
}
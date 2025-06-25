<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;

class Admin {

    use Hook;

    public function __construct() {
        $this->action( 'plugins_loaded', function() {
            // ( new Settings() )->init();
        } );
    }
}
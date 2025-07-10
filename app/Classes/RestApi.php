<?php

namespace ThemePaste\SecureAdmin\Classes;

use ThemePaste\SecureAdmin\Classes\APIs\FailedLoginsController;
use ThemePaste\SecureAdmin\Classes\APIs\SuccessLoginsController;

class RestApi {

    /**
     * Private constructor to prevent direct instantiation.
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Registers the REST API routes.
     */
    public function register_routes() {
        register_rest_route(
            'secure-admin/v1',
            '/failed-logins',
            [
                'methods'             => 'GET',
                'callback'            => [ FailedLoginsController::get(), 'get_failed_logins' ],
                'permission_callback' => [ FailedLoginsController::get(), 'get_failed_logins_permission_check' ],
            ]
        );
        register_rest_route(
            'secure-admin/v1',
            '/success-logins',
            [
                'methods'             => 'GET',
                'callback'            => [ SuccessLoginsController::get(), 'get_success_logins' ],
                'permission_callback' => [ SuccessLoginsController::get(), 'get_failed_logins_permission_check' ],
            ]
        );
    }
}
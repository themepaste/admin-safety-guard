<?php

namespace Themepaste\SecureAdmin\Classes;

use Themepaste\SecureAdmin\Classes\API\FailedLoginsController;
use Themepaste\SecureAdmin\Classes\API\SuccessfulLoginsController;

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
            '/successful-logins',
            [
                'methods'             => 'GET',
                'callback'            => [ SuccessfulLoginsController::get(), 'get_successful_logins' ],
                'permission_callback' => [ SuccessfulLoginsController::get(), 'get_successful_logins_permission_check' ],
            ]
        );
    }
}
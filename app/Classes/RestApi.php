<?php

namespace ThemePaste\SecureAdmin\Classes;

use ThemePaste\SecureAdmin\Classes\APIs\BlockUsersController;
use ThemePaste\SecureAdmin\Classes\APIs\FailedLoginsController;
use ThemePaste\SecureAdmin\Classes\APIs\Reports;
use ThemePaste\SecureAdmin\Classes\APIs\SuccessLoginsController;
use ThemePaste\SecureAdmin\Classes\APIs\TwoFAByAppUsers;

class RestApi {

    /**
     * Private constructor to prevent direct instantiation.
     */
    public function __construct() {
        add_action( 'rest_api_init', [$this, 'register_routes'] );
    }

    /**
     * Registers the REST API routes.
     */
    public function register_routes() {

        register_rest_route(
            'secure-admin/v1',
            '/success-logins',
            [
                'methods'             => 'GET',
                'callback'            => function ( $request ) {
                    return SuccessLoginsController::get()->get_data( $request );
                },
                'permission_callback' => function ( $request ) {
                    return SuccessLoginsController::get()->authorize_request( $request );
                },
            ]
        );

        register_rest_route(
            'secure-admin/v1',
            '/failed-logins',
            [
                'methods'             => 'GET',
                'callback'            => function ( $request ) {
                    return FailedLoginsController::get()->get_data( $request );
                },
                'permission_callback' => function ( $request ) {
                    return FailedLoginsController::get()->authorize_request( $request );
                },
            ]
        );

        register_rest_route(
            'secure-admin/v1',
            '/block-users',
            [
                'methods'             => 'GET',
                'callback'            => function ( $request ) {
                    return BlockUsersController::get()->get_data( $request );
                },
                'permission_callback' => function ( $request ) {
                    return BlockUsersController::get()->authorize_request( $request );
                },
            ]
        );

        register_rest_route(
            'secure-admin/v1',
            '/dahboard/limit-login-attempts',
            [
                'methods'             => 'GET',
                'callback'            => function ( $request ) {
                    return Reports::get()->get_data( $request );
                },
                'permission_callback' => function ( $request ) {
                    return Reports::get()->authorize_request( $request );
                },
            ]
        );

        register_rest_route(
            'secure-admin/v1',
            '/failed-logins/count',
            [
                'methods'             => 'GET',
                'callback'            => function ( $request ) {
                    return FailedLoginsController::get()->get_count( $request );
                },
                'permission_callback' => function ( $request ) {
                    return FailedLoginsController::get()->authorize_request( $request );
                },
            ]
        );

        register_rest_route(
            'secure-admin/v1',
            '/2fa/app/users',
            [
                'methods'             => 'GET',
                'callback'            => function ( $request ) {
                    return TwoFAByAppUsers::get()->get_data( $request );
                },
                'permission_callback' => function ( $request ) {
                    return TwoFAByAppUsers::get()->authorize_request( $request );
                },
                'args'                => [
                    'per_page' => [
                        'default'           => 10,
                        'sanitize_callback' => 'absint',
                    ],
                    'page'     => [
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                    ],
                    's'        => [
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'role'     => [
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

    }
}
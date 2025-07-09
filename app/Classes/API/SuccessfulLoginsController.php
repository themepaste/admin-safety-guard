<?php

namespace Themepaste\SecureAdmin\Classes\API;

use WP_REST_Request;
use WP_REST_Response;

class SuccessfulLoginsController {
    /**
     * The single instance of the class.
     *
     * @var SuccessfulLoginsController|null
     */
    private static $instance = null;

    /**
     * Ensures only one instance of the class is loaded.
     *
     * @return SuccessfulLoginsController
     */
    public static function get() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retrieves successful login data.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_successful_logins( WP_REST_Request $request ) {
        global $wpdb;

        $page        = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
        $limit       = $request->get_param( 'limit' ) ? absint( $request->get_param( 'limit' ) ) : 10;
        $offset      = ( $page - 1 ) * $limit;
        $table_name  = $wpdb->prefix . 'secure_admin_s_logins';
        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
        $data        = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );

        $response = [
            'data'  => $data,
            'total' => $total_items,
        ];

        return new WP_REST_Response( $response, 200 );
    }

    /**
     * Checks if the user has permission to access the endpoint.
     *
     * @return bool
     */
    public function get_successful_logins_permission_check() {
        return current_user_can( 'manage_options' );
    }
}
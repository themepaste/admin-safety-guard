<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;

class FailedLoginsController {
    /**
     * The single instance of the class.
     *
     * @var FailedLoginsController|null
     */
    private static $instance = null;

    /**
     * Ensures only one instance of the class is loaded.
     *
     * @return FailedLoginsController
     */
    public static function get() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retrieves failed login data.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_failed_logins( WP_REST_Request $request ) {
        global $wpdb;

        // Sanitize and validate parameters
        $page  = absint( $request->get_param( 'page' ) );
        $limit = absint( $request->get_param( 'limit' ) );

        $page  = $page > 0 ? $page : 1;
        $limit = $limit > 0 ? $limit : 10;

        $offset     = ( $page - 1 ) * $limit;
        $table_name = get_tpsa_db_table_name( 'failed_logins' );

        // Verify table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
            return new WP_REST_Response(
                [
                    'error'   => true,
                    'message' => __( 'Table does not exist.', 'tp-secure-plugin' ),
                ],
                500
            );
        }

        // Get total number of records
        $total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        // Fetch paginated results
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );

        if ( empty( $results ) ) {
            return $this->build_response( [], $total_items, $page, $limit, __( 'No records found for the given page.', 'tp-secure-plugin' ) );
        }

        return $this->build_response( $results, $total_items, $page, $limit );
    }


    /**
     * Checks if the user has permission to access the endpoint.
     *
     * @return bool
     */
    public function get_failed_logins_permission_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Build a standard REST response for failed login data.
     *
     * @param array $data
     * @param int   $total
     * @param int   $page
     * @param int   $limit
     * @param string|null $notice Optional notice message.
     *
     * @return WP_REST_Response
     */
    private function build_response( $data, $total, $page, $limit, $notice = null ) {
        $response = [
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ];

        if ( $notice ) {
            $response['notice'] = $notice;
        }

        return new WP_REST_Response( $response, 200 );
    }
}
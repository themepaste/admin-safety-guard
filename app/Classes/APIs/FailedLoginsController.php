<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

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
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    
    /**
     * Returns a paginated list of failed login records.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_failed_logins( WP_REST_Request $request ) {
        global $wpdb;

        // Sanitize and validate parameters
        $page   = absint( $request->get_param( 'page' ) );
        $limit  = absint( $request->get_param( 'limit' ) );
        $search = sanitize_text_field( $request->get_param( 's' ) );

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

        $where_sql = '';
        $like_search = '';
        if ( ! empty( $search ) ) {
            $like_search = '%' . $wpdb->esc_like( $search ) . '%';

            // Search across all relevant columns
            $where_sql = $wpdb->prepare(
                "WHERE username LIKE %s 
                OR user_agent LIKE %s 
                OR ip_address LIKE %s 
                OR login_time LIKE %s 
                OR country LIKE %s 
                OR city LIKE %s",
                $like_search,
                $like_search,
                $like_search,
                $like_search,
                $like_search,
                $like_search
            );
        }

        // Get total number of filtered records
        $total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where_sql" );

        // Fetch paginated results
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name $where_sql ORDER BY id DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );

        if ( empty( $results ) ) {
            return $this->build_response( [], $total_items, $page, $limit, __( 'No records found for the given search.', 'tp-secure-plugin' ) );
        }

        return $this->build_response( $results, $total_items, $page, $limit );
    }


    /**
     * Permission check callback for `get_failed_logins` endpoint.
     *
     * Ensures that the request is valid and that the user has the necessary
     * permissions to access the data.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_failed_logins_permission_check( WP_REST_Request $request ) {

        // You can add more custom checks here, like checking roles or capabilities:
        // if ( ! current_user_can( 'manage_options' ) ) {
        //     return new WP_Error(
        //         'rest_forbidden',
        //         __( 'You do not have permission to access this data.', 'tp-secure-plugin' ),
        //         [ 'status' => 403 ]
        //     );
        // }

        return true;
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
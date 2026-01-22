<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

abstract class BaseController {
    /**
     * The single instance of the class.
     *
     * @var static|null
     */
    protected static $instance = null;

    /**
     * Ensures only one instance of the class is loaded.
     *
     * @return static
     */
    public static function get() {
        if ( is_null( static::$instance ) ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    protected function __construct() {}

    /**
     * Abstract method to get the database table name for the controller.
     *
     * @return string
     */
    abstract protected function get_table_name(): string;

    /**
     * Generic method to return a paginated list of records from a specified table.
     *
     * @param WP_REST_Request $request
     * @param string $table_name The name of the database table to query.
     * @return WP_REST_Response
     */
    protected function get_records( WP_REST_Request $request, string $table_name ) {
        global $wpdb;

        // Sanitize and validate parameters
        $page = absint( $request->get_param( 'page' ) );
        $limit = absint( $request->get_param( 'limit' ) );
        $search = sanitize_text_field( $request->get_param( 's' ) );

        $page = $page > 0 ? $page : 1;
        $limit = $limit > 0 ? $limit : 10;

        $offset = ( $page - 1 ) * $limit;
        $full_table_name = get_tpsa_db_table_name( $table_name );

        // Verify table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $full_table_name ) ) !== $full_table_name ) {
            return new WP_REST_Response(
                [
                    'error'   => true,
                    'message' => __( 'Table does not exist.', 'tp-secure-plugin' ),
                ],
                500
            );
        }

        $where_sql = '';
        if ( !empty( $search ) ) {
            $like_search = '%' . $wpdb->esc_like( $search ) . '%';

            // Search across all relevant columns (assuming common columns for login logs)
            $where_sql = $wpdb->prepare(
                "WHERE username LIKE %s
                OR user_agent LIKE %s
                OR ip_address LIKE %s
                OR login_time LIKE %s",
                $like_search,
                $like_search,
                $like_search,
                $like_search
            );
        }

        // Get total number of filtered records
        $total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $full_table_name $where_sql" );

        // Fetch paginated results
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $full_table_name $where_sql ORDER BY id DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );

        if ( empty( $results ) && !empty( $search ) ) {
            return $this->build_response( [], $total_items, $page, $limit, __( 'No records found for the given search.', 'tp-secure-plugin' ) );
        } elseif ( empty( $results ) ) {
            return $this->build_response( [], $total_items, $page, $limit, __( 'No records found for the given page.', 'tp-secure-plugin' ) );
        }

        return $this->build_response( $results, $total_items, $page, $limit );
    }

    /**
     * Retrieves the total number of records in the specified table.
     *
     * If no table name is provided, the controller's default table name is used.
     *
     * @param string $table_name Optional. The name of the database table to query. Defaults to the controller's default table name.
     * @return int The total number of records in the specified table.
     */
    protected function get_record_count( string $table_name = '' ): int {
        global $wpdb;

        // If no table passed, use controller default table
        $table_name = $table_name !== '' ? $table_name : $this->get_table_name();

        $full_table_name = get_tpsa_db_table_name( $table_name );

        // Check table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $full_table_name ) ) !== $full_table_name ) {
            return 0;
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$full_table_name}" );
    }

    /**
     * Default permission check callback. Can be overridden by subclasses.
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function authorize_request( WP_REST_Request $request ) {
        // Default: allow access. Subclasses should override for specific permissions.
        return true;
    }

    /**
     * Build a standard REST response.
     *
     * @param array $data
     * @param int   $total
     * @param int   $page
     * @param int   $limit
     * @param string|null $notice Optional notice message.
     *
     * @return WP_REST_Response
     */
    protected function build_response( $data, $total, $page, $limit, $notice = null ) {
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
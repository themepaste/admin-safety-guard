<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use wpdb;

class Reports {
    protected static $instance = null;

    /**
     * Singleton instance getter.
     */
    public static function get() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Handle GET request and return row count of a specific table.
     */
    public function get_data( WP_REST_Request $request ) {
        global $wpdb;

        $allowed_tables = [
            's_logins',
            'failed_logins',
            'block_users'
        ];

        $table_key = $request->get_param('report');

        if ( ! $table_key || ! in_array( $table_key, $allowed_tables, true ) ) {
            return new WP_REST_Response([
                'error' => 'Invalid or missing report parameter.',
            ], 400);
        }

        $full_table_name = get_tpsa_db_table_name( $table_key );

        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$full_table_name}" );

        return new WP_REST_Response([
            'table'      => $table_key,
            'row_count'  => intval($count),
        ], 200);
    }

    /**
     * Authorization callback for the route.
     */
    public function authorize_request( WP_REST_Request $request ) {
        // return current_user_can('manage_options'); // Change as needed

        return true;
    }
}

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
     * Gets the number of records in the given tables.
     *
     * Returns an associative array where the keys are the table names and the values are the number of records in the table.
     * If the table name is invalid, the value will be the string 'Invalid table'.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The response object.
     */
    public function get_data( WP_REST_Request $request ) {
        global $wpdb;

        $allowed_tables = [
            's_logins',
            'failed_logins',
            'block_users'
        ];

        $report_param = $request->get_param( 'reports' );
        $requested_tables = array_map( 'trim', explode( ',', $report_param ) );

        $results = [];

        foreach ( $requested_tables as $table_key ) {
            if ( in_array( $table_key, $allowed_tables, true ) ) {
                $full_table_name = get_tpsa_db_table_name( $table_key );

                // Optional: filter past 24 hours
                if ( $table_key === 's_logins' || $table_key === 'block_users' ) {
                    $count = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$full_table_name} WHERE login_time >= NOW() - INTERVAL 1 DAY"
                        )
                    );
                } elseif ( $table_key === 'failed_logins' ) {
                    $count = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$full_table_name} WHERE last_login_time >= NOW() - INTERVAL 1 DAY"
                        )
                    );
                } else {
                    $count = 0;
                }

                $results[ $table_key ] = intval( $count );
            } else {
                $results[ $table_key ] = 'Invalid table';
            }
        }

        return new WP_REST_Response($results, 200);
    }

    /**
     * Authorization callback for the route.
     */
    public function authorize_request( WP_REST_Request $request ) {
        // return current_user_can('manage_options');
        return true;
    }
}

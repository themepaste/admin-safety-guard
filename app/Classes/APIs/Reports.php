<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;

class Reports {
    protected static $instance = null;

    public static function get() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Last 24 hours report in 6 buckets (4 hours each).
     *
     * Response:
     * [
     *   { "name": "Blocked Users", "data": [..6 nums..] },
     *   { "name": "Successful Logins", "data": [..6 nums..] },
     *   { "name": "Failed Logins", "data": [..6 nums..] }
     * ]
     */
    public function get_data( WP_REST_Request $request ) {
        global $wpdb;

        // request key => [label, table_key, datetime_column]
        $map = [
            'block_users'   => ['Blocked Users', 'block_users', 'login_time'],
            's_logins'      => ['Successful Logins', 's_logins', 'login_time'],
            'failed_logins' => ['Failed Logins', 'failed_logins', 'last_login_time'],
        ];

        // Optional filter via ?reports=s_logins,failed_logins,block_users
        $report_param = (string) $request->get_param( 'reports' );
        $requested = array_filter( array_map( 'trim', explode( ',', $report_param ) ) );

        if ( empty( $requested ) ) {
            $requested = array_keys( $map );
        }

        // Last 24 hours window in WP timezone
        $end_ts = current_time( 'timestamp' );
        $start_ts = $end_ts - DAY_IN_SECONDS;

        $start_mysql = date( 'Y-m-d H:i:s', $start_ts );
        $end_mysql = date( 'Y-m-d H:i:s', $end_ts );

        $series = [];

        foreach ( $requested as $key ) {
            if ( !isset( $map[$key] ) ) {
                continue;
            }

            [$label, $table_key, $time_col] = $map[$key];
            $table = get_tpsa_db_table_name( $table_key );

            // 6 buckets of 4 hours: index 0=oldest(24-20h) ... index 5=latest(4-0h)
            $data = array_fill( 0, 6, 0 );

            /**
             * Bucket formula:
             * bucket = FLOOR( (UNIX_TIMESTAMP(time_col) - UNIX_TIMESTAMP(start)) / 14400 )
             * 14400 seconds = 4 hours
             */
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT
                        FLOOR( (UNIX_TIMESTAMP({$time_col}) - UNIX_TIMESTAMP(%s)) / 14400 ) AS bucket,
                        COUNT(*) AS total
                    FROM {$table}
                    WHERE {$time_col} BETWEEN %s AND %s
                    GROUP BY bucket
                    ",
                    $start_mysql,
                    $start_mysql,
                    $end_mysql
                ),
                ARRAY_A
            );

            foreach ( $rows as $r ) {
                $bucket = (int) $r['bucket'];
                if ( $bucket >= 0 && $bucket < 6 ) {
                    $data[$bucket] = (int) $r['total'];
                }
            }

            $series[] = [
                'name' => $label,
                'data' => $data,
            ];
        }

        // Keep consistent order even if user passes reports in different order
        $order = ['block_users', 's_logins', 'failed_logins'];
        $ordered_series = [];

        foreach ( $order as $k ) {
            foreach ( $series as $item ) {
                if (
                    ( $k === 'block_users' && $item['name'] === 'Blocked Users' ) ||
                    ( $k === 's_logins' && $item['name'] === 'Successful Logins' ) ||
                    ( $k === 'failed_logins' && $item['name'] === 'Failed Logins' )
                ) {
                    $ordered_series[] = $item;
                    break;
                }
            }
        }

        // If user passed subset, return subset in generated order
        if ( count( $ordered_series ) > 0 ) {
            return new WP_REST_Response( $ordered_series, 200 );
        }

        return new WP_REST_Response( $series, 200 );
    }

    public function authorize_request( WP_REST_Request $request ) {
        // return current_user_can('manage_options');
        return true;
    }
}
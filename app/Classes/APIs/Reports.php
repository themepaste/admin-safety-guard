<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;

class Reports extends BaseController {

    /**
     * This controller aggregates across several tables rather than paging one.
     *
     * @return string
     */
    protected function get_table_name(): string {
        return '';
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

        // Last 24 hours window in WP timezone (data stored via current_time('mysql'))
        $end_mysql   = current_time( 'mysql' );
        $start_mysql = wp_date( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );

        $series = [];

        // Iterate the map rather than the request so the series always comes
        // back in a stable order, whatever order `reports` was passed in.
        foreach ( $map as $key => $definition ) {
            if ( !in_array( $key, $requested, true ) ) {
                continue;
            }

            [$label, $table_key, $time_col] = $definition;
            $table = get_tpsa_db_table_name( $table_key );

            if ( !$this->table_exists( $table ) ) {
                continue;
            }

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

        return new WP_REST_Response( $series, 200 );
    }
}
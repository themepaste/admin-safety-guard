<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Monitoring actions: at-a-glance counters, and the ability to act on what the
 * logs show without leaving the screen.
 *
 * A log you cannot act on is just a list. These endpoints let an administrator
 * block an address they can see misbehaving, and release one that was blocked
 * by mistake.
 */
class MonitorController extends BaseController {

    /**
     * Not a single-table controller.
     *
     * @return string
     */
    protected function get_table_name(): string {
        return '';
    }

    /* ---------------------------------------------------------------------
     * Summary
     * ------------------------------------------------------------------- */

    /**
     * Counters for the dashboard tiles.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_summary( WP_REST_Request $request ) {
        global $wpdb;

        $since_24h = wp_date( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );

        $s_logins = get_tpsa_db_table_name( 's_logins' );
        $failed = get_tpsa_db_table_name( 'failed_logins' );
        $blocked = get_tpsa_db_table_name( 'block_users' );

        $successful_24h = 0;
        $failed_24h = 0;
        $blocked_total = 0;
        $locked_now = 0;
        $top_offender = null;

        if ( $this->table_exists( $s_logins ) ) {
            $successful_24h = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$s_logins} WHERE login_time >= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $since_24h
                )
            );
        }

        if ( $this->table_exists( $failed ) ) {
            // login_attempts holds the running counter for the address, so sum
            // it rather than counting rows.
            $failed_24h = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COALESCE( SUM( login_attempts ), 0 ) FROM {$failed} WHERE last_login_time >= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $since_24h
                )
            );

            $lock_minutes = $this->lockout_minutes();
            $lock_since = wp_date( 'Y-m-d H:i:s', time() - ( $lock_minutes * MINUTE_IN_SECONDS ) );

            $locked_now = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT( DISTINCT ip_address ) FROM {$failed} WHERE lockout_time IS NOT NULL AND lockout_time >= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $lock_since
                )
            );

            $offender = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT ip_address, SUM( login_attempts ) AS hits, SUM( lockouts ) AS locks
                     FROM {$failed}
                     WHERE last_login_time >= %s
                     GROUP BY ip_address
                     ORDER BY hits DESC, locks DESC
                     LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $since_24h
                ),
                ARRAY_A
            );

            if ( $offender && !empty( $offender['ip_address'] ) ) {
                $top_offender = [
                    'ip'       => $offender['ip_address'],
                    'attempts' => (int) $offender['hits'],
                    'lockouts' => (int) $offender['locks'],
                ];
            }
        }

        if ( $this->table_exists( $blocked ) ) {
            $blocked_total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$blocked}" ); // phpcs:ignore WordPress.DB.PreparedSQL
        }

        return new WP_REST_Response(
            [
                'successful_24h' => $successful_24h,
                'failed_24h'     => $failed_24h,
                'blocked_total'  => $blocked_total,
                'locked_now'     => $locked_now,
                'top_offender'   => $top_offender,
                'protection_on'  => $this->protection_enabled(),
            ],
            200
        );
    }

    /* ---------------------------------------------------------------------
     * Actions
     * ------------------------------------------------------------------- */

    /**
     * Block an IP address immediately.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function block_ip( WP_REST_Request $request ) {
        $ip = $this->validated_ip( $request );

        if ( is_wp_error( $ip ) ) {
            return $ip;
        }

        // Refuse to let an administrator block the address they are using;
        // that would lock them out of their own site.
        if ( $ip === tpsa_get_client_ip() ) {
            return new WP_Error(
                'tpsa_own_ip',
                __( 'That is your own IP address. Blocking it would lock you out of this site.', 'admin-safety-guard' ),
                ['status' => 400]
            );
        }

        $table = get_tpsa_db_table_name( 'block_users' );

        if ( !$this->table_exists( $table ) ) {
            return new WP_Error( 'tpsa_no_table', __( 'The block list table is missing.', 'admin-safety-guard' ), ['status' => 500] );
        }

        global $wpdb;

        $exists = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE ip_address = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $ip
            )
        );

        if ( !$exists ) {
            $wpdb->insert(
                $table,
                [
                    'user_agent' => 'Blocked manually from the monitoring screen',
                    'ip_address' => $ip,
                    'login_time' => current_time( 'mysql' ),
                ],
                ['%s', '%s', '%s']
            );
        }

        return new WP_REST_Response(
            [
                'ip'      => $ip,
                'blocked' => true,
                /* translators: %s: IP address. */
                'message' => sprintf( __( '%s is now blocked.', 'admin-safety-guard' ), $ip ),
            ],
            200
        );
    }

    /**
     * Release an IP address: remove the block and clear its lockout counters.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function unblock_ip( WP_REST_Request $request ) {
        $ip = $this->validated_ip( $request );

        if ( is_wp_error( $ip ) ) {
            return $ip;
        }

        global $wpdb;

        $blocked = get_tpsa_db_table_name( 'block_users' );
        if ( $this->table_exists( $blocked ) ) {
            $wpdb->delete( $blocked, ['ip_address' => $ip], ['%s'] );
        }

        // Clearing the block without clearing the counters would let the very
        // next failed attempt re-trigger the lockout.
        $failed = get_tpsa_db_table_name( 'failed_logins' );
        if ( $this->table_exists( $failed ) ) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$failed} SET login_attempts = 0, lockouts = 0, lockout_time = NULL WHERE ip_address = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $ip
                )
            );
        }

        return new WP_REST_Response(
            [
                'ip'      => $ip,
                'blocked' => false,
                /* translators: %s: IP address. */
                'message' => sprintf( __( '%s has been released and its counters reset.', 'admin-safety-guard' ), $ip ),
            ],
            200
        );
    }

    /* ---------------------------------------------------------------------
     * Export
     * ------------------------------------------------------------------- */

    /**
     * Export a log as CSV for audits and offline analysis.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function export_csv( WP_REST_Request $request ) {
        $map = [
            'success' => ['s_logins', ['username', 'ip_address', 'user_agent', 'login_time', 'login_count']],
            'failed'  => ['failed_logins', ['username', 'ip_address', 'user_agent', 'login_attempts', 'lockouts', 'first_login_time', 'last_login_time']],
            'blocked' => ['block_users', ['ip_address', 'user_agent', 'login_time']],
        ];

        $type = sanitize_key( (string) $request->get_param( 'type' ) );

        if ( !isset( $map[$type] ) ) {
            return new WP_Error( 'tpsa_bad_type', __( 'Unknown log type.', 'admin-safety-guard' ), ['status' => 400] );
        }

        list( $table_key, $columns ) = $map[$type];
        $table = get_tpsa_db_table_name( $table_key );

        if ( !$this->table_exists( $table ) ) {
            return new WP_Error( 'tpsa_no_table', __( 'That log table is missing.', 'admin-safety-guard' ), ['status' => 500] );
        }

        global $wpdb;

        // Hard cap: an export is a convenience, not a way to exhaust memory.
        $rows = $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY id DESC LIMIT 5000", // phpcs:ignore WordPress.DB.PreparedSQL
            ARRAY_A
        );

        $out = fopen( 'php://temp', 'r+' );
        fputcsv( $out, $columns );

        foreach ( (array) $rows as $row ) {
            $line = [];
            foreach ( $columns as $column ) {
                $line[] = isset( $row[$column] ) ? $this->csv_safe( (string) $row[$column] ) : '';
            }
            fputcsv( $out, $line );
        }

        rewind( $out );
        $csv = stream_get_contents( $out );
        fclose( $out );

        return new WP_REST_Response(
            [
                'filename' => 'asg-' . $type . '-logins-' . gmdate( 'Ymd-His' ) . '.csv',
                'csv'      => $csv,
                'rows'     => count( (array) $rows ),
            ],
            200
        );
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    /**
     * Read and validate the `ip` parameter.
     *
     * @param WP_REST_Request $request Request.
     * @return string|WP_Error
     */
    private function validated_ip( WP_REST_Request $request ) {
        $ip = trim( (string) $request->get_param( 'ip' ) );

        if ( '' === $ip || !filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return new WP_Error(
                'tpsa_bad_ip',
                __( 'A valid IP address is required.', 'admin-safety-guard' ),
                ['status' => 400]
            );
        }

        return $ip;
    }

    /**
     * Neutralise spreadsheet formula injection.
     *
     * A logged username or user agent beginning with = + - or @ is executed as
     * a formula when the exported file is opened in Excel or Sheets, so prefix
     * it with an apostrophe.
     *
     * @param string $value Cell value.
     * @return string
     */
    private function csv_safe( $value ) {
        if ( '' !== $value && in_array( $value[0], ['=', '+', '-', '@', "\t", "\r"], true ) ) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Configured lockout window, in minutes.
     *
     * @return int
     */
    private function lockout_minutes() {
        $settings = get_option( get_tpsa_settings_option_name( 'limit-login-attempts' ), [] );

        return max( 1, (int) ( $settings['block-for'] ?? 15 ) );
    }

    /**
     * Whether brute-force protection is switched on.
     *
     * @return bool
     */
    private function protection_enabled() {
        $settings = get_option( get_tpsa_settings_option_name( 'limit-login-attempts' ), [] );

        return !empty( $settings['enable'] );
    }
}

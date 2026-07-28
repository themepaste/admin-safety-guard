<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Classes\ThreatLog;
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
                // Dashboard tiles.
                'threats_total'  => ThreatLog::count(),
                'threats_24h'    => ThreatLog::count( $since_24h ),
                'threats_prev'   => $this->threats_previous_day(),
                'active_users'   => $this->active_users( $since_24h ),
                'total_users'    => $this->total_users(),
                'failed_prev'    => $this->failed_previous_day(),
            ],
            200
        );
    }

    /**
     * Threats recorded in the 24 hours BEFORE the current window, so the
     * dashboard can show a real trend instead of a hard-coded percentage.
     *
     * @return int
     */
    private function threats_previous_day() {
        global $wpdb;

        $table = get_tpsa_db_table_name( ThreatLog::TABLE );

        if ( !ThreatLog::table_exists( $table ) ) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE blocked_at >= %s AND blocked_at < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                wp_date( 'Y-m-d H:i:s', time() - ( 2 * DAY_IN_SECONDS ) ),
                wp_date( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS )
            )
        );
    }

    /**
     * Failed attempts in the previous 24-hour window, for the trend arrow.
     *
     * @return int
     */
    private function failed_previous_day() {
        global $wpdb;

        $table = get_tpsa_db_table_name( 'failed_logins' );

        if ( !$this->table_exists( $table ) ) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE( SUM( login_attempts ), 0 ) FROM {$table} WHERE last_login_time >= %s AND last_login_time < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                wp_date( 'Y-m-d H:i:s', time() - ( 2 * DAY_IN_SECONDS ) ),
                wp_date( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS )
            )
        );
    }

    /**
     * Accounts that actually signed in during the window.
     *
     * The dashboard previously showed the total number of registered users and
     * called it "Active Users", which never changes and tells an administrator
     * nothing about what is happening on the site.
     *
     * @param string $since Site-local MySQL datetime.
     * @return int
     */
    private function active_users( $since ) {
        global $wpdb;

        $table = get_tpsa_db_table_name( 's_logins' );

        if ( !$this->table_exists( $table ) ) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT( DISTINCT username ) FROM {$table} WHERE login_time >= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $since
            )
        );
    }

    /**
     * Registered accounts, cached — count_users() is expensive on large sites.
     *
     * @return int
     */
    private function total_users() {
        $total = get_transient( 'tpsa_total_users' );

        if ( false === $total ) {
            $counts = count_users();
            $total = isset( $counts['total_users'] ) ? (int) $counts['total_users'] : 0;
            set_transient( 'tpsa_total_users', $total, HOUR_IN_SECONDS );
        }

        return (int) $total;
    }

    /* ---------------------------------------------------------------------
     * Threat log
     * ------------------------------------------------------------------- */

    /**
     * Paginated list of blocked threats.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_threats( WP_REST_Request $request ) {
        global $wpdb;

        $table = get_tpsa_db_table_name( ThreatLog::TABLE );

        if ( !ThreatLog::table_exists( $table ) ) {
            return new WP_REST_Response( ['data' => [], 'total' => 0, 'labels' => ThreatLog::types()], 200 );
        }

        $page = max( 1, absint( $request->get_param( 'page' ) ) );
        $limit = absint( $request->get_param( 'limit' ) );
        $limit = $limit > 0 ? min( $limit, 100 ) : 20;
        $offset = ( $page - 1 ) * $limit;

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return new WP_REST_Response(
            [
                'data'   => (array) $rows,
                'total'  => $total,
                'page'   => $page,
                'limit'  => $limit,
                // Human-readable names, so the UI does not duplicate the list.
                'labels' => ThreatLog::types(),
            ],
            200
        );
    }

    /**
     * Delete the whole threat log.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function clear_threats( WP_REST_Request $request ) {
        $removed = ThreatLog::clear();

        return new WP_REST_Response(
            [
                'cleared' => $removed,
                /* translators: %d: number of records removed. */
                'message' => sprintf( _n( 'Cleared %d record.', 'Cleared %d records.', $removed, 'admin-safety-guard' ), $removed ),
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
     * Purge
     * ------------------------------------------------------------------- */

    /**
     * The logs that can be purged, and the datetime column each is aged by.
     *
     * @return array<string, array{0:string,1:string}>
     */
    private function purgeable() {
        return [
            'success' => ['s_logins', 'login_time'],
            'failed'  => ['failed_logins', 'last_login_time'],
            'blocked' => ['block_users', 'login_time'],
            'threats' => ['threats', 'blocked_at'],
        ];
    }

    /**
     * Named retention windows, in seconds. Anything older is removed.
     *
     * @return array<string, int>
     */
    private function purge_windows() {
        return [
            '24h' => DAY_IN_SECONDS,
            '7d'  => 7 * DAY_IN_SECONDS,
            '14d' => 14 * DAY_IN_SECONDS,
            '30d' => 30 * DAY_IN_SECONDS,
            '90d' => 90 * DAY_IN_SECONDS,
        ];
    }

    /**
     * Delete old records from a log.
     *
     * Accepts either a named window (`older_than`: keep the last 7d/14d/30d…),
     * `all` to empty the log, or an explicit `from`/`to` date range so an
     * administrator can remove one specific incident.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function purge_log( WP_REST_Request $request ) {
        $logs = $this->purgeable();
        $type = sanitize_key( (string) $request->get_param( 'type' ) );

        if ( !isset( $logs[$type] ) ) {
            return new WP_Error( 'tpsa_bad_type', __( 'Unknown log type.', 'admin-safety-guard' ), ['status' => 400] );
        }

        list( $table_key, $time_column ) = $logs[$type];
        $table = get_tpsa_db_table_name( $table_key );

        if ( !$this->table_exists( $table ) ) {
            return new WP_Error( 'tpsa_no_table', __( 'That log table is missing.', 'admin-safety-guard' ), ['status' => 500] );
        }

        global $wpdb;

        $older_than = sanitize_key( (string) $request->get_param( 'older_than' ) );
        $from = $this->clean_date( $request->get_param( 'from' ) );
        $to = $this->clean_date( $request->get_param( 'to' ) );

        $column = '`' . str_replace( '`', '', $time_column ) . '`';

        // 1) Explicit range wins, so a single incident can be removed.
        if ( '' !== $from || '' !== $to ) {
            $clauses = [];
            $values = [];

            if ( '' !== $from ) {
                $clauses[] = "{$column} >= %s";
                $values[] = $from . ' 00:00:00';
            }

            if ( '' !== $to ) {
                $clauses[] = "{$column} <= %s";
                $values[] = $to . ' 23:59:59';
            }

            $removed = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$table} WHERE " . implode( ' AND ', $clauses ), // phpcs:ignore WordPress.DB.PreparedSQL
                    $values
                )
            );

            return $this->purge_response( $removed, $type );
        }

        // 2) Everything.
        if ( 'all' === $older_than ) {
            $removed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL
            $wpdb->query( "TRUNCATE TABLE {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL

            return $this->purge_response( $removed, $type );
        }

        // 3) Named retention window.
        $windows = $this->purge_windows();

        if ( !isset( $windows[$older_than] ) ) {
            return new WP_Error(
                'tpsa_bad_window',
                __( 'Choose a time period to delete.', 'admin-safety-guard' ),
                ['status' => 400]
            );
        }

        // Rows are written with current_time( 'mysql' ), so the cut-off is
        // site-local too.
        $cutoff = wp_date( 'Y-m-d H:i:s', time() - $windows[$older_than] );

        $removed = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE {$column} < %s", // phpcs:ignore WordPress.DB.PreparedSQL
                $cutoff
            )
        );

        return $this->purge_response( $removed, $type );
    }

    /**
     * Normalise a YYYY-MM-DD date, rejecting anything else.
     *
     * @param mixed $value Raw parameter.
     * @return string Empty when absent or malformed.
     */
    private function clean_date( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value || !preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
            return '';
        }

        // Reject impossible dates such as 2026-02-31.
        list( $y, $m, $d ) = array_map( 'intval', explode( '-', $value ) );

        return checkdate( $m, $d, $y ) ? $value : '';
    }

    /**
     * Standard response for a purge.
     *
     * @param mixed  $removed Rows removed, or false on failure.
     * @param string $type    Log type.
     *
     * @return WP_REST_Response
     */
    private function purge_response( $removed, $type ) {
        $count = is_numeric( $removed ) ? (int) $removed : 0;

        return new WP_REST_Response(
            [
                'type'    => $type,
                'removed' => $count,
                /* translators: %d: number of records deleted. */
                'message' => sprintf( _n( 'Deleted %d record.', 'Deleted %d records.', $count, 'admin-safety-guard' ), $count ),
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

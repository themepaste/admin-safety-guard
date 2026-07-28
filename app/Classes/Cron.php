<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Scheduled maintenance for the plugin's log tables.
 *
 * Without this the `block_users` table is never pruned, which means an IP that
 * trips the lockout threshold stays blocked forever instead of for 24 hours.
 */
class Cron {

    use Hook;

    /**
     * Cron hook name.
     *
     * @var string
     */
    const EVENT = 'tpsa_cleanup_logs';

    /**
     * How long failed-login history is retained, in days.
     *
     * @var int
     */
    const FAILED_LOGIN_RETENTION_DAYS = 30;

    /**
     * How long successful-login history is retained, in days.
     *
     * Successful sign-ins are appended one row per event, so without a
     * retention window this table grows for the life of the site.
     *
     * @var int
     */
    const SUCCESS_LOGIN_RETENTION_DAYS = 90;

    public function __construct() {
        $this->action( 'init', [$this, 'schedule_event'] );
        $this->action( self::EVENT, [$this, 'cleanup'] );

        register_deactivation_hook( TPSA_PLUGIN_FILE, [__CLASS__, 'unschedule_event'] );
    }

    /**
     * Make sure the daily cleanup event is registered.
     *
     * @return void
     */
    public function schedule_event() {
        if ( !wp_next_scheduled( self::EVENT ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::EVENT );
        }
    }

    /**
     * Remove the scheduled event when the plugin is deactivated.
     *
     * @return void
     */
    public static function unschedule_event() {
        wp_clear_scheduled_hook( self::EVENT );
    }

    /**
     * Expire blocked IPs and prune stale failed-login history.
     *
     * Timestamps in these tables are written with current_time( 'mysql' ), i.e.
     * in the site's local timezone, so the cut-offs are built with wp_date() to
     * match. Comparing against a UTC value would over- or under-delete by the
     * site's UTC offset.
     *
     * @return void
     */
    public function cleanup() {
        global $wpdb;

        // Blocked IPs are a 24-hour ban; drop anything older.
        $block_table = get_tpsa_db_table_name( 'block_users' );
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$block_table} WHERE login_time < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                wp_date( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS )
            )
        );

        // Keep the failed-login table from growing without bound.
        $failed_table = get_tpsa_db_table_name( 'failed_logins' );
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$failed_table} WHERE last_login_time < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                wp_date( 'Y-m-d H:i:s', time() - ( self::FAILED_LOGIN_RETENTION_DAYS * DAY_IN_SECONDS ) )
            )
        );

        // Same for the sign-in audit trail, which now records one row per event.
        $success_table = get_tpsa_db_table_name( 's_logins' );
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$success_table} WHERE login_time < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                wp_date( 'Y-m-d H:i:s', time() - ( self::SUCCESS_LOGIN_RETENTION_DAYS * DAY_IN_SECONDS ) )
            )
        );
    }
}

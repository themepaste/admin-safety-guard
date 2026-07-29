<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Central record of everything the plugin actually blocked.
 *
 * Features do not write here directly. They fire the `tpsa_threat_blocked`
 * action and this class records it, so a new protection can start reporting
 * threats without touching the dashboard, the REST API or the schema.
 *
 * @since 1.4.0
 */
class ThreatLog {

    use Hook;

    /**
     * Table name (without the site prefix).
     *
     * @var string
     */
    const TABLE = 'threats';

    /**
     * Known threat types and how to describe them to a human.
     *
     * @return array<string, string>
     */
    public static function types() {
        return [
            'login_lockout'   => __( 'Login lockout', 'admin-safety-guard' ),
            'ip_blocked'      => __( 'IP blocked for 24 hours', 'admin-safety-guard' ),
            'ip_denied'       => __( 'Blocked IP tried to sign in', 'admin-safety-guard' ),
            'recaptcha_fail'  => __( 'Failed reCAPTCHA', 'admin-safety-guard' ),
            'twofa_fail'      => __( 'Wrong two-factor code', 'admin-safety-guard' ),
            'twofa_blocked'   => __( 'Second factor not possible', 'admin-safety-guard' ),
            'xmlrpc_blocked'  => __( 'XML-RPC request blocked', 'admin-safety-guard' ),
            'login_url_probe' => __( 'Probed the old login URL', 'admin-safety-guard' ),
            'author_enum'      => __( 'Tried to discover usernames', 'admin-safety-guard' ),
            'session_ip_change' => __( 'Session used from a new IP', 'admin-safety-guard' ),
            'firewall_sqli'   => __( 'SQL injection attempt', 'admin-safety-guard' ),
            'firewall_xss'    => __( 'Cross-site scripting attempt', 'admin-safety-guard' ),
            'firewall_ua'     => __( 'Blocked user agent', 'admin-safety-guard' ),
            'firewall_size'   => __( 'Oversized request', 'admin-safety-guard' ),
            'firewall_ip'     => __( 'Firewall blocked IP', 'admin-safety-guard' ),
        ];
    }

    public function __construct() {
        $this->action( 'tpsa_threat_blocked', [$this, 'record'], 10, 3 );
    }

    /**
     * Record a blocked threat.
     *
     * @param string $type    One of the keys from types().
     * @param string $detail  Short context, e.g. the attempted username.
     * @param string $ip      Client address; resolved automatically when empty.
     *
     * @return void
     */
    public function record( $type, $detail = '', $ip = '' ) {
        $type = sanitize_key( $type );

        if ( '' === $type ) {
            return;
        }

        $table = get_tpsa_db_table_name( self::TABLE );

        if ( !self::table_exists( $table ) ) {
            return;
        }

        $ip = '' !== $ip ? $ip : tpsa_get_client_ip();

        // One row per address+type per minute. A sustained attack would
        // otherwise write thousands of near-identical rows and turn the
        // dashboard counter into noise.
        $bucket = 'tpsa_threat_' . md5( $type . '|' . $ip );

        if ( false !== get_transient( $bucket ) ) {
            return;
        }

        set_transient( $bucket, 1, MINUTE_IN_SECONDS );

        global $wpdb;

        $wpdb->insert(
            $table,
            [
                'threat_type' => substr( $type, 0, 40 ),
                'ip_address'  => substr( (string) $ip, 0, 45 ),
                'detail'      => substr( (string) $detail, 0, 190 ),
                'user_agent'  => substr( self::user_agent(), 0, 255 ),
                'request_uri' => substr( self::request_uri(), 0, 190 ),
                'blocked_at'  => current_time( 'mysql' ),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Convenience wrapper so features read clearly at the call site.
     *
     * @param string $type   Threat type.
     * @param string $detail Short context.
     * @param string $ip     Optional address.
     *
     * @return void
     */
    public static function report( $type, $detail = '', $ip = '' ) {
        do_action( 'tpsa_threat_blocked', $type, $detail, $ip );
    }

    /**
     * Total recorded threats, optionally within a window.
     *
     * @param string $since Site-local MySQL datetime, or '' for all time.
     * @return int
     */
    public static function count( $since = '' ) {
        $table = get_tpsa_db_table_name( self::TABLE );

        if ( !self::table_exists( $table ) ) {
            return 0;
        }

        global $wpdb;

        if ( '' === $since ) {
            return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE blocked_at >= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $since
            )
        );
    }

    /**
     * Delete every recorded threat.
     *
     * @return int Rows removed.
     */
    public static function clear() {
        $table = get_tpsa_db_table_name( self::TABLE );

        if ( !self::table_exists( $table ) ) {
            return 0;
        }

        global $wpdb;

        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL
        $wpdb->query( "TRUNCATE TABLE {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL

        return $count;
    }

    /**
     * Whether the table exists, cached per request.
     *
     * @param string $table Fully-prefixed table name.
     * @return bool
     */
    public static function table_exists( $table ) {
        static $checked = [];

        if ( isset( $checked[$table] ) ) {
            return $checked[$table];
        }

        global $wpdb;

        $checked[$table] = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );

        return $checked[$table];
    }

    private static function user_agent() {
        return isset( $_SERVER['HTTP_USER_AGENT'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
        : '';
    }

    private static function request_uri() {
        return isset( $_SERVER['REQUEST_URI'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
        : '';
    }
}

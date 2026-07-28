<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Weighted security audit of the whole site.
 *
 * The old score was simply "plugin features switched on / plugin features
 * available", so a site could read 100/100 while running an outdated core over
 * plain HTTP with a user called `admin`. This grades the site itself: real
 * conditions first, plugin coverage second, each weighted by how much it
 * actually matters.
 *
 * Every check is a local read (options, constants, filesystem) — no external
 * requests — and the whole result is cached, so the score costs nothing on a
 * normal page load.
 *
 * @since 1.3.0
 */
class SecurityAudit {

    use Hook;

    /**
     * Transient holding the computed report.
     *
     * @var string
     */
    const CACHE_KEY = 'tpsa_security_audit_v2';

    /**
     * How long a report stays cached.
     *
     * @var int
     */
    const CACHE_TTL = 900;

    /**
     * User meta storing a dismissed critical notice.
     *
     * @var string
     */
    const DISMISS_META = '_tpsa_audit_notice_dismissed';

    public function __construct() {
        // Recompute as soon as anything that feeds the score changes.
        foreach ( ['upgrader_process_complete', 'activated_plugin', 'deactivated_plugin', 'switch_theme', 'profile_update', 'user_register', 'delete_user'] as $hook ) {
            $this->action( $hook, [__CLASS__, 'flush'] );
        }

        $this->action( 'updated_option', [$this, 'flush_on_settings_change'] );

        $this->action( 'admin_notices', [$this, 'render_critical_notice'] );
        $this->action( 'admin_init', [$this, 'handle_dismiss'] );
    }

    /**
     * Drop the cache when one of our settings is saved.
     *
     * @param string $option Option name.
     * @return void
     */
    public function flush_on_settings_change( $option ) {
        if ( 0 === strpos( (string) $option, 'tpsa_' ) ) {
            self::flush();
        }
    }

    /**
     * Invalidate the cached report.
     *
     * @return void
     */
    public static function flush() {
        delete_transient( self::CACHE_KEY );
    }

    /* ---------------------------------------------------------------------
     * Report
     * ------------------------------------------------------------------- */

    /**
     * The full audit: score, grade and every check.
     *
     * @param bool $fresh Skip the cache.
     * @return array
     */
    public static function report( $fresh = false ) {
        if ( !$fresh ) {
            $cached = get_transient( self::CACHE_KEY );

            if ( is_array( $cached ) ) {
                return $cached;
            }
        }

        $checks = self::run_checks();

        $earned = 0;
        $possible = 0;
        $issues = [];

        foreach ( $checks as $check ) {
            $possible += $check['weight'];

            if ( $check['pass'] ) {
                $earned += $check['weight'];
            } else {
                $issues[] = $check;
            }
        }

        $score = $possible > 0 ? (int) round( ( $earned / $possible ) * 100 ) : 0;
        $score = max( 0, min( 100, $score ) );

        // Sort worst first so the list reads as a to-do.
        usort(
            $issues,
            static function ( $a, $b ) {
                $order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
                $cmp = $order[$a['severity']] <=> $order[$b['severity']];

                return 0 !== $cmp ? $cmp : ( $b['weight'] <=> $a['weight'] );
            }
        );

        $report = [
            'score'    => $score,
            'grade'    => self::grade( $score ),
            'label'    => self::label( $score ),
            'checks'   => $checks,
            'issues'   => $issues,
            'critical' => count( array_filter( $issues, static fn( $i ) => 'critical' === $i['severity'] ) ),
            'passed'   => count( $checks ) - count( $issues ),
            'total'    => count( $checks ),
        ];

        set_transient( self::CACHE_KEY, $report, self::CACHE_TTL );

        return $report;
    }

    /**
     * Every check, in one place.
     *
     * @return array
     */
    private static function run_checks() {
        $checks = [];

        // --- Site conditions ------------------------------------------------
        $checks[] = self::check(
            'https',
            __( 'Site is served over HTTPS', 'admin-safety-guard' ),
            is_ssl() || 0 === strpos( (string) get_option( 'home' ), 'https://' ),
            'critical',
            12,
            __( 'Passwords and session cookies travel in plain text without HTTPS. Install a TLS certificate — most hosts provide one free.', 'admin-safety-guard' )
        );

        $checks[] = self::check(
            'core-updated',
            __( 'WordPress core is up to date', 'admin-safety-guard' ),
            !self::core_needs_update(),
            'critical',
            12,
            __( 'Outdated core is the most exploited weakness on the web. Update from Dashboard, Updates.', 'admin-safety-guard' ),
            admin_url( 'update-core.php' )
        );

        $outdated = self::outdated_extensions();
        $checks[] = self::check(
            'extensions-updated',
            __( 'Plugins and themes are up to date', 'admin-safety-guard' ),
            0 === $outdated,
            'high',
            8,
            sprintf(
                /* translators: %d: number of plugins or themes with updates. */
                _n( '%d plugin or theme has an update waiting. Known vulnerabilities are usually fixed in these.', '%d plugins or themes have updates waiting. Known vulnerabilities are usually fixed in these.', max( 1, $outdated ), 'admin-safety-guard' ),
                $outdated
            ),
            admin_url( 'update-core.php' )
        );

        $checks[] = self::check(
            'no-admin-user',
            __( 'No account named "admin"', 'admin-safety-guard' ),
            !self::has_obvious_username(),
            'high',
            8,
            __( 'Accounts called admin, administrator or test are the first thing every bot tries. Create a new administrator with a different name and delete the old one.', 'admin-safety-guard' ),
            admin_url( 'users.php' )
        );

        $checks[] = self::check(
            'debug-display',
            __( 'Errors are not shown to visitors', 'admin-safety-guard' ),
            !( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ),
            'high',
            6,
            __( 'Displayed PHP errors leak file paths and database details. Set WP_DEBUG_DISPLAY to false in wp-config.php.', 'admin-safety-guard' )
        );

        $checks[] = self::check(
            'file-edit',
            __( 'Theme and plugin editor is disabled', 'admin-safety-guard' ),
            defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT,
            'medium',
            5,
            __( 'The built-in editor turns a stolen admin session into running PHP.', 'admin-safety-guard' ),
            self::settings_url( 'privacy-hardening', 'privacy-hardening' )
        );

        $checks[] = self::check(
            'php-supported',
            __( 'PHP version is still supported', 'admin-safety-guard' ),
            version_compare( PHP_VERSION, '8.1', '>=' ),
            'medium',
            5,
            sprintf(
                /* translators: %s: current PHP version. */
                __( 'This site runs PHP %s, which no longer receives security fixes. Ask your host to upgrade.', 'admin-safety-guard' ),
                PHP_VERSION
            )
        );

        $checks[] = self::check(
            'db-prefix',
            __( 'Database prefix is not the default', 'admin-safety-guard' ),
            'wp_' !== $GLOBALS['wpdb']->base_prefix,
            'low',
            3,
            __( 'The default wp_ prefix makes automated SQL injection easier to write.', 'admin-safety-guard' )
        );

        // --- Plugin protections ----------------------------------------------
        $checks[] = self::check(
            'limit-login',
            __( 'Brute-force protection is on', 'admin-safety-guard' ),
            self::feature_on( 'limit-login-attempts', 'enable' ),
            'critical',
            12,
            __( 'Without a lockout, an attacker can try passwords indefinitely.', 'admin-safety-guard' ),
            self::settings_url( 'security-core', 'limit-login-attempts' )
        );

        $checks[] = self::check(
            'two-factor',
            __( 'Two-factor authentication is on', 'admin-safety-guard' ),
            self::feature_on( 'two-factor-auth', 'otp-email' ),
            'high',
            10,
            __( 'A second factor makes a stolen password useless on its own.', 'admin-safety-guard' ),
            self::settings_url( 'security-core', 'two-factor-auth' )
        );

        $checks[] = self::check(
            'custom-login',
            __( 'Login URL has been moved', 'admin-safety-guard' ),
            self::feature_on( 'custom-login-url', 'enable' ),
            'medium',
            6,
            __( 'Moving the login page off wp-login.php makes most automated attacks miss entirely.', 'admin-safety-guard' ),
            self::settings_url( 'security-core', 'custom-login-url' )
        );

        $checks[] = self::check(
            'session-security',
            __( 'Sessions expire when idle', 'admin-safety-guard' ),
            self::feature_on( 'session-security', 'enable' ),
            'medium',
            5,
            __( 'By default a sign-in stays valid for up to 14 days, however long the screen is left unattended.', 'admin-safety-guard' ),
            self::settings_url( 'security-core', 'session-security' )
        );

        $checks[] = self::check(
            'recaptcha',
            __( 'Bot protection on the login form', 'admin-safety-guard' ),
            self::feature_on( 'recaptcha', 'enable' ),
            'medium',
            5,
            __( 'reCAPTCHA stops scripted sign-in attempts before they reach WordPress.', 'admin-safety-guard' ),
            self::settings_url( 'security-core', 'recaptcha' )
        );

        $checks[] = self::check(
            'user-enumeration',
            __( 'Usernames cannot be discovered', 'admin-safety-guard' ),
            self::feature_on( 'privacy-hardening', 'block-author-enum' ),
            'medium',
            5,
            __( 'Anyone can currently list your usernames via ?author=1 or the REST API, which is the step before a password attack.', 'admin-safety-guard' ),
            self::settings_url( 'privacy-hardening', 'privacy-hardening' )
        );

        $checks[] = self::check(
            'xmlrpc',
            __( 'XML-RPC is disabled', 'admin-safety-guard' ),
            self::feature_on( 'privacy-hardening', 'xml-rpc-enable' ),
            'low',
            4,
            __( 'XML-RPC allows many password guesses in a single request. Leave it on only if you use the mobile app or Jetpack.', 'admin-safety-guard' ),
            self::settings_url( 'privacy-hardening', 'privacy-hardening' )
        );

        $checks[] = self::check(
            'security-headers',
            __( 'Security headers are sent', 'admin-safety-guard' ),
            self::feature_on( 'privacy-hardening', 'security-headers' ),
            'low',
            4,
            __( 'Browser-level protection against clickjacking, MIME sniffing and referrer leaks.', 'admin-safety-guard' ),
            self::settings_url( 'privacy-hardening', 'privacy-hardening' )
        );

        /**
         * Filter the audit checks, so other code can add its own.
         *
         * @since 1.3.0
         *
         * @param array $checks The check list.
         */
        return apply_filters( 'tpsa_security_audit_checks', $checks );
    }

    /* ---------------------------------------------------------------------
     * Admin notice
     * ------------------------------------------------------------------- */

    /**
     * Warn about critical problems, once, dismissibly.
     *
     * @return void
     */
    public function render_critical_notice() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( get_user_meta( get_current_user_id(), self::DISMISS_META, true ) ) {
            return;
        }

        $report = self::report();

        if ( $report['critical'] < 1 ) {
            return;
        }

        $dismiss = wp_nonce_url(
            add_query_arg( 'tpsa_dismiss_audit', '1' ),
            'tpsa_dismiss_audit',
            'tpsa_audit_nonce'
        );

        // Land on the plugin dashboard with the issue dialog already open.
        $review = self::settings_url( 'analytics', 'analytics' ) . '#security-issues';

        $critical = array_values(
            array_filter( $report['issues'], static fn( $i ) => 'critical' === $i['severity'] )
        );
        ?>
<div class="notice tpsa-notice">
    <div class="tpsa-notice__bar" aria-hidden="true"></div>
    <div class="tpsa-notice__body">
        <div class="tpsa-notice__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L4 5v6c0 5.5 3.8 10.7 8 11 4.2-.3 8-5.5 8-11V5l-8-3z" />
                <path d="M12 8v5" />
                <path d="M12 16h.01" />
            </svg>
        </div>

        <div class="tpsa-notice__content">
            <p class="tpsa-notice__title">
                <?php
                printf(
                    /* translators: %d: number of critical issues. */
                    esc_html( _n( '%d critical security issue needs your attention', '%d critical security issues need your attention', $report['critical'], 'admin-safety-guard' ) ),
                    (int) $report['critical']
                );
                ?>
            </p>

            <ul class="tpsa-notice__list">
                <?php foreach ( array_slice( $critical, 0, 3 ) as $issue ) : ?>
                    <li>
                        <span class="tpsa-notice__sev"><?php esc_html_e( 'Critical', 'admin-safety-guard' ); ?></span>
                        <?php echo esc_html( $issue['problem'] ?? $issue['title'] ); ?>
                    </li>
                <?php endforeach; ?>
                <?php if ( count( $critical ) > 3 ) : ?>
                    <li class="tpsa-notice__more">
                        <?php
                        printf(
                            /* translators: %d: number of further issues. */
                            esc_html__( 'and %d more', 'admin-safety-guard' ),
                            count( $critical ) - 3
                        );
                        ?>
                    </li>
                <?php endif; ?>
            </ul>

            <p class="tpsa-notice__actions">
                <a class="tpsa-notice__btn" href="<?php echo esc_url( $review ); ?>">
                    <?php esc_html_e( 'Review and fix', 'admin-safety-guard' ); ?>
                </a>
                <a class="tpsa-notice__dismiss" href="<?php echo esc_url( $dismiss ); ?>">
                    <?php esc_html_e( 'Dismiss', 'admin-safety-guard' ); ?>
                </a>
            </p>
        </div>
    </div>
</div>
        <?php
    }

    /**
     * Remember a dismissal for this user.
     *
     * @return void
     */
    public function handle_dismiss() {
        if ( !isset( $_GET['tpsa_dismiss_audit'] ) ) {
            return;
        }

        $nonce = isset( $_GET['tpsa_audit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['tpsa_audit_nonce'] ) ) : '';

        if ( !wp_verify_nonce( $nonce, 'tpsa_dismiss_audit' ) || !current_user_can( 'manage_options' ) ) {
            return;
        }

        update_user_meta( get_current_user_id(), self::DISMISS_META, time() );

        wp_safe_redirect( remove_query_arg( ['tpsa_dismiss_audit', 'tpsa_audit_nonce'] ) );
        exit;
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    private static function check( $id, $title, $pass, $severity, $weight, $fix = '', $url = '' ) {
        $problems = self::problem_labels();

        return [
            'id'       => $id,
            // What a passing site looks like.
            'title'    => $title,
            // What is actually wrong, for the notice and the issue list. Stating
            // the goal ("Site is served over HTTPS") next to a red cross reads
            // like a pass; stating the fault does not.
            'problem'  => $problems[$id] ?? $title,
            'pass'     => (bool) $pass,
            'severity' => $severity,
            'weight'   => (int) $weight,
            'fix'      => $fix,
            'url'      => $url,
        ];
    }

    /**
     * Negative phrasing for each check.
     *
     * @return array<string, string>
     */
    private static function problem_labels() {
        return [
            'https' => __( 'Site is not using HTTPS', 'admin-safety-guard' ),
            'core-updated' => __( 'WordPress core is out of date', 'admin-safety-guard' ),
            'extensions-updated' => __( 'Plugins or themes need updating', 'admin-safety-guard' ),
            'no-admin-user' => __( 'A guessable admin username exists', 'admin-safety-guard' ),
            'debug-display' => __( 'PHP errors are shown to visitors', 'admin-safety-guard' ),
            'file-edit' => __( 'Theme and plugin editor is enabled', 'admin-safety-guard' ),
            'php-supported' => __( 'PHP version is no longer supported', 'admin-safety-guard' ),
            'db-prefix' => __( 'Database uses the default wp_ prefix', 'admin-safety-guard' ),
            'limit-login' => __( 'Brute-force protection is off', 'admin-safety-guard' ),
            'two-factor' => __( 'Two-factor authentication is off', 'admin-safety-guard' ),
            'custom-login' => __( 'Login URL is still wp-login.php', 'admin-safety-guard' ),
            'session-security' => __( 'Sessions never expire when idle', 'admin-safety-guard' ),
            'recaptcha' => __( 'No bot protection on the login form', 'admin-safety-guard' ),
            'user-enumeration' => __( 'Usernames can be discovered', 'admin-safety-guard' ),
            'xmlrpc' => __( 'XML-RPC is enabled', 'admin-safety-guard' ),
            'security-headers' => __( 'Security headers are not sent', 'admin-safety-guard' ),
        ];
    }

    /**
     * Whether one of the plugin's own switches is on.
     */
    private static function feature_on( $screen, $key ) {
        $settings = get_option( get_tpsa_settings_option_name( $screen ), [] );

        return is_array( $settings ) && !empty( $settings[$key] );
    }

    private static function settings_url( $tab, $screen ) {
        return add_query_arg(
            ['page' => 'tp-admin-safety-guard', 'tab' => $tab, 'tpsa-setting' => $screen],
            admin_url( 'admin.php' )
        );
    }

    private static function core_needs_update() {
        $updates = get_site_transient( 'update_core' );

        if ( !$updates || empty( $updates->updates ) ) {
            return false;
        }

        foreach ( $updates->updates as $update ) {
            if ( isset( $update->response ) && 'upgrade' === $update->response ) {
                return true;
            }
        }

        return false;
    }

    private static function outdated_extensions() {
        $count = 0;

        $plugins = get_site_transient( 'update_plugins' );
        if ( $plugins && !empty( $plugins->response ) ) {
            $count += count( (array) $plugins->response );
        }

        $themes = get_site_transient( 'update_themes' );
        if ( $themes && !empty( $themes->response ) ) {
            $count += count( (array) $themes->response );
        }

        return $count;
    }

    /**
     * Whether an obvious administrator username exists.
     */
    private static function has_obvious_username() {
        foreach ( ['admin', 'administrator', 'root', 'test', 'webmaster'] as $name ) {
            $user = get_user_by( 'login', $name );

            if ( $user && user_can( $user, 'manage_options' ) ) {
                return true;
            }
        }

        return false;
    }

    private static function grade( $score ) {
        if ( $score >= 90 ) { return 'A'; }
        if ( $score >= 75 ) { return 'B'; }
        if ( $score >= 60 ) { return 'C'; }
        if ( $score >= 40 ) { return 'D'; }

        return 'F';
    }

    private static function label( $score ) {
        if ( $score >= 90 ) { return __( 'Excellent protection', 'admin-safety-guard' ); }
        if ( $score >= 75 ) { return __( 'Strong protection', 'admin-safety-guard' ); }
        if ( $score >= 60 ) { return __( 'Moderate protection', 'admin-safety-guard' ); }
        if ( $score >= 40 ) { return __( 'Weak protection', 'admin-safety-guard' ); }

        return __( 'Critical risk', 'admin-safety-guard' );
    }
}

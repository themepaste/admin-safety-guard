<?php
/**
 * Uninstall routine for Admin Safety Guard.
 *
 * Runs when the plugin is deleted from the Plugins screen. The plugin itself is
 * NOT loaded at this point, so nothing here may rely on the plugin's classes,
 * constants or helper functions.
 *
 * @package ThemePaste\SecureAdmin
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Remove every option, table, transient and user meta row for one site.
 *
 * @return void
 */
function tpsa_uninstall_site() {
    global $wpdb;

    // Per-feature settings plus the plugin's own bookkeeping options.
    $options = array(
        'tpsa_analytics_settings',
        'tpsa_admin-bar_settings',
        'tpsa_admin-bar_pro_fields',
        'tpsa_custom-login-url_settings',
        'tpsa_limit-login-attempts_settings',
        'tpsa_login-logs-activity_settings',
        'tpsa_recaptcha_settings',
        'tpsa_two-factor-auth_settings',
        'tpsa_password-protection_settings',
        'tpsa_privacy-hardening_settings',
        'tpsa_customize_settings',
        'tpsa_web-application-firewall_settings',
        'tpsa_social-login_settings',
        'tpsa_table-prefix-check_settings',
        'tpsa_2fa-using-mobile-app_settings',
        'tpsa_version',
        'tpsa_2fa_legacy_otp_purged',
        'tpsm_is_setup_wizard',
        'tp_admin_safety_guard_deactivate_token',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // Catch any settings option added by a filter that is not in the list above.
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'tpsa\_%\_settings'"
    );

    // Transients (table-exists cache, pending OTPs, cached user count, wizard redirect).
    $wpdb->query(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE '\_transient\_tpsa\_%'
            OR option_name LIKE '\_transient\_timeout\_tpsa\_%'
            OR option_name LIKE '\_transient\_tpsm\_%'
            OR option_name LIKE '\_transient\_timeout\_tpsm\_%'"
    );

    // User meta written by the wizard notice and the legacy OTP implementation.
    delete_metadata( 'user', 0, 'tpsm_dismissed_setup_notice', '', true );
    delete_metadata( 'user', 0, '_tpsa_otp_code', '', true );

    // Custom log tables.
    foreach ( array( 's_logins', 'failed_logins', 'block_users' ) as $table ) {
        $name = $wpdb->prefix . 'tpsa_' . $table;
        $wpdb->query( "DROP TABLE IF EXISTS `{$name}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    // Scheduled cleanup event.
    wp_clear_scheduled_hook( 'tpsa_cleanup_logs' );
}

if ( is_multisite() ) {
    $tpsa_site_ids = get_sites(
        array(
            'fields' => 'ids',
            'number' => 0,
        )
    );

    foreach ( $tpsa_site_ids as $tpsa_site_id ) {
        switch_to_blog( $tpsa_site_id );
        tpsa_uninstall_site();
        restore_current_blog();
    }
} else {
    tpsa_uninstall_site();
}

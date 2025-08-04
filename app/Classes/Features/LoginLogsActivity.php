<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: LoginLogsActivity
 *
 * Hides the WordPress admin bar based on plugin settings.
 *
 * @package ThemePaste\SecureAdmin\Classes\Features
 * @since   1.0.0
 */
class LoginLogsActivity implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference and settings screen slug.
     *
     * This ID corresponds to:
     * - The feature key in the `tpsa_settings_fields()` configuration array.
     * - The `tpsa-setting` query parameter in the admin settings screen URL.
     *
     * Example usage:
     * - Settings array: tpsa_settings_fields()['login-logs-activity']
     * - Admin page URL: wp-admin/admin.php?page=tp-secure-admin&tpsa-setting=login-logs-activity
     *
     * @since 1.0.0
     * @var string
     */
    private $features_id = 'login-logs-activity';

    /**
     * Registers the WordPress hooks for the HideAdminBar feature.
     *
     * Hooks the 'init' action to the 'login-logs-activity' method.
     *
     * @since 1.0.0
     *
     * @return void
     */

    public function register_hooks() {
        $this->action( 'wp_login', [$this, 'save_successful_login_log'], 10, 2 );
    }
    
    public function save_successful_login_log( $user_login, $user ) {
        global $wpdb;

        $table      = get_tpsa_db_table_name( 's_logins' );
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $login_time = current_time('mysql');

        // Check if user already exists in the table
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE username = %s LIMIT 1",
                $user_login
            )
        );

        if ( $existing ) {
            // Update existing row: increment login count and update other details
            $wpdb->update(
                $table,
                [
                    'ip_address'  => $ip_address,
                    'user_agent'  => $user_agent,
                    'login_time'  => $login_time,
                    'login_count' => $existing->login_count + 1
                ],
                [ 'username' => $user_login ],
                [ '%s', '%s', '%s', '%d' ],
                [ '%s' ]
            );
        } else {
            // Insert new row for first-time login
            $wpdb->insert(
                $table,
                [
                    'username'    => $user_login,
                    'ip_address'  => $ip_address,
                    'user_agent'  => $user_agent,
                    'login_time'  => $login_time,
                    'login_count' => 1
                ],
                [
                    '%s', '%s', '%s', '%s', '%d'
                ]
            );
        }
    }

}
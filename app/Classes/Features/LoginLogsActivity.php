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
        $this->action( 'wp_login_failed', [$this, 'save_failed_login_log'] );
    }

    public function save_failed_login_log( $username ) {
        global $wpdb;

        $table      = get_tpsa_db_table_name( 'failed_logins' );
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Default values
        $country = 'Unknown';
        $city    = 'Unknown';

        // Optional: use a geolocation service
        $geo_data = $this->get_geo_info( $ip_address );

        if ( $geo_data ) {
            $country = $geo_data['country'] ?? 'Unknown';
            $city    = $geo_data['city'] ?? 'Unknown';
        }

        $wpdb->insert(
            $table,
            [
                'username'   => $username,
                'user_agent' => $user_agent,
                'ip_address' => $ip_address,
                'login_time' => current_time('mysql'),
                'country'    => $country,
                'city'       => $city,
            ],
            [
                '%s', '%s', '%s', '%s', '%s', '%s'
            ]
        );
    }

    private function get_geo_info( $ip ) {
        $url = "http://www.geoplugin.net/json.gp?ip={$ip}";
        $response = file_get_contents($url);
        $data = json_decode($response);

        return [
            'country'   => $data->geoplugin_countryName ?? null,
            'city'      => $data->geoplugin_city ?? null
        ];

        return false;
    }


    public function save_successful_login_log( $user_login, $user ) {
         global $wpdb;

        $table      = get_tpsa_db_table_name( 's_logins' );
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $wpdb->insert(
            $table,
            [
                'id'         => $user->ID,
                'username'   => $user_login,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'login_time' => current_time('mysql')
            ],
            [
                '%d', '%s', '%s', '%s', '%s'
            ]
        );
    }
}
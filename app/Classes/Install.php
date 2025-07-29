<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;

class Install {

    use Hook;
    use Asset;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->activation( [ $this, 'bootstrapping' ] );
    }

    /**
     * Run installation tasks on plugin activation.
     */
    public function bootstrapping() {
        if ( ! $this->is_database_up_to_date() ) {

            $this->create_table(
                's_logins',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(100) NOT NULL,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                login_time DATETIME NOT NULL,
                login_count INT UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
                "
            );

            $this->create_table(
                'failed_logins',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(100) NOT NULL,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                first_login_time DATETIME NOT NULL,
                last_login_time DATETIME NOT NULL,
                login_attempts INT UNSIGNED NOT NULL DEFAULT 1,
                lockouts INT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
                "
            );

            $this->create_table(
                'block_users',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                login_time DATETIME NOT NULL,
                PRIMARY KEY (id)
                "
            );

            $this->update_db_version();
        }
    }

    /**
     * Create a custom database table.
     *
     * @param string $table_name
     * @param string $table_columns
     */
    private function create_table( $table_name, $table_columns ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;

        $full_table_name = get_tpsa_db_table_name( $table_name );
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$full_table_name} ($table_columns) {$charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Check if the database is already up to date.
     *
     * @return bool
     */
    private function is_database_up_to_date() {
        $installed_ver = get_option( 'tpsa_version' );
        return version_compare( $installed_ver, TPSA_PLUGIN_VERSION, '=' );
    }

    /**
     * Update the database version stored in options.
     */
    private function update_db_version() {
        update_option( 'tpsa_version', TPSA_PLUGIN_VERSION );
    }
}

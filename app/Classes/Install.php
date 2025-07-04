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

            // Create table for successful logins data
            $this->create_table(
                's_logins',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(100) NOT NULL,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                login_time DATETIME NOT NULL,
                PRIMARY KEY (id),
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
    public function create_table( $table_name, $table_columns ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;

        $prefix          = $wpdb->prefix . TPSA_PREFIX . '_';
        $full_table_name = esc_sql( $prefix . $table_name );
        $charset_collate = $wpdb->get_charset_collate();

        // Clean trailing comma and whitespace in column string
        $table_columns = trim( rtrim( $table_columns, ', ' ) );

        // Compose SQL safely using prepare for full structure
        $sql_template = "CREATE TABLE `%s` ( %s ) ENGINE=InnoDB $charset_collate;";
        $sql = $wpdb->prepare( $sql_template, $full_table_name, $table_columns );

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

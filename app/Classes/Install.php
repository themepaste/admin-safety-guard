<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Hook;
use ThemePaste\SecureAdmin\Traits\Asset;

class Install {

    use Hook;
    use Asset;

    public function __construct() {
        $this->activation( [$this, 'bootstrapping'] );
    }

    public function bootstrapping() {

        if( ! $this->is_database_up_to_date() ) {

            $this->create_table( 
                'habib', 
                'book_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    title VARCHAR(255) NOT NULL,
                    author VARCHAR(255) NOT NULL,
                    publisher VARCHAR(255) NOT NULL,
                    isbn VARCHAR(20) NOT NULL,
                    publication_date DATE NOT NULL,
                    PRIMARY KEY (book_id)', 
            );

            $this->update_db_version(); 
        }
    }

    
    public function create_table( $table_name, $table_columns ): void {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        global $wpdb;

        $db         = $wpdb;
        $prefix     = $db->prefix . TPSA_PREFIX . '_';
        $table_name = $prefix . $table_name;
        $charset_collate = $db->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} ($table_columns) {$charset_collate};";

        dbDelta($sql);
    }

    
	private function is_database_up_to_date() {
		$installed_ver = get_option( 'tpsa_version' );
		return version_compare( $installed_ver, TPSA_PLUGIN_VERSION, '=' );
	}

    
    private function update_db_version() {
        update_option( 'tpsa_version', TPSA_PLUGIN_VERSION );
    }
}
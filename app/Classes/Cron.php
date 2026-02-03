<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Traits\Hook;

class Cron {

    use Hook;
    use Asset;

    public function __construct() {
        // Register the cron job hook on class initialization
        $this->schedule_cron_event();

        // Hook the function to the cron event
        $this->action( 'remove_old_block_users_data', [$this, 'remove_old_block_users_data_function'] );
    }

    // Function to remove old records
    public function remove_old_block_users_data_function() {
        global $wpdb;

        // Get the timestamp for 24 hours ago
        $time_24_hours_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );
        $block_table = get_tpsa_db_table_name( 'block_users' );

        // Delete records older than 24 hours
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$block_table} WHERE login_time < %s",
                $time_24_hours_ago
            )
        );
    }

    // Schedule the cron event
    private function schedule_cron_event() {
        // Schedule the event if it's not already scheduled
        if ( !wp_next_scheduled( 'remove_old_block_users_data' ) ) {
            wp_schedule_event( time(), 'daily', 'remove_old_block_users_data' );
        }
    }
}
<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;

class FailedLoginsController extends BaseController {
    /**
     * Returns the database table name for failed logins.
     *
     * @return string
     */
    protected function get_table_name(): string {
        return 'failed_logins';
    }

    /**
     * The failed_logins table stores first_login_time / last_login_time
     * rather than a single `login_time` column.
     *
     * @return string[]
     */
    protected function get_searchable_columns(): array {
        return [ 'username', 'user_agent', 'ip_address', 'last_login_time' ];
    }

    /**
     * Returns a paginated list of failed login records.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_data( WP_REST_Request $request ) {
        return $this->get_records( $request, $this->get_table_name() );
    }

    /**
     * Returns the total number of failed login records in the database.
     *
     * @param WP_REST_Request $request Unused; present to match the route callback signature.
     * @return int The total number of failed login records.
     */
    public function get_count( WP_REST_Request $request ) {
        return $this->get_record_count( $this->get_table_name() );
    }
}
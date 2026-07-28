<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;

class BlockUsersController extends BaseController {
    /**
     * Returns the database table name for blocked users.
     *
     * @return string
     */
    protected function get_table_name(): string {
        return 'block_users';
    }

    /**
     * The block_users table has no `username` column.
     *
     * @return string[]
     */
    protected function get_searchable_columns(): array {
        return [ 'user_agent', 'ip_address', 'login_time' ];
    }

    /**
     * @return string
     */
    protected function get_time_column(): string {
        return 'login_time';
    }

    /**
     * Returns a paginated list of blocked users.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_data( WP_REST_Request $request ) {
        return $this->get_records( $request, $this->get_table_name() );
    }
}
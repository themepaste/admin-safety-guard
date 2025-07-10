<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

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
     * Returns a paginated list of failed login records.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_data( WP_REST_Request $request ) {
        return $this->get_records( $request, $this->get_table_name() );
    }
}
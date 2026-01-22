<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;

class SuccessLoginsController extends BaseController {
    /**
     * Returns the database table name for successful logins.
     *
     * @return string
     */
    protected function get_table_name(): string {
        return 's_logins';
    }

    /**
     * Returns a paginated list of successful login records.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_data( WP_REST_Request $request ) {
        return $this->get_records( $request, $this->get_table_name() );
    }
}
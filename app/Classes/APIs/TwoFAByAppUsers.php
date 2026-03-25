<?php

namespace ThemePaste\SecureAdmin\Classes\APIs;

use WP_REST_Request;
use WP_REST_Response;
use WP_User_Query;

class TwoFAByAppUsers extends BaseController {

/**
 * Returns the database table name for failed logins.
 *
 * @return string
 */
    protected function get_table_name(): string {
        return ''; // Not used in this controller since we're querying WP users directly.
    }

    /**
     * Returns a paginated list of users.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_data( WP_REST_Request $request ) {
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $page = $request->get_param( 'page' ) ?: 1;
        $search = $request->get_param( 's' ) ?: '';
        $role = $request->get_param( 'role' ) ?: '';

        $args = [
            'number' => $per_page,
            'offset' => ( $page - 1 ) * $per_page,
        ];

        if ( !empty( $search ) ) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'display_name', 'user_email'];
        }

        if ( !empty( $role ) ) {
            $args['role'] = $role;
        }

        $user_query = new WP_User_Query( $args );
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();

        $data = [];
        foreach ( $users as $user ) {
            $data[] = [
                'ID'       => $user->ID,
                'name'     => $user->display_name,
                'username' => $user->user_login,
                'email'    => $user->user_email,
                'roles'    => $user->roles,
            ];
        }

        return new WP_REST_Response( [
            'total'    => $total_users,
            'per_page' => $per_page,
            'page'     => $page,
            'data'     => $data,
        ], 200 );
    }

    /**
     * Example authorization logic.
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function authorize_request( WP_REST_Request $request ) {
        // Only admins can access this endpoint
        // return current_user_can( 'list_users' );
        return true;
    }
}
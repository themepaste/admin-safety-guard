<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

class FormProcessor {

    public static function process_form() {

        // 1) Capability check first
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized user', 'admin-safety-guard' ) );
        }

        // 2) Nonce verify (sanitize + unslash because wp_verify_nonce is pluggable)
        $nonce = isset( $_POST['tpsa-nonce_name'] )
        ? sanitize_text_field( wp_unslash( $_POST['tpsa-nonce_name'] ) )
        : '';

        if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'tpsa-nonce_action' ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'admin-safety-guard' ) );
        }

        // 3) Screen slug (sanitize_key is better for slugs)
        $screen_slug = isset( $_POST['screen_slug'] )
        ? sanitize_key( wp_unslash( $_POST['screen_slug'] ) )
        : '';

        if ( empty( $screen_slug ) ) {
            wp_die( esc_html__( 'Missing screen_slug.', 'admin-safety-guard' ) );
        }

        // Get settings fields
        $all_fields = tpsa_settings_fields();
        $fields = $all_fields[$screen_slug]['fields'] ?? array();

        // Build sanitized settings data
        $sanitized = array();

        foreach ( $fields as $key => $field ) {

            $key = sanitize_key( $key );
            $field_type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';

            $field_name = get_tpsa_prefix() . $screen_slug . '_' . $key;
            $raw = isset( $_POST[$field_name] ) ? wp_unslash( $_POST[$field_name] ) : null;

            switch ( $field_type ) {

            case 'switch':
                // Checkbox style: present => 1, missing => 0
                $sanitized[$key] = isset( $_POST[$field_name] ) ? 1 : 0;
                break;

            case 'text':
                $sanitized[$key] = is_scalar( $raw ) ? sanitize_text_field( (string) $raw ) : '';
                break;

            case 'number':
                // Support int/float safely
                $num = is_scalar( $raw ) ? filter_var( $raw, FILTER_VALIDATE_FLOAT ) : false;
                $sanitized[$key] = ( $num !== false ) ? $num : 0;
                break;

            case 'multi-check':
            case 'social-login':
                // Expect array
                $arr = ( is_array( $raw ) ) ? $raw : array();
                $sanitized[$key] = array_values( array_map( 'sanitize_text_field', $arr ) );
                break;

            case 'single-repeater':
                // Expect array of strings; keep at least one empty item
                $arr = ( is_array( $raw ) ) ? $raw : array();
                $arr = array_map( 'sanitize_text_field', $arr );
                $arr = array_values( array_filter( $arr, static fn( $v ) => $v !== '' ) );

                $sanitized[$key] = !empty( $arr ) ? $arr : array( '' );
                break;

            case 'login-template':
                /**
                 * This one depends on what you allow:
                 * - If you allow HTML: use wp_kses_post()
                 * - If you allow plain text only: use sanitize_textarea_field()
                 *
                 * Most "template" fields usually allow HTML.
                 */
                $content = is_scalar( $raw ) ? (string) $raw : '';
                $sanitized[$key] = wp_kses_post( $content );
                break;

            default:
                $sanitized[$key] = is_scalar( $raw ) ? sanitize_text_field( (string) $raw ) : '';
                break;
            }
        }

        // Save settings option
        $option_name = get_tpsa_settings_option_name( $screen_slug );
        update_option( $option_name, $sanitized );

        // Save Admin Bar Pro fields
        if ( $screen_slug === 'admin-bar' ) {
            $pro_fields = self::collect_admin_bar_pro_fields_from_post();
            update_option( 'tpsa_admin-bar_pro_fields', $pro_fields, false );
        }

        // Redirect back
        $redirect_url = add_query_arg(
            array(
                'page'           => Settings::$SETTING_PAGE_ID,
                'tpsa-setting'   => $screen_slug,
                'settings-saved' => '1',
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Read + sanitize the Admin Bar Pro fields from $_POST.
     * Returns a normalized array ready to store in tpsa_admin-bar_pro_fields.
     */
    private static function collect_admin_bar_pro_fields_from_post(): array {

        // Scope (multi-checkbox)
        $scope = array();

        if ( isset( $_POST['tpsa_admin-bar_scope'] ) ) {
            $raw_scope = wp_unslash( $_POST['tpsa_admin-bar_scope'] );
            if ( is_array( $raw_scope ) ) {
                $scope = array_map( 'sanitize_text_field', $raw_scope );
            }
        }

        // Normalize scope and fallback safely
        $scope = array_values( array_intersect( $scope, array( 'admin', 'front' ) ) );
        if ( empty( $scope ) ) {
            $scope = array( 'admin', 'front' );
        }

        // Textareas → arrays (one per line)
        $exact_ids = self::clean_lines_to_array( isset( $_POST['tpsa_admin-bar_exact_ids'] ) ? wp_unslash( $_POST['tpsa_admin-bar_exact_ids'] ) : '' );
        $id_prefix = self::clean_lines_to_array( isset( $_POST['tpsa_admin-bar_id_prefix'] ) ? wp_unslash( $_POST['tpsa_admin-bar_id_prefix'] ) : '' );
        $title_contains = self::clean_lines_to_array( isset( $_POST['tpsa_admin-bar_title_contains'] ) ? wp_unslash( $_POST['tpsa_admin-bar_title_contains'] ) : '' );
        $parent_ids = self::clean_lines_to_array( isset( $_POST['tpsa_admin-bar_parent_ids'] ) ? wp_unslash( $_POST['tpsa_admin-bar_parent_ids'] ) : '' );
        $href_contains = self::clean_lines_to_array( isset( $_POST['tpsa_admin-bar_href_contains'] ) ? wp_unslash( $_POST['tpsa_admin-bar_href_contains'] ) : '' );
        $roles = self::clean_lines_to_array( isset( $_POST['tpsa_admin-bar_roles'] ) ? wp_unslash( $_POST['tpsa_admin-bar_roles'] ) : '' );

        return array(
            'scope'          => $scope,
            'exact_ids'      => $exact_ids,
            'id_prefix'      => $id_prefix,
            'title_contains' => $title_contains,
            'parent_ids'     => $parent_ids,
            'href_contains'  => $href_contains,
            'roles'          => $roles,
        );
    }

    /**
     * Turn a textarea payload into a cleaned array of strings (one per line).
     */
    private static function clean_lines_to_array( $text ): array {
        $text = is_scalar( $text ) ? (string) $text : '';

        $lines = preg_split( '/\r\n|\r|\n/', $text );
        if ( !is_array( $lines ) ) {
            return array();
        }

        $lines = array_map( 'trim', $lines );
        $lines = array_filter( $lines, static fn( $v ) => $v !== '' );
        $lines = array_map( 'sanitize_text_field', $lines );

        return array_values( $lines );
    }
}
<?php 

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

class FormProcessor {

    public static function process_form() {

        if ( ! isset( $_POST['tpsa-nonce_name'] ) || ! wp_verify_nonce( $_POST['tpsa-nonce_name'], 'tpsa-nonce_action' ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'tp-secure-plugin' ) );
        }
    
        // Check capabilities if needed
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized user', 'tp-secure-plugin' ) );
        }

        $screen_slug = sanitize_text_field( $_POST['screen_slug'] ?? '' );
        if ( empty( $screen_slug ) ) {
            wp_die( 'Missing screen_slug.' );
        }

        // Get settings fields from a global or helper method
        $all_fields = tpsa_settings_fields();
        $fields     = $all_fields[ $screen_slug ]['fields'] ?? [];

        // Build settings data
        $sanitized = [];

        foreach ( $fields as $key => $field ) {
            $field_name = get_tpsa_prefix() . $screen_slug . '_' . $key;
            $raw        = $_POST[ $field_name ] ?? null;

            // Basic sanitization logic (customize based on field type)
            switch ( $field['type'] ) {
                case 'switch':
                    $sanitized[ $key ] = isset( $raw ) ? 1 : 0;
                    break;
                case 'text':
                    $sanitized[ $key ] = sanitize_text_field( $raw );
                    break;
                case 'login-template':
                    $sanitized[ $key ] = wp_unslash( $raw );
                    break;
                case 'single-repeater':
                    $raw = isset( $_POST[ $field_name ] ) ? (array) $_POST[ $field_name ] : [];
                    // $vals = array_values(array_filter(array_map('sanitize_text_field', $raw), 'strlen'));
                    $sanitized[$key] = $raw ?: [''];
                    break;
                case 'number':
                    // choose one of int/float and validate
                    $num = filter_var( $raw, FILTER_VALIDATE_FLOAT );
                    $sanitized[$key] = ( $num !== false ) ? $num : 0; // or null/default
                    break;
                case 'multi-check':
                case 'social-login':
                    $raw = isset($_POST[$field_name]) ? (array) $_POST[$field_name] : [];
                    $sanitized[$key] = array_map('sanitize_text_field', $raw);
                    break;
                default:
                    $sanitized[ $key ] = sanitize_text_field( $raw );
                    break;
            }
        }

         // Save settings
        $option_name = get_tpsa_settings_option_name( $screen_slug );
        update_option( $option_name, $sanitized );

         /**
         * EXTRA: Save "Pro fields" only when editing the admin bar screen.
         * These fields are NOT part of $fields; they post under their own names.
         * Option key: tpsa_admin-bar_pro_fields
         */
        if ( $screen_slug === 'admin-bar' ) {
            $pro_fields = self::collect_admin_bar_pro_fields_from_post();
            update_option( 'tpsa_admin-bar_pro_fields', $pro_fields, false );
        }

        // Redirect or render message
        wp_redirect( add_query_arg( 
            array(
                'page'          => Settings::$SETTING_PAGE_ID,
                'tpsa-setting'  => $screen_slug,
                'settings-saved' => true
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Read + sanitize the Admin Bar Pro fields from $_POST.
     * Returns a normalized array ready to store in tpsa_admin-bar_pro_fields.
     */
    private static function collect_admin_bar_pro_fields_from_post() : array {
        // Scope (multi-checkbox)
        $scope = isset( $_POST['tpsa_admin-bar_scope'] ) && is_array( $_POST['tpsa_admin-bar_scope'] )
            ? array_map( 'sanitize_text_field', (array) $_POST['tpsa_admin-bar_scope'] )
            : [];

        // Normalize scope and fallback safely
        $scope = array_values( array_intersect( $scope, [ 'admin', 'front' ] ) );
        if ( empty( $scope ) ) {
            $scope = [ 'admin', 'front' ];
        }

        // Textareas → arrays (one per line)
        $exact_ids      = self::clean_lines_to_array( $_POST['tpsa_admin-bar_exact_ids']      ?? '' );
        $id_prefix      = self::clean_lines_to_array( $_POST['tpsa_admin-bar_id_prefix']      ?? '' );
        $title_contains = self::clean_lines_to_array( $_POST['tpsa_admin-bar_title_contains'] ?? '' );
        $parent_ids     = self::clean_lines_to_array( $_POST['tpsa_admin-bar_parent_ids']     ?? '' );
        $href_contains  = self::clean_lines_to_array( $_POST['tpsa_admin-bar_href_contains']  ?? '' );
        $roles          = self::clean_lines_to_array( $_POST['tpsa_admin-bar_roles']          ?? '' );

        // Return normalized structure
        return [
            'scope'          => $scope,           // ['admin','front']
            'exact_ids'      => $exact_ids,       // []
            'id_prefix'      => $id_prefix,       // []
            'title_contains' => $title_contains,  // []
            'parent_ids'     => $parent_ids,      // []
            'href_contains'  => $href_contains,   // []
            'roles'          => $roles,           // []
        ];
    }

    /**
     * Turn a textarea payload into a cleaned array of strings (one per line).
     */
    private static function clean_lines_to_array( $text ) : array {
        // Allow only safe scalar
        $text = is_scalar( $text ) ? (string) $text : '';
        // Normalize line endings and split
        $lines = preg_split( '/\r\n|\r|\n/', $text );
        if ( ! is_array( $lines ) ) {
            return [];
        }
        // Trim, sanitize, drop empties
        $lines = array_map( 'trim', $lines );
        $lines = array_filter( $lines, static fn( $v ) => $v !== '' );

        // Final sanitization
        $lines = array_map( 'sanitize_text_field', $lines );

        return array_values( $lines );
    }
}

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
                case 'multi-check':
                    $raw = isset( $_POST[ $field_name ] ) ? (array) $_POST[ $field_name ] : [];
                    $sanitized[ $key ] = array_map( 'sanitize_text_field', $raw );
                    break;
                default:
                    $sanitized[ $key ] = sanitize_text_field( $raw );
                    break;
            }
        }

         // Save settings
        $option_name = get_tpsa_settings_option_name( $screen_slug );
        update_option( $option_name, $sanitized );

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
}

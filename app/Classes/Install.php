<?php

namespace ThemePaste\SecureAdmin\Classes;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Traits\Asset;
use ThemePaste\SecureAdmin\Traits\Hook;

class Install {

    use Hook;
    use Asset;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->activation( [$this, 'bootstrapping'] );
        $this->activation( [$this, 'send_deactivation_email'] );
        $this->action( 'admin_post_admin_safety_guard_deactivate', [$this, 'handle_deactivate'] );
    }

    /**
     * Run installation tasks on plugin activation.
     */
    public function bootstrapping() {
        if ( !$this->is_database_up_to_date() ) {

            set_transient( 'tpsm_do_activation_redirect', true, 30 );

            $this->create_table(
                's_logins',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(100) NOT NULL,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                login_time DATETIME NOT NULL,
                login_count INT UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
                "
            );

            $this->create_table(
                'failed_logins',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(100) NOT NULL,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                first_login_time DATETIME NOT NULL,
                last_login_time DATETIME NOT NULL,
                login_attempts INT UNSIGNED NOT NULL DEFAULT 1,
                lockouts INT UNSIGNED NOT NULL DEFAULT 0,
                lockout_time DATETIME DEFAULT NULL,
                PRIMARY KEY (id)
                "
            );

            $this->create_table(
                'block_users',
                "
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_agent TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                login_time DATETIME NOT NULL,
                PRIMARY KEY (id)
                "
            );

            $this->update_db_version();
        }
    }

    /**
     * Send deactivation email.
     */
    public function send_deactivation_email() {

        if ( $this->is_database_up_to_date() ) {
            return;
        }

        $admin_email = get_option( 'admin_email' );
        if ( !is_email( $admin_email ) ) {
            return;
        }

        $site_name = get_bloginfo( 'name' );
        $site_url = home_url();
        $plugin_name = 'Admin Safety Guard';

        // Generate a secure token for one-click deactivate link
        $token = wp_generate_password( 32, false );
        update_option( 'tp_admin_safety_guard_deactivate_token', $token );

        $deactivate_url = add_query_arg(
            array(
                'action' => 'admin_safety_guard_deactivate',
                'token'  => $token,
            ),
            admin_url( 'admin-post.php' )
        );

        $subject = sprintf(
            '[%s] %s has been activated',
            $site_name,
            $plugin_name
        );

        $message = "Hi,\n\n";
        $message .= "$plugin_name has just been installed and activated on your site:\n";
        $message .= "$site_name ($site_url)\n\n";

        $message .= "If you feel that this plugin does not work properly, breaks your site,\n";
        $message .= "or you simply want to deactivate it, you have a backup option.\n\n";

        $message .= "👉 One-click safe deactivate link (you must be logged in as an admin):\n";
        $message .= $deactivate_url . "\n\n";

        $message .= "Alternatively, you can deactivate it manually:\n";
        $message .= "Dashboard → Plugins → Installed Plugins → \"$plugin_name\" → Deactivate\n\n";

        $message .= "Best regards,\n";
        $message .= "$plugin_name\n";

    }

    public function handle_deactivate() {
        auth_redirect();

        // Only allow admins with plugin activation capability
        if ( !current_user_can( 'activate_plugins' ) ) {
            wp_die( __( 'You do not have permission to deactivate plugins.', 'tp-secure-admin' ) );
        }

        $stored_token = get_option( 'tp_admin_safety_guard_deactivate_token' );
        $token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

        // Check token
        if ( empty( $stored_token ) || empty( $token ) || !hash_equals( $stored_token, $token ) ) {
            wp_die( __( 'Invalid or expired deactivation link.', 'tp-secure-admin' ) );
        }

        // Token is valid, so delete it to avoid unlimited re-use (optional but recommended)
        delete_option( 'tp_admin_safety_guard_deactivate_token' );

        // Deactivate this plugin
        deactivate_plugins( TPSA_PLUGIN_BASENAME );

        // Redirect back to plugins page with a notice
        $redirect_url = add_query_arg(
            array(
                'tp_secure_admin_deactivated' => 1,
            ),
            admin_url( 'plugins.php' )
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Create a custom database table.
     *
     * @param string $table_name
     * @param string $table_columns
     */
    private function create_table( $table_name, $table_columns ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;

        $full_table_name = get_tpsa_db_table_name( $table_name );
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$full_table_name} ($table_columns) {$charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Check if the database is already up to date.
     *
     * @return bool
     */
    private function is_database_up_to_date() {
        $installed_ver = get_option( 'tpsa_version' );
        return version_compare( $installed_ver, TPSA_PLUGIN_VERSION, '=' );
    }

    /**
     * Update the database version stored in options.
     */
    private function update_db_version() {
        update_option( 'tpsa_version', TPSA_PLUGIN_VERSION );
    }
}
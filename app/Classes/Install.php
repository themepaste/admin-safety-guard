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

        if ( !$this->is_database_up_to_date() ) {
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

        // Build HTML email body
        $message = '
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>' . esc_html( $plugin_name ) . ' Activated</title>
    <style>
        /* Basic mobile-friendly styles – some clients ignore <style>,
           so key layout is still inline */
        @media only screen and (max-width: 600px) {
            .tp-container {
                padding: 16px !important;
            }
            .tp-card {
                padding: 20px !important;
            }
            .tp-title {
                font-size: 20px !important;
            }
            .tp-text {
                font-size: 14px !important;
            }
            .tp-button {
                font-size: 14px !important;
                padding: 10px 18px !important;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#f4f5fb; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f5fb; padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" class="tp-container" style="max-width:600px; padding:0 24px;">
                <tr>
                    <td align="center" style="padding-bottom:16px;">
                        <div style="font-size:12px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.08em;">
                            WordPress Security Notification
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" class="tp-card" style="background:#ffffff; border-radius:16px; padding:28px 32px; box-shadow:0 10px 25px rgba(15,23,42,0.08);">
                            <tr>
                                <td align="left" style="padding-bottom:18px;">
                                    <div class="tp-title" style="font-size:22px; font-weight:700; color:#111827;">
                                        ' . esc_html( $plugin_name ) . ' is now active on your site.
                                    </div>
                                    <div class="tp-text" style="margin-top:6px; font-size:14px; color:#6b7280;">
                                        ' . esc_html( $site_name ) . ' (' . esc_html( $site_url ) . ')
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding-bottom:16px;">
                                    <div class="tp-text" style="font-size:14px; line-height:1.7; color:#4b5563;">
                                        Hi,
                                        <br><br>
                                        <strong>' . esc_html( $plugin_name ) . '</strong> has just been installed and activated on your website.
                                        We&apos;re here to help keep your site more secure and stable.
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding-bottom:20px;">
                                    <div class="tp-text" style="font-size:14px; line-height:1.7; color:#4b5563;">
                                        If you ever feel that this plugin does not work properly, causes layout issues, or you simply want to turn it off, you have a safe fallback:
                                    </div>
                                </td>
                            </tr>

                            <!-- Primary Button -->
                            <tr>
                                <td align="center" style="padding-bottom:20px;">
                                    <a href="' . esc_url( $deactivate_url ) . '" class="tp-button"
                                       style="
       display:inline-block;
       padding:12px 24px;
       border-radius:999px;
       background: linear-gradient(90deg, #814bfe, #9c5bff);
       color:#ffffff;
       font-size:15px;
       font-weight:600;
       text-decoration:none;
       box-shadow:0 8px 18px rgba(129, 75, 254, 0.35);
   ">
                                        Safe Deactivate Plugin
                                    </a>
                                </td>
                            </tr>

                            <!-- Fallback link -->
                            <tr>
                                <td style="padding-bottom:24px;">
                                    <div class="tp-text" style="font-size:12px; line-height:1.6; color:#9ca3af; text-align:center;">
                                        If the button doesn&apos;t work, copy &amp; paste this link into your browser (you must be logged in as an admin):
                                        <br>
                                        <a href="' . esc_url( $deactivate_url ) . '" style="color:#6366f1; text-decoration:underline; word-break:break-all;">
                                            ' . esc_html( $deactivate_url ) . '
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Manual steps -->
                            <tr>
                                <td style="padding-bottom:16px;">
                                    <div class="tp-text" style="font-size:14px; line-height:1.7; color:#4b5563;">
                                        <strong>Manual deactivate (alternative):</strong><br>
                                        Dashboard → <strong>Plugins</strong> → <strong>Installed Plugins</strong> → "<strong>' . esc_html( $plugin_name ) . '</strong>" → <strong>Deactivate</strong>
                                    </div>
                                </td>
                            </tr>

                            <!-- Info box -->
                            <tr>
                                <td>
                                    <div style="border-radius:12px; background:#f9fafb; border:1px solid #e5e7eb; padding:12px 14px; font-size:12px; color:#6b7280; line-height:1.6;">
                                        💡 <strong>Tip:</strong> For maximum safety, always keep a recent backup of your files and database before making major changes to security settings or installing new plugins.
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding-top:12px;">
                        <div style="font-size:11px; color:#9ca3af;">
                            Sent from ' . esc_html( $plugin_name ) . ' • ' . esc_html( $site_name ) . '
                        </div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
';

        $headers = array();
        $from_name = $plugin_name;
        $from_email = 'no-reply@' . wp_parse_url( home_url(), PHP_URL_HOST );
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        wp_mail( $admin_email, $subject, $message, $headers );

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
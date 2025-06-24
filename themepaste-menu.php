<?php
function wptpsa_menu_items()
{
    add_menu_page('Themepaste Secure Admin', 'Themepaste Secure Admin', 'manage_options', 'wptpsa-page', 'wptpsa_main_page', 'dashicons-admin-generic', 99);
    add_submenu_page("wptpsa-page", "Custom Layout", "Custom Layout", "manage_options", "wptpsa-layout", "wptpsa_layout_page"); 

    add_submenu_page("wptpsa-page", "Email Activation", "Email Activation", "manage_options", "wptpsa-email-template", "wptpsa_email_page");

    add_submenu_page("wptpsa-page", "Captcha Activation", "Captcha Activation", "manage_options", "wptpsa-captcha", "wptpsa_captcha_page");

    add_submenu_page("wptpsa-page", "Login Attempts", "Login Attempts", "manage_options", "wptpsa-login-attempts", "wptpsa_login_attempts_page");

    add_submenu_page("wptpsa-page", "User Role Manager", "User Role Manager", "manage_options", "wptpsa-user-role-manager", "wptpsa_user_role_manager_page");

    add_submenu_page("wptpsa-page", "Settings", "Settings", "manage_options", "wptpsa-settings", "wptpsa_settings_page");
} 

add_action("admin_menu", "wptpsa_menu_items");
?>
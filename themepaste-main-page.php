<?php

function wptpsa_get_email_config($index){
    global $wptpsa_email_config;
    $option = get_option('wptpsa_email_config');
    if(!empty($option)) {
    $output = isset($option[$index]) && !empty($option[$index]) ? $option[$index] : $wptpsa_email_config[$index];
    return $output;
    }
    else
    {
    $output = '';    
    return $output;    
    }
}


// Main Page Section
function wptpsa_main_page() {

    global $wptpsa;

    if( isset($_POST["wptpsa_config"]) && is_array($_POST["wptpsa_config"]) ) {
        $post_wptpsa_config = WPTPSABase::sanitize_array_text_field($_POST["wptpsa_config"], true);
        wptpsa_update_options($post_wptpsa_config);
        $wptpsa->message("Custom URL updated successfully.");
    }
?>
    <div class="wrap">
        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>
        <form method="post" action="" enctype="multipart/form-data">
            <?php
                settings_fields("section");
                do_settings_sections("wptpsa-manager");      
                submit_button(); 
            ?>          
        </form>
    </div>
<?php     

}



// Updation options
function wptpsa_update_options($input) {

    global $wp_rewrite;
    
    $options = get_option('wptpsa_config');
    
    if(!is_array($options)) {
        $options = array();
    }


    // Seding email to site admin when the wp-admin changed
    $previous_login = isset($options['login']) ? $options['login'] : '';
    $current_login = ltrim($input['login'], "/");
    $previous_login = ltrim($previous_login, "/");
    if( $previous_login != $current_login ){
        $user = wp_get_current_user();
        $eTemp = new WPTPSAEmailTemplate();
        $eTemp->welcome = str_replace('{$name}', $user->display_name, wptpsa_get_email_config('welcome') );
        $eTemp->thanks = wptpsa_get_email_config('thanks');
        $eTemp->copyright = wptpsa_get_email_config('copyright');
        $eTemp->set( 'Changed the admin login' );
        $wp_login_url = !empty($current_login) ? site_url('/').$current_login : site_url().'/wp-admin';
        $eTemp->set( 'New admin login URL: '.$wp_login_url );
        $message = $eTemp->get_message();
        $headers = $eTemp->get_headers();
        wp_mail( $user->user_email, 'Changed Admin URL', $message, $headers );
   }

    
    $params = array('login', 'activation', 'register', 'lostpassword', 'logout',
        "redirect_login", "redirect_logout"
    );
    
    foreach($params as $action) {
        $value = trim($input[$action]);
        if(!empty($value)) {
            $options[$action] = "/".ltrim($value, "/");
        } else {
            $options[$action] = null;
        }
    }

    update_option("wptpsa_config", $options);

    $wp_rewrite->flush_rules();
}



// show the value without slash '/'
function wptpsa_remove_before_slash($value){
    return ltrim($value, "/");
}


// Configuration all fields
function wptpsa_login_url_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_login_url' name='wptpsa_config[login]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["login"])) ?>' placeholder="wp-login.php" />
    <?php
}

function wptpsa_activation_url_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_activation_url' name='wptpsa_config[activation]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["activation"])) ?>' placeholder="wp-login.php?action=activation" /> <br><span>(For this you must checked activation option from <a href="<?php echo WPTPSA_EMAIL_URL;?>">Email Activation</a>.)</span>
    <?php
}

function wptpsa_register_url_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_register_url' name='wptpsa_config[register]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["register"])) ?>' placeholder="/wp-login.php?action=register" />
    <?php
}

function wptpsa_lostpassword_url_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_lostpassword_url' name='wptpsa_config[lostpassword]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["lostpassword"])) ?>' placeholder="wp-login.php?action=lostpassword" />
    <?php
}

function wptpsa_logout_url_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_logout_url' name='wptpsa_config[logout]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["logout"])) ?>' placeholder="wp-login.php?action=logout" />
    <?php
}

function wptpsa_login_redirect_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_login_redirect' name='wptpsa_config[redirect_login]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["redirect_login"])) ?>' placeholder="wp-admin/" />
    <?php
}

function wptpsa_logout_redirect_input() {
    $options = get_option('wptpsa_config');
    ?>
        <code><?php esc_html_e(site_url('/')) ?></code>
        <input id='wptpsa_logout_redirect' name='wptpsa_config[redirect_logout]' size='40' type='text' value='<?php esc_attr_e(wptpsa_remove_before_slash($options["redirect_logout"])) ?>' placeholder="" />
    <?php
}

// Registration all fields    
function wptpsa_main_page_fields() {

    add_settings_section("section", "", null, "wptpsa-manager");
    add_settings_field('wptpsa_login_url', 'Admin Login URL', 'wptpsa_login_url_input', 'wptpsa-manager', 'section');
    add_settings_field('wptpsa_activation_url', 'Admin Login Activation URL', 'wptpsa_activation_url_input', 'wptpsa-manager', 'section');
    add_settings_field('wptpsa_register_url', 'Registration URL', 'wptpsa_register_url_input', 'wptpsa-manager', 'section');
    add_settings_field('wptpsa_lostpassword_url', 'Lost Password URL', 'wptpsa_lostpassword_url_input', 'wptpsa-manager', 'section');
    add_settings_field('wptpsa_logout_url', 'Logout URL', 'wptpsa_logout_url_input', 'wptpsa-manager', 'section');
    
    add_settings_field('wptpsa_login_redirect', 'Login Redirect URL', 'wptpsa_login_redirect_input', 'wptpsa-manager', 'section');
    add_settings_field('wptpsa_logout_redirect', 'Logout Redirect URL', 'wptpsa_logout_redirect_input', 'wptpsa-manager', 'section');


    register_setting("section", "wptpsa_login_url");
    register_setting("section", "wptpsa_activation_url");
    register_setting("section", "wptpsa_register_url");
    register_setting("section", "wptpsa_lostpassword_url");
    register_setting("section", "wptpsa_logout_url");
    register_setting("section", "wptpsa_login_redirect");
    register_setting("section", "wptpsa_logout_redirect");
}
add_action("admin_init", "wptpsa_main_page_fields");


// set logout url
function wptpsa_logout_page( $logout_url, $redirect ) {
    $logout_url = wptpsa_logout_url('');
    if ( preg_match('/\?/', $logout_url) )
        return home_url( $logout_url.'&redirect_to=' . home_url() );
    else
        return home_url( $logout_url.'?redirect_to=' . home_url() );
}

add_filter( 'logout_url', 'wptpsa_logout_page', 10, 2 );

// set lost password url
function wptpsa_lost_password_page( $lostpassword_url, $redirect ) {
    $custom_urls = get_option('wptpsa_config');
    $url = empty( $custom_urls['lostpassword'] ) ? '/wp-login.php?action=lostpassword&' : $custom_urls['lostpassword'].'?';
    return home_url( $url.'redirect_to=' . home_url() );
}

add_filter( 'lostpassword_url', 'wptpsa_lost_password_page', 99, 2 );
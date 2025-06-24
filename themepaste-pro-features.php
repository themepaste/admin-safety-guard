<?php
function wptpsa_free_version_notification() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Notice! Themepaste Secure Admin <a style="color:green;" class="pro_button" href="'.WPTPSA_PRO_PACKAGE_URL.'">Get Pro Version</a>', 'themepaste' ); ?></p>
    </div>
    <?php
}

if ( WPTPSA_PACKAGE=='FREE' )
add_action( 'admin_notices', 'wptpsa_free_version_notification' );


function wptpsa_pro_message(){
    if ( WPTPSA_PACKAGE=='FREE' ){
		echo '<p style="color:red; font-size:20px; margin:5px 0;">This features is available in PRO version. <a style="color:green; font-size:20px;" class="pro_button" href="'.WPTPSA_PRO_PACKAGE_URL.'">Get Pro Version</a></p>';
    }
    if ( WPTPSA_PACKAGE=='PRO' ){
        echo '<p style="color:#FFBA00; font-size:20px; margin:5px 0;">Please put the licence no. <a style="color:green; font-size:20px;" class="pro_button" href="'.WPTPSA_SETTING_URL.'">Active</a></p>';
    }
}


// Captcha Main Page Section
function wptpsa_captcha_page() {
    global $wptpsa;
?>
    <div class="wrap">
        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>
        <?php wptpsa_pro_message();?>
        <div class="pro-features">
        	<img src="<?php echo WPTPSA_PLUGIN_PATH;?>/images/login_captcha.jpg">
        </div>
    </div>
<?php     

}


// Email Page Section
function wptpsa_email_page() {
    
    global $wptpsa, $wptpsa_email_config;
?>
    <div class="wrap">
        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>            
        <?php wptpsa_pro_message();?>
        <div class="pro-features">
        	<img src="<?php echo WPTPSA_PLUGIN_PATH;?>/images/email_activation.jpg">
        </div>
        
    </div>
<?php     

}


// Captcha Main Page Section
function wptpsa_login_attempts_page() {
    global $wptpsa, $wpdb;
?>
    <div class="wrap">
        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>
        <?php wptpsa_pro_message();?>
        <div class="pro-features">
        	<img src="<?php echo WPTPSA_PLUGIN_PATH;?>/images/login_attempts.jpg">
        </div>
    </div>
<?php     

}



// User Role Manager Section
function wptpsa_user_role_manager_page() {
    
    global $wptpsa, $wptpsa_email_config;
?>
    <div class="wrap">

        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>
        <?php wptpsa_pro_message();?>
        <div class="pro-features">
        	<img src="<?php echo WPTPSA_PLUGIN_PATH;?>/images/role_manager.jpg">
        </div>            
    </div>
<?php     

}
?>
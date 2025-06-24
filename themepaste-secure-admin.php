<?php
/*
Plugin Name: Themepaste Secure Admin
Plugin URI: http://themepaste.com/product/themepaste-secure-admin-pro/
Description: This is Themepaste Secure Admin Manager Plugin for wordpress website to customize the wp-admin url, layout etc.
Version: 1.1
Author: Themepaste Team
Author URI: http://themepaste.com/
License: GPL2 or Later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: themepaste
*/

ini_set('display_errors','Off');

# VERSION CONFIG
$wptpsa_version = '1.1';
defined('WPTPSA_REMOTE') or define('WPTPSA_REMOTE', 'http://themepaste.com/' ); 
defined('WPTPSA_PACKAGE') or define('WPTPSA_PACKAGE', 'FREE' ); 
defined('WPTPSA_SLUG') or define('WPTPSA_SLUG', 'themepaste-secure-admin' );
 
# CONFIG
defined('WPTPSA_DIR') or define('WPTPSA_DIR', __dir__ ); 
defined('WPTPSA_PLUGIN_PATH') or define('WPTPSA_PLUGIN_PATH', plugins_url(WPTPSA_SLUG) ); 
defined('WPTPSA_PRO_PACKAGE_URL') or define('WPTPSA_PRO_PACKAGE_URL', WPTPSA_REMOTE.'product/themepaste-secure-admin-pro/' ); 
$wptpsa_master_status = get_option('wptpsa_master_status');
$wptpsa_master_status = empty($wptpsa_master_status) ? 'active' : $wptpsa_master_status;
defined('WPTPSA_MASTER_STATUS') or define('WPTPSA_MASTER_STATUS', $wptpsa_master_status ); 
defined('WPTPSA_LICENCE_URL') or define('WPTPSA_LICENCE_URL', WPTPSA_REMOTE.'plugin-manager/index.php?action=LicenceConfirm&plugin='.WPTPSA_SLUG ); 
defined('WPTPSA_TABLE_URL') or define('WPTPSA_TABLE_URL', WPTPSA_REMOTE.'plugin-manager/index.php?action=TableData&plugin='.WPTPSA_SLUG ); 
$wptpsa_licence_status = get_option('wptpsa_licence_status');
$wptpsa_licence_status = empty($wptpsa_licence_status) ? 'inactive' : $wptpsa_licence_status;
defined('WPTPSA_LICENCE_STATUS') or define('WPTPSA_LICENCE_STATUS', $wptpsa_licence_status ); 

// Pages List
defined('WPTPSA_MAIN_URL') or define('WPTPSA_MAIN_URL', 'admin.php?page=wptpsa-page');
defined('WPTPSA_LAYOUT_URL') or define('WPTPSA_LAYOUT_URL', 'admin.php?page=wptpsa-layout');
defined('WPTPSA_CAPTCHA_URL') or define('WPTPSA_CAPTCHA_URL', 'admin.php?page=wptpsa-captcha');
defined('WPTPSA_EMAIL_URL') or define('WPTPSA_EMAIL_URL', 'admin.php?page=wptpsa-email-template');
defined('WPTPSA_ACTIVATION_URL') or define('WPTPSA_ACTIVATION_URL', 'wp-login.php?action=activation');
defined('WPTPSA_LOGIN_ATTEMPTS_URL') or define('WPTPSA_LOGIN_ATTEMPTS_URL', 'admin.php?page=wptpsa-login-attempts');
defined('WPTPSA_USER_ROLE_URL') or define('WPTPSA_USER_ROLE_URL', 'admin.php?page=wptpsa-user-role-manager');
defined('WPTPSA_SETTING_URL') or define('WPTPSA_SETTING_URL', 'admin.php?page=wptpsa-settings');

require_once(WPTPSA_DIR.'/classes/WPTPSABase.php');
require_once(WPTPSA_DIR.'/classes/WPTPSAEmailTemplate.php');
require_once(WPTPSA_DIR.'/themepaste-functions.php');
require_once(WPTPSA_DIR.'/themepaste-tabs.php');
require_once(WPTPSA_DIR.'/themepaste-install.php');
require_once(WPTPSA_DIR.'/themepaste-menu.php');
require_once(WPTPSA_DIR.'/themepaste-main-page.php');
require_once(WPTPSA_DIR.'/themepaste-settings.php');
require_once(WPTPSA_DIR.'/themepaste-custom-layout.php');
require_once(WPTPSA_DIR.'/templates/master_status.php');

// Premium Version
if ( is_dir(WPTPSA_DIR.'/pro') && file_exists(WPTPSA_DIR.'/pro/pro-themepaste-config.php') && WPTPSA_PACKAGE=='PRO' && WPTPSA_LICENCE_STATUS == 'active' ){
	require_once(WPTPSA_DIR.'/pro/pro-themepaste-config.php');
} else {
	require_once(WPTPSA_DIR.'/themepaste-pro-features.php');
}

register_activation_hook( __FILE__, array( 'WPTPSABase', 'install' ) );
register_deactivation_hook( __FILE__, array( 'WPTPSABase', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'WPTPSABase', 'uninstall' ) );

if ( WPTPSA_MASTER_STATUS == 'active' ){
	add_action('init', 'wptpsa_init_urls');
	add_action('init', 'wptpsa_init_redirect');
}
add_action('generate_rewrite_rules', 'wptpsa_generate_rewrite_rules');


// this is for include at front end
function wptpsa_frontend_script()
{
	global $wptpsa_version;

    // Register the style like this for a plugin:
    wp_register_style( 'wptpsa_fontend_style', plugins_url( '/css/wptpsa-frontend.css', __FILE__ ), array(), $wptpsa_version, 'all' );

    wp_enqueue_style( 'wptpsa_fontend_style' );	 

}
add_action( 'wp_enqueue_scripts', 'wptpsa_frontend_script' );


add_action('admin_init', 'wptpsa_backend_script');
function wptpsa_backend_script() {
	global $wptpsa_version;
    wp_enqueue_script('wptpsa-js', plugins_url( '/js/wptpsa.js?v='.$wptpsa_version, __FILE__ ));
}


add_action('admin_head', 'wptpsa_backend_style');
function wptpsa_backend_style() {
	global $wptpsa_version;
  echo '<link rel="stylesheet" href="'.plugins_url( '/css/wptpsa.css?v='.$wptpsa_version, __FILE__ ).'" type="text/css" media="all" />';
}

function wptpsa_bootstrap(){
	global $wptpsa_version;
	echo '<link rel="stylesheet" href="'.plugins_url( '/js/bootstrap/bootstrap.min.css?v='.$wptpsa_version, __FILE__ ).'" type="text/css" media="all" />';
	echo '<script src="'.plugins_url( '/js/bootstrap/bootstrap.min.js?v='.$wptpsa_version, __FILE__ ) . '"></script>';
	echo '<script src="'.plugins_url( '/js/jscolor.js?v='.$wptpsa_version, __FILE__ ) . '"></script>';
}


function wptpsa_datatable(){
	global $wptpsa_version;
	echo '<link rel="stylesheet" href="'.plugins_url( '/js/datatable/jquery.dataTables.min.css?v='.$wptpsa_version, __FILE__ ) . '" type="text/css" media="all" />';
	echo '<script src="'.plugins_url( '/js/datatable/jquery.dataTables.min.js?v='.$wptpsa_version, __FILE__ ) . '"></script>';
}


// UPLOAD ENGINE
function wptpsa_load_media_files() {
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'wptpsa_load_media_files' );


// checking the login status
add_action( 'wptpsa_check_login_status', 'wptpsa_check_login_status' );

function wptpsa_check_login_status() {
	include_once ( ABSPATH . 'wp-includes/pluggable.php' );
	if ( is_user_logged_in() ) {
	    //nothing
	} else {
	    // checking url
	    $custom_urls = get_option('wptpsa_config');
	    $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'login';
	    //echo $action;exit;
	    if( !empty($custom_urls) && !empty($custom_urls[$action]) ){
		    if ( preg_match('/(wp-admin|wp-login)/', $_SERVER['REQUEST_URI']) ) {
		    	header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".home_url('404'));
				exit();
		    }
		}
	}
}


if (WPTPSA_MASTER_STATUS == 'active'){
	do_action('wptpsa_check_login_status');
}


// Settings List in Plugin List
function wptpsa_plugin_extra_links( $links ) {
    $settings_link = '<a href="admin.php?page=wptpsa-settings">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
    $more_link = '<a target="_blank" href="http://themepaste.com">' . __( 'Details' ) . '</a>';
    array_push( $links, $more_link );
  	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wptpsa_plugin_extra_links' );

?>
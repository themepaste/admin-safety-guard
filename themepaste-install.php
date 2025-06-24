<?php 
global $wptpsa_version, $wpdb;

function wptpsa_plugin_upgrade(){
    global $wptpsa_version;
    update_option('wptpsa_version', $wptpsa_version);
}

function wptpsa_plugin_check() {
    global $wptpsa_version;
    if ( get_site_option( 'wptpsa_version' ) != $wptpsa_version ) {
        wptpsa_plugin_upgrade();
    }
}

add_action( 'plugins_loaded', 'wptpsa_plugin_check' );


function wptpsa_install() {
    global $wpdb, $wp_rewrite;
    global $wptpsa_version;
    $wp_rewrite->flush_rules();

    update_option('wptpsa_version', $wptpsa_version);

}

function wptpsa_deactivate(){
    global $wp_rewrite;
    delete_option("wptpsa_config");
    remove_action('generate_rewrite_rules', 'wptpsa_generate_rewrite_rules');
    $wp_rewrite->flush_rules();
}


function wptpsa_uninstall(){
	global $wpdb, $wptpsa_version, $wp_rewrite;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    delete_option("wptpsa_config");
    delete_option("wptpsa_version");
    remove_action('generate_rewrite_rules', 'wptpsa_generate_rewrite_rules');
    $wp_rewrite->flush_rules();
}

?>
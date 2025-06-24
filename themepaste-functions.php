<?php
// To get the custom urls    
function wptpsa_url($url = null) {
    $wptpsa_url = array(
        'lostpassword' => null,
        'register' => null,
        'logout' => null,
        'login' => null,
        'activation' => null
    );
    
    $config = get_option("wptpsa_config");
    if(is_array($config)) {
        $wptpsa_url = $config;
    }
    
    $wptpsa_url = apply_filters("wptpsa_url", $wptpsa_url);

    if($url === null) {
        return $wptpsa_url;
    } elseif(isset($wptpsa_url[$url]) && $wptpsa_url[$url]) {
        return $wptpsa_url[$url];
    } else {
        return false;
    }
}


// url sorting method
function wptpsa_sort($a, $b) {
    if(strlen($a) < strlen($b)) {
        return 1;
    } else {
        return -1;
    }
}


// Setting up the filters
function wptpsa_init_urls() {
    foreach(wptpsa_url() as $k => $rewrite) {
        if(!is_null($rewrite)) {
            add_filter($k."_url", "wptpsa_".$k."_url");
        }
    }
    
    if(wptpsa_url("redirect_login")) {
        add_filter("login_redirect", "wptpsa_login_redirect");
    }
    
    add_filter("site_url", "wptpsa_site_url", 10, 3);
    add_filter("wp_redirect", "wptpsa_wp_redirect", 10, 2);
}



// Filter Functions
function wptpsa_login_redirect($url) {
    $redirect_url = wptpsa_url("redirect_login");
    $redirect_url = empty($redirect_url) ? '/wp-admin' : $redirect_url;
    return site_url().$redirect_url;
}



function wptpsa_wp_redirect($url, $status) {
    
    $login = wptpsa_url("login");
    
    if(!$login) {
        return $url;
    }
    
    $trigger = array(
        "wp-login.php?checkemail=registered",
        "wp-login.php?checkemail=confirm"
    );
    
    foreach($trigger as $t) {
        if($url == $t) {
            return str_replace("wp-login.php", site_url().$login, $url);
        }
    }
    
    return $url;
}



function wptpsa_site_url($url, $path, $scheme = null) {

    $from = array(
        'lostpassword' => '/wp-login.php?action=lostpassword',
        'register' => '/wp-login.php?action=register',
        'logout' => '/wp-login.php?action=logout',
        'login' => '/wp-login.php',
        'activation' => '/'.WPTPSA_ACTIVATION_URL,
    );
        
    foreach($from as $k => $find) {
        if(wptpsa_url($k)) {
            $url = str_replace($find, wptpsa_url($k), $url);
        }
    }

    return $url;
}


function wptpsa_generate_rewrite_rules() {
	global $wp_rewrite;
    
    $rewrite = wptpsa_url();    
    uasort($rewrite, "wptpsa_sort");

	$from = array(
        'login' => 'wp-login.php',
        'activation' => WPTPSA_ACTIVATION_URL,
        'lostpassword' => 'wp-login.php?action=lostpassword',
        'register' => 'wp-login.php?action=register',
		'logout' => 'wp-login.php?action=logout'
	);

    $non_wp_rules = array();
    
    // deactivation for registration
    unset($rewrite["registration"]);
    
    foreach(array_keys($from) as $k) {
        if(isset($rewrite[$k]) && !is_null($rewrite[$k])) {
            $non_wp_rules[ltrim($rewrite[$k], "/")] = $from[$k];
        }
    }
    
	$wp_rewrite->non_wp_rules = $non_wp_rules + $wp_rewrite->non_wp_rules;
}



function wptpsa_login_url($login_url, $redirect = "") {
	$login_url = site_url( wptpsa_url('login') );

	if ( !empty($redirect) ) {
		$login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
    }

	return $login_url;
}


function wptpsa_activation_url($url) {
    return site_url( (wptpsa_url('activation')==null || wptpsa_url('activation') == '') ? WPTPSA_ACTIVATION_URL : wptpsa_url('activation' ) );
}

function wptpsa_register_url($url) {
    return site_url( wptpsa_url('register') );
}


function wptpsa_lostpassword_url($lostpassword_url, $redirect = "") {
	$args = array();
	if ( !empty($redirect) ) {
		$args['redirect_to'] = $redirect;
	}

	$lostpassword_url = add_query_arg( $args, site_url( wptpsa_url('lostpassword') ) );
	return $lostpassword_url;
}



function wptpsa_logout_url($redirect = "") {
	$args = array();
    
    if ( wptpsa_url("redirect_logout") ) {
        $args['redirect_to'] = site_url().wptpsa_url("redirect_logout");
    } elseif ( !empty($redirect) ) {
		$args['redirect_to'] = site_url();
	}

    $logout_url = wptpsa_url('logout');
    $logout_url = empty( $logout_url ) ? '/wp-login.php?action=logout' : $logout_url;
    $logout_url = add_query_arg($args,  $logout_url );
	$logout_url = wp_nonce_url( $logout_url, 'log-out' );
	return $logout_url;
}

// end filter functions

// Initial redirects

function wptpsa_init_redirect() {

    if(!isset($_SERVER["REQUEST_URI"])) {
        return;
    }
    
    $file = basename($_SERVER["REQUEST_URI"]);

    if(substr($file, 0, 12) != "wp-login.php") {
        return;
    }
    
    if(isset($_GET["action"])) {
        $action = $_GET["action"];
    } else {
        $action = "login";
    }
    
    if(isset($_GET["redirect_to"])) {
        $redirect = $_GET["redirect_to"];
    } else {
        $redirect = "";
    }

    $action = sanitize_text_field($action);
    $redirect = sanitize_text_field($redirect);
    
    if($action == "login" && wptpsa_url("login")) {
        $url = wptpsa_login_url("", $redirect);
    } elseif($action == "lostpassword" && wptpsa_url("lostpassword")) {
        $url = wptpsa_lostpassword_url("", $redirect);
    } elseif($action == "register" && wptpsa_url("register")) {
        $url = wptpsa_register_url("");
    } elseif($action == "logout" && wptpsa_url("logout")) {
        $url = wptpsa_logout_url($redirect);
    } else {
        $url = null;
    }

    if($url) {
        wp_redirect($url);
        exit;
    }
}

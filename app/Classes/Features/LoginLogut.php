<?php

namespace ThemePaste\SecureAdmin\Classes\Features;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class LoginLogut implements FeatureInterface {

    use Hook;

    private $features_id = 'custom-login-url';
    private $custom_login_path = '';

    public function register_hooks() {
        $this->action( 'plugins_loaded', [$this, 'custom_login_url'], 1 );
        $this->filter( 'tpsa_custom-login-url_login-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
        $this->filter( 'tpsa_custom-login-url_logout-url', [$this, 'modify_the_custom_login_logout_url_field'], 10, 2 );
    }

    public function modify_the_custom_login_logout_url_field( $template, $args ) {
        $site_url = get_site_url();
        $template = str_replace(
            '<input type="text" id="%2$s" name="%2$s" value="%3$s">',
            $site_url . '/<input type="text" id="%2$s" name="%2$s" value="%3$s">',
            $template
        );
        return $template;
    }
    
    public function custom_login_url() {
        $option_name = get_tpsa_settings_option_name( $this->features_id );
        $settings = get_option( $option_name, [] );

        if( !empty( $settings ) && is_array( $settings ) ) {
            if( isset( $settings['enable'] ) && $settings['enable'] == 1 ) {
                if( isset( $settings['login-url'] ) && !empty( $settings['login-url'] ) ) {
                    $this->custom_login_path = sanitize_title( $settings['login-url'] );
                    $this->register_custom_login_url( $this->custom_login_path );
                }
            }
        }
    }

    private function register_custom_login_url( $custom_login_path ) {
        if( empty( $custom_login_path ) ) {
            return;
        }
        
        $this->action( 'setup_theme', [$this, 'tpsa_custom_login_rewrite'], 1 );
        $this->action( 'wp_loaded', [$this, 'maybe_block_default_login'] );
        $this->filter( 'site_url', [$this, 'change_login_url'], 10, 3 );
        $this->filter( 'wp_redirect', [$this, 'filter_login_redirects'], 10, 2 );
    }

    public function tpsa_custom_login_rewrite() {
        add_rewrite_rule(
            '^' . $this->custom_login_path . '/?$',
            'wp-login.php',
            'top'
        );
    }

    public function maybe_block_default_login() {
        global $pagenow;
        
        $request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
        $request_uri = trim( $request_uri, '/' );
        
        // Block direct access to wp-login.php unless it's a logout request
        if ( $pagenow === 'wp-login.php' && 
             (!isset($_GET['action']) || $_GET['action'] !== 'logout') ) {
            $this->return_404();
        }
        
        // Block access to wp-admin for non-logged-in users
        if ( is_admin() && !is_user_logged_in() && 
             !(defined('DOING_AJAX') && DOING_AJAX) ) {
            $this->return_404();
        }
        
        // Handle custom login URL
        if ( $request_uri === $this->custom_login_path && 
             !is_user_logged_in() && $pagenow !== 'wp-login.php' ) {
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    private function return_404() {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include get_404_template();
        exit;
    }

    public function change_login_url( $url, $path, $scheme ) {
        if ( strpos( $path, 'wp-login.php' ) !== false ) {
            $url = home_url( $this->custom_login_path, $scheme );
            
            if ( isset( $_GET['action'] ) ) {
                $url = add_query_arg( 'action', $_GET['action'], $url );
            }
            
            if ( isset( $_GET['redirect_to'] ) ) {
                $url = add_query_arg( 'redirect_to', urlencode( $_GET['redirect_to'] ), $url );
            }
        }
        
        return $url;
    }

    public function filter_login_redirects( $location, $status ) {
        if ( strpos( $location, 'wp-login.php' ) !== false ) {
            $location = str_replace( 'wp-login.php', $this->custom_login_path, $location );
        }
        return $location;
    }
}
<?php
class WPTPSABase {

	public function __construct(){
		
	}

    /**
    * Global Enable/Disable status
    * @return (string) status template
    */
    static function master_status(){
        wptpsa_master_status();
    }

	/**
    * Initial settings during activating plugin
    * @return (void)
    */
	static function install(){
		wptpsa_install();
	}

	/**
    * Disable settings during deactivating plugin
    * @return (void)
    */
	static function deactivate(){
		wptpsa_deactivate();
	}

	/**
    * Deleted all settings during deleting plugin
    * @return (void)
    */
	static function uninstall(){
		wptpsa_uninstall();
	}

	/**
    * Show instand message
    * @param (string) $type
    * @return (void) 
    */
    static function message($message, $type='updated success'){
        echo "<div class='notice $type'>$message</div>";
    }

    /**
    * Set Message in Session
    * @param (string) $type
    * @return (void)
    */
    static function set_message($message, $type='success'){
        $_SESSION['message'][] = array($type, $message);
    }

    /**
    * Show message from session
    * @return (void)
    */
    static function show_message(){
        if ( !empty($_SESSION['message'] )){
            foreach ($_SESSION['message'] as $key => $value) {
                
                if ($value[0]=='success')
                    $class = 'updated success';
                else 
                    $class = $value[0];

                echo "<div class='notice {$class}'>{$value[1]}</div>";

                unset($_SESSION['message'][$key]);
            }
        }
        $type = array();
        $message = array();
        $_SESSION['message'][] = array($type, $message);
    }

    /**
    * Reload URL
    * @return (void)
    */
    static function reload(){
        echo '<script type="text/javascript">location.reload();</script>';
    } 

    /**
    * Redirect URL
    * @return (void)
    */
    static function redirect($redirect){
        header("Location: $redirect");
        exit;
    }


    /**
     * Recursive sanitation for array
     * 
     * @param array $array
     * @param boolean $space_removed
     * @return mixed $array
     */
    static function sanitize_array_text_field($array, $space_removed=false) {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = sanitize_array_text_field($value);
            }
            else {
                $value = sanitize_text_field( $value );
                if( $space_removed === true)
                    $value = str_replace(' ', '', $value);
            }
        }

        return $array;
    }


    /**
     * To Getting IP
     * @return (string/boolean) $ip
     */
    static function getIP(){

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip =esc_sql($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = esc_sql($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            $ip =  esc_sql($_SERVER['REMOTE_ADDR']);
             if($ip=='::1'){
                $ip = '127.0.0.1';
             }
        }

        if(filter_var($ip, FILTER_VALIDATE_IP))
            return $ip;
        else 
            return false;
    }
}

$wptpsa = new WPTPSABase;
?>
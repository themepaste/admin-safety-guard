<?php
function wptpsa_reset_layout(){
    $status = 0;
    if( isset($_POST['reset_wptpsa_layout']) && (int)sanitize_text_field($_POST['reset_wptpsa_layout'])>0 ){
        delete_option('wptpsa_layout_config');
        $status = 1;
    }
    echo json_encode(array('status'=>$status));
    wp_die();
} 

add_action( 'wp_ajax_wptpsa_reset_layout', 'wptpsa_reset_layout' );
add_action( 'wp_ajax_nopriv_wptpsa_reset_layout', 'wptpsa_reset_layout' );

$wptpsa_layout_config_default = array(
    'login_logo' => '',
    'background' => 'ffffff',
    'box_bg' => 'ffffff',
    'box_border' => 1,
    'box_border_color' => 'cccccc',
    'font_color' => '72777c',
    'font_size' => 14,
    'btn_font_size' => 13,
    'btn_font_color' => 'ffffff',
    'btn_font_color_hover' => 'ffffff',
    'btn_bg_color' => '00a14e',
    'btn_bg_color_hover' => '00a14e',
    'btn_border_color' => '028441',
    'btn_border_color_hover' => '028441',
);

$wptpsa_layout_config = array();

$layout_configs = get_option('wptpsa_layout_config');
if ( !empty($layout_configs) ){
    foreach ($layout_configs as $key => $value) {
        if( !empty($value) )
            $wptpsa_layout_config[$key] = $value;
        else
            $wptpsa_layout_config[$key] = $wptpsa_layout_config_default[$key];
    }
}else {
    $wptpsa_layout_config = $wptpsa_layout_config_default;  
}


// Main Page Section  
function wptpsa_layout_page() {
    
    global $wptpsa;

    if( isset($_POST["wptpsa_layout_config"]) && is_array($_POST["wptpsa_layout_config"]) ) {
        $post_wptpsa_layout_config = WPTPSABase::sanitize_array_text_field($_POST["wptpsa_layout_config"]);
        wptpsa_layout_update_options($post_wptpsa_layout_config);
        $wptpsa->message("Custom layout updated successfully.");
        echo '<script>
        setTimeout(function(){
            location.reload();
        }, 500);
        </script>';
    }
?>
    <div class="wrap">
        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>

        <div id="email-template-setting-left">
            <form method="post" action="" enctype="multipart/form-data">
                <?php
                    settings_fields("section");
                    do_settings_sections("wptpsa-manager-layout"); 
                ?>   
                <p class="submit">
                <input type="button" id="reset_wptpsa_layout" class="button button-secondary" value="Reset">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>       
            </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('#reset_wptpsa_layout').on('click', function(){
                    if (confirm('Are you sure to Reset?')){
                        $.post({
                            url: ajaxurl,
                            dataType: 'json',
                            data: {action:'wptpsa_reset_layout', reset_wptpsa_layout:1},
                            success: function(json){
                                if( json.status==1 ){
                                    $("#TPAdminModalAlert .title").html('Reset Success');
                                    $("#TPAdminModalAlert .content").html('Layout Configuration Successfully Reset.');
                                    $("#TPAdminModalAlert").modal();
                                    setTimeout(function(){
                                        // reloading after reset
                                        location.reload();
                                    }, 500);
                                }
                            }
                        });
                    }else{
                        return false;
                    }
                });
            });
        </script>

    </div>
<?php     

}


// Updation options
function wptpsa_layout_update_options($input) {
    if ( get_option( 'wptpsa_layout_config' ) !== false ) {
        update_option("wptpsa_layout_config", $input);
    } else {
        add_option("wptpsa_layout_config", $input);
    }
}



// Configuration all fields
function wptpsa_custom_layout_status_input() {
    global $wptpsa_layout_config;
    if (isset($wptpsa_layout_config['custom_layout_status'])) {
        $custom_layout_status = $wptpsa_layout_config['custom_layout_status'];
    } else {
        $custom_layout_status = null; // Or any default value
    }
    ?>
        <input id='custom_layout_status' name='wptpsa_layout_config[custom_layout_status]' size='40' type='checkbox' value='1' <?php echo '1'==$custom_layout_status ? 'checked' : ''; ?> /> <span>Enable</span>
    <?php
}

function wptpsa_login_logo_input() {
    global $wptpsa_layout_config;
    ?>
        
        <img src="<?php echo empty($wptpsa_layout_config["login_logo"]) ? '' : $wptpsa_layout_config["login_logo"];?>" id="attached_image" width="200">
        <div>
            <input type="text" name="wptpsa_layout_config[login_logo]" id="attached_file" class="regular-text00" value="<?php echo empty($wptpsa_layout_config["login_logo"]) ? '' : $wptpsa_layout_config["login_logo"];?>">
            <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Logo">

        </div>

    <?php
}


function wptpsa_background_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='background' name='wptpsa_layout_config[background]'  type='text' class="jscolor" value='<?php esc_attr_e($wptpsa_layout_config["background"]) ?>' placeholder="<?php echo $wptpsa_layout_config['background'];?>" />
    <?php
}

function wptpsa_box_background_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='box_bg' class="jscolor" name='wptpsa_layout_config[box_bg]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["box_bg"]) ?>' placeholder="<?php echo $wptpsa_layout_config['box_bg'];?>" />
    <?php
}


function wptpsa_box_border_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='box_border' name='wptpsa_layout_config[box_border]'  type='number' value='<?php esc_attr_e($wptpsa_layout_config["box_border"]) ?>' placeholder="<?php echo $wptpsa_layout_config['box_border'];?>" />PX
    <?php
}


function wptpsa_box_border_color_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='box_border_color' class="jscolor" name='wptpsa_layout_config[box_border_color]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["box_border_color"]) ?>' placeholder="<?php echo $wptpsa_layout_config['box_border_color'];?>" />
    <?php
}


function wptpsa_font_color_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='font_color' class="jscolor" name='wptpsa_layout_config[font_color]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["font_color"]) ?>' placeholder="<?php echo $wptpsa_layout_config['font_color'];?>" />
    <?php
}


function wptpsa_font_size_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='font_size' name='wptpsa_layout_config[font_size]'  type='number' value='<?php esc_attr_e($wptpsa_layout_config["font_size"]) ?>' placeholder="<?php echo $wptpsa_layout_config['font_size'];?>" />PX
    <?php
}



// Buttons
function wptpsa_btn_font_size_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_font_size' name='wptpsa_layout_config[btn_font_size]'  type='number' value='<?php esc_attr_e($wptpsa_layout_config["btn_font_size"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_font_size'];?>" />PX
    <?php
}



function wptpsa_btn_font_color_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_font_color' class="jscolor" name='wptpsa_layout_config[btn_font_color]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["btn_font_color"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_font_color'];?>" />
    <?php
}


function wptpsa_btn_font_color_hover_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_font_color_hover' class="jscolor" name='wptpsa_layout_config[btn_font_color_hover]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["btn_font_color_hover"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_font_color_hover'];?>" />
    <?php
}

function wptpsa_btn_border_color_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_border_color' class="jscolor" name='wptpsa_layout_config[btn_border_color]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["btn_border_color"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_border_color'];?>" />
    <?php
}


function wptpsa_btn_border_color_hover_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_border_color_hover' class="jscolor" name='wptpsa_layout_config[btn_border_color_hover]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["btn_border_color_hover"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_border_color_hover'];?>" />
    <?php
}



function wptpsa_btn_bg_color_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_bg_color' class="jscolor" name='wptpsa_layout_config[btn_bg_color]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["btn_bg_color"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_bg_color'];?>" />
    <?php
}


function wptpsa_btn_bg_color_hover_input() {
    global $wptpsa_layout_config;
    ?>
        <input id='btn_bg_color_hover' class="jscolor" name='wptpsa_layout_config[btn_bg_color_hover]'  type='text' value='<?php esc_attr_e($wptpsa_layout_config["btn_bg_color_hover"]) ?>' placeholder="<?php echo $wptpsa_layout_config['btn_bg_color_hover'];?>" />
    <?php
}



// Registration all fields    
function wptpsa_layout_page_fields() {

    add_settings_section("section", "", null, "wptpsa-manager-layout");

    // status
    add_settings_field('wptpsa_custom_layout_status', 'Custom Layout Status', 'wptpsa_custom_layout_status_input', 'wptpsa-manager-layout', 'section');

    // logo
    add_settings_field('wptpsa_login_logo', 'Logo', 'wptpsa_login_logo_input', 'wptpsa-manager-layout', 'section');
    
    // background
    add_settings_field('wptpsa_background', 'Background', 'wptpsa_background_input', 'wptpsa-manager-layout', 'section');
    
    // Form Box Settings
    add_settings_field('wptpsa_box_background', 'Form Box Background', 'wptpsa_box_background_input', 'wptpsa-manager-layout', 'section');

    add_settings_field('wptpsa_box_border', 'Form Box Border', 'wptpsa_box_border_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_box_border_color', 'Form Box Border Color', 'wptpsa_box_border_color_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_font_color', 'Font Color', 'wptpsa_font_color_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_font_size', 'Font Size', 'wptpsa_font_size_input', 'wptpsa-manager-layout', 'section');


    // Buttons
    add_settings_field('wptpsa_btn_font_size', 'Button Font Size', 'wptpsa_btn_font_size_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_btn_font_color', 'Button Font Color', 'wptpsa_btn_font_color_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_btn_font_color_hover', 'Button Font Color Mouse Over', 'wptpsa_btn_font_color_hover_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_btn_bg_color', 'Button Background Color', 'wptpsa_btn_bg_color_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_btn_bg_color_hover', 'Button Background Color Mouse Over', 'wptpsa_btn_bg_color_hover_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_btn_border_color', 'Button Border Color', 'wptpsa_btn_border_color_input', 'wptpsa-manager-layout', 'section');
    add_settings_field('wptpsa_btn_border_color_hover', 'Button Border Color Mouse Over', 'wptpsa_btn_border_color_hover_input', 'wptpsa-manager-layout', 'section');

    register_setting("section", "wptpsa_custom_layout_status");
    register_setting("section", "wptpsa_login_logo");
    register_setting("section", "wptpsa_background");
    register_setting("section", "wptpsa_box_background");
    register_setting("section", "wptpsa_box_border");
    register_setting("section", "wptpsa_box_border_color");
    register_setting("section", "wptpsa_font_size");
    register_setting("section", "wptpsa_font_color");

    // Buttons
    register_setting("section", "wptpsa_btn_font_size");
    register_setting("section", "wptpsa_btn_font_color");
    register_setting("section", "wptpsa_btn_font_color_hover");
    register_setting("section", "wptpsa_btn_bg_color");
    register_setting("section", "wptpsa_btn_bg_color_hover");
    register_setting("section", "wptpsa_btn_border_color");
    register_setting("section", "wptpsa_btn_border_color_hover");


}

add_action("admin_init", "wptpsa_layout_page_fields");



// Template
function wptpsa_login_page_template() { 
    
    global $wptpsa_layout_config;

    $custom_logo = ( isset($wptpsa_layout_config['login_logo']) && !empty($wptpsa_layout_config['login_logo'] ) ) ? $wptpsa_layout_config['login_logo'] : '';

    if( !empty($custom_logo) ){
?>
    <style type="text/css">
        #login h1 a, .login h1 a {
        background-image: url(<?php echo $custom_logo; ?>);
        max-height:65px;
        max-width:320px;
        width: 100%;
        background-size: initial;
        background-repeat: no-repeat;
        }
    </style>

<?php } else { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            display: none!important;
        }
    </style>
<?php  } ?>

    <style type="text/css">
    body { background-color: #<?php echo $wptpsa_layout_config['background'];?>!important; }
    #login { width: 35%!important; min-width: 320px!important; }
    .login form { 
        border: <?php echo $wptpsa_layout_config['box_border'];?>px solid #<?php echo $wptpsa_layout_config['box_border_color'];?>!important; 
        background-color: #<?php echo $wptpsa_layout_config['box_bg'];?>!important; }
    .wp-core-ui .button-primary.active, 
    .wp-core-ui .button-primary.active:focus, 
    .wp-core-ui .button-primary:active,
    .wp-core-ui .button-primary, 
    .wp-core-ui .button-primary.focus, 
    .wp-core-ui .button-primary.hover, 
    .wp-core-ui .button-primary:focus{        
        background: #<?php echo $wptpsa_layout_config['btn_bg_color'];?>!important;
        border-color: #<?php echo $wptpsa_layout_config['btn_border_color'];?>!important;
        color: #<?php echo $wptpsa_layout_config['btn_font_color'];?>;
        font-size: #<?php echo $wptpsa_layout_config['btn_font_size'];?>;
        box-shadow: none!important;
        text-shadow: none!important;
    }
    .wp-core-ui .button-primary.active:hover, 
    .wp-core-ui .button-primary:hover {
        background: #<?php echo $wptpsa_layout_config['btn_bg_color_hover'];?>!important;
        border-color: #<?php echo $wptpsa_layout_config['btn_border_color_hover'];?>!important;
        color: #<?php echo $wptpsa_layout_config['btn_font_color_hover'];?>;
    }
    .login #login_error, .login .message {
        border-color: #00a14e!important;
    }
    .login #login_error {
        border-left-color: #dc3232!important;
    }
    .login label {
        font-size: <?php echo $wptpsa_layout_config['font_size'];?>px!important;
        color: <?php echo $wptpsa_layout_config['font_color'];?>px!important;
    }
    .login form .input, .login input[type="text"] {
        font-size: <?php echo $wptpsa_layout_config['font_size'];?>px!important;
        padding: 5px!important;
        color: <?php echo $wptpsa_layout_config['font_color'];?>px!important;
    }
    .login #nav {
        margin-top: -36px!important;
    }
    #backtoblog { display: none!important; }
    </style>
<?php
}


if ( isset($wptpsa_layout_config['custom_layout_status']) && $wptpsa_layout_config['custom_layout_status']==1 && WPTPSA_MASTER_STATUS == 'active'){
    add_action( 'login_enqueue_scripts', 'wptpsa_login_page_template' );
}


// changing the logo link from wordpress.org to your site
function wptpsa_login_power_url() {  return home_url(); }
add_filter( 'login_headerurl', 'wptpsa_login_power_url' );

// changing the alt text on the logo to show your site name
function wptpsa_login_power_title() { return get_option( 'blogname' ); }
add_filter( 'login_headertitle', 'wptpsa_login_power_title' );
?>
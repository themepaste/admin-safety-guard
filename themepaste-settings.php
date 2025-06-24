<?php
// Setting Page
function wptpsa_settings_page() {
    
    global $wptpsa;

?>
    <div class="wrap">
        <h1>Themepaste Secure Admin <?php $wptpsa->master_status();?></h1>
        <?php wptpsa_tabs_html();?>

        <?php
        $licence = get_option('wptpsa_licence');
        ?>
        <form id="licence_form" method="post" action="" enctype="multipart/form-data">
            <link href="<?php echo plugins_url( '/css/font-awesome.css', __FILE__ );?>" rel="stylesheet">
            <h3>Pro version activation</h3>
            <p><label>Licence</label>: <input type="text" size="40" name="licence_no" id="licence_no" <?php echo WPTPSA_PACKAGE!='PRO'?'readonly':'';?> value="<?php echo $licence;?>" placeholder="Enter the licence number"> <span style="font-size: 16px;"><?php if(get_option('wptpsa_licence_status') == 'active'){ echo '<i title="Active" style="color:green;" class="fa fa-check-square-o" aria-hidden="true"></i>';}else{ echo '<i style="color:red;" title="Invalid Licence" class="fa fa-times" aria-hidden="true"></i>';}?></span> Click here if you dont have licence. <a href="http://themepaste.com/product/themepaste-secure-admin-pro">Get Licence</a></p>
            <input type="hidden" id="licence_status" name="licence_status" value="">
            <p><button type="button" id="SaveLicence" class="button button-primary" <?php echo WPTPSA_PACKAGE!='PRO'?'disabled':'';?> >Save</button>         
        </form>
    </div>
<?php     

}
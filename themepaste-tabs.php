<?php
function wptpsa_tabs_active_class($page)
{
    $cur_page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
    if ( $page == $cur_page )
    {
        echo 'active';
    }
    else
        echo '';
}


function wptpsa_tabs_html(){
    wptpsa_bootstrap();
    wptpsa_datatable();
?>
    <ul class="wptpsa_tabs">
        <li class="<?php wptpsa_tabs_active_class('wptpsa-page');?>"><a href="<?php echo WPTPSA_MAIN_URL;?>">Custom URL</a></li>
        <li class="<?php wptpsa_tabs_active_class('wptpsa-layout');?>"><a href="<?php echo WPTPSA_LAYOUT_URL;?>">Custom Layout</a></li>
        <li class="<?php wptpsa_tabs_active_class('wptpsa-email-template');?>"><a href="<?php echo WPTPSA_EMAIL_URL;?>">Email Activation</a></li>
        <li class="<?php wptpsa_tabs_active_class('wptpsa-captcha');?>"><a href="<?php echo WPTPSA_CAPTCHA_URL;?>">Captcha Activation</a></li>
        <li class="<?php wptpsa_tabs_active_class('wptpsa-login-attempts');?>"><a href="<?php echo WPTPSA_LOGIN_ATTEMPTS_URL;?>">Login Attempts</a></li>
        <li class="<?php wptpsa_tabs_active_class('wptpsa-user-role-manager');?>"><a href="<?php echo WPTPSA_USER_ROLE_URL;?>">User Role Manager</a></li>
        <li class="<?php wptpsa_tabs_active_class('wptpsa-settings');?>"><a href="<?php echo WPTPSA_SETTING_URL;?>">Settings</a></li>
    </ul>

    <div class="message_container">    

        <?php 
        global $wptpsa;
        $wptpsa->show_message();
        ?>


        <!-- Modal -->
        <div class="modal fade" id="TPAdminModalAlert" role="dialog">
            <div class="modal-dialog">

              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title title">Notice</h4>
                </div>
                <div class="modal-body content">
                  
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
              </div>
              
            </div>
        </div>
        <!--/modal-->  
    </div>
<?php
}
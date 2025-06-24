<?php
function wptpsa_master_status_action(){
	global $wptpsa,$wp_rewrite;
	$status = 0;
	if( isset($_POST['status']) && !empty($_POST['status']) ){
		update_option('wptpsa_master_status', sanitize_text_field($_POST['status']));
		$status = 1;
		$master_status = get_option('wptpsa_master_status');
		$msg = $master_status=='active' ? 'Enabled' : 'Disabled';
		$msg = "Themepaste Secure Admin Plugin has been $msg Successfully.";
		$wptpsa->set_message($msg);

		// URL enabled/disabled
		if($master_status=='active'){
			$wp_rewrite->flush_rules();
		}else{				
	        remove_action('generate_rewrite_rules', 'wptpsa_generate_rewrite_rules');
	        $wp_rewrite->flush_rules();
		}
	}
	echo json_encode(array('status'=>$status,'opt'=>$master_status));
	wp_die();
}

add_action( 'wp_ajax_wptpsa_master_status_action', 'wptpsa_master_status_action' );
add_action( 'wp_ajax_nopriv_wptpsa_master_status_action', 'wptpsa_master_status_action' );


function wptpsa_master_status(){
	?>
	<script type="text/javascript">
		function wptpsa_master_status(){
			var value = jQuery('#wptpsa_master_status').is(':checked') ? 'active' : 'inactive';
			wptpsa_post({action:'wptpsa_master_status_action', status: value}, function(json){
				if( json.status == '1')
					location.reload();
			});				
		}
	</script>
	<style type="text/css">
		.wptpsa_master_status {}
	</style>
	<span class="wptpsa_master_status" title="<?php echo ucfirst(WPTPSA_MASTER_STATUS); ?>">
		<label class="switch">
			<input onclick="wptpsa_master_status()" type="checkbox" name="wptpsa_master_status" id="wptpsa_master_status" value="active" <?php echo WPTPSA_MASTER_STATUS=='active' ? 'checked' : '';?>>
		    <div class="slider round"></div>
		</label>
	</span>
	<?php
}
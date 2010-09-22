<?
/*
*	DynaPosty
*	Admin area functions - main settings - displaying and saving
*/

add_action( 'admin_init', 'dypo_adminInit' );
function dypo_adminInit () {
	// only show the CSS and JS if we're on an admin page which involves the plugin.
	if ( isDyPoAdminPage() ) {		
		// include the general CSS for admin pages
		$stylesheetURL = DYPO_URL . '/dypo-admin.css';
		wp_enqueue_style('dypo-admin',$stylesheetURL,array(),false);
		
		$functionsURL = DYPO_JS_URL . '/dypo-admin.js';
		wp_enqueue_script('dypo-admin-js',$functionsURL,array(),false);
		
		$jq2jsonURL = DYPO_JS_URL . '/jquery.json-2.2.js';
		wp_enqueue_script('dypo-jq2json-js',$jq2jsonURL,array(),false);
		
		// we use thickbox for the help screen.
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
	}
}

// function to call when saving data via ajax.  wordpress hooked.
add_action('wp_ajax_dypo_saveOptions', 'dypo_saveOptions');
function dypo_saveOptions() {
	global $dypo_options, $dypo_shortcodes, $dypo_valueSets, $dypo_values ;
	
	if ( array_key_exists( 'dypo_URLVar', $_POST ) ) {
		$dypo_options['dypo_URLVar'] = stripslashes($_POST['dypo_URLVar']);
	}
	if ( array_key_exists( 'dypo_setCookie', $_POST ) ) {
		$dypo_options['dypo_setCookie'] = strtolower($_POST['dypo_setCookie']) == 'true';
	}
	if ( array_key_exists( 'dypo_cookieExpire', $_POST ) ) {
		$dypo_options['dypo_cookieExpire'] = stripslashes($_POST['dypo_cookieExpire']);
	}
	if ( array_key_exists( 'dypo_shortcodes', $_POST ) ) {
		$dypo_options['dypo_shortcodes'] = dypo_json_decode( stripslashes( $_POST['dypo_shortcodes'] ) );
	}
	if ( array_key_exists( 'dypo_valueSets', $_POST ) ) {
		$dypo_options['dypo_valueSets'] = dypo_json_decode( stripslashes( $_POST['dypo_valueSets'] ) ) ;
	}
	if ( array_key_exists( 'dypo_values', $_POST ) ) {
		dypo_saveShortcodeValues( dypo_json_decode( stripslashes( $_POST['dypo_values'] ) ) );
	}
	//save whatever we got to the options table
	update_option( DYPO_OPTIONS, $dypo_options );
	_e("Settings and Shortcodes Saved.");
	if ( $dypo_options['dypo_URLVar'] == '' ) {
		_e(" <strong>Warning</strong>: DynaPosty will always use the default values if no URL variable name is given.");
	}
	die();
}

/*
*	Server Environment Testing
*/
function dypo_envTester() {
	global $dypo_options, $dypo_envTest;
	// if we're doing the test, it means that we've no record of doing it before (or the user has specifically requested a re-test)
	// so now that we're doing it, we'll say that it failed, and let the following code correct it. 
	// we will assume that if everything goes well, the final ajax call will change it from failure to success
	$dypo_options['dypo_envTest'] = 'failure';
	update_option(DYPO_OPTIONS, $dypo_options);
	
?>
	<div id="dypo_envTest" class="dypo_message" >
		<p><?_e('First, we need to run a couple of tests to confirm that your server can run DynaPosty. Don\'t worry, this won\'t hurt a bit.');?></p>
	<?php
	$dypo_PHPTest = '';
	$dypo_JSONTest = '';
	$dypo_remoteAPITest = '';
	$dypo_WPVersionTest = '';
	
	if (version_compare(phpversion(), "5.0", ">=")) $dypo_PHPTest = 'class="dypo_pass"';
	if (function_exists(json_decode)) $dypo_JSONTest = 'class="dypo_pass"';
	if (wp_remote_retrieve_response_code(wp_remote_get(DYPO_REPORTING_URL)) == '200') $dypo_remoteAPITest = 'class="dypo_pass"';
	if (version_compare(get_bloginfo( 'version' ), '2.7', '>=')) $dypo_WPVersionTest = 'class="dypo_pass"';

	?>
		<dl class="dypo_tests">
			<dt>PHP Version</dt>
			<dd <?php echo $dypo_PHPTest; ?>>...PHP version is 5.0 or greater?</dd>
			<dt>Wordpress Version</dt>
			<dd <?php echo $dypo_WPVersionTest; ?>>...WordPress version is 2.7 or greater?</dd>	
			<dt>json_decode</dt>
			<dd <?php echo $dypo_JSONTest; ?>>...json_decode (php function) is available?</dd>
			<dt>External Request to API</dt>
			<dd <?php echo $dypo_remoteAPITest; ?>>...Outbound request to API Server?</dd>
			<dt>AJAX request</dt>
			<dd id="dypo_AJAXTest" >...Submitting AJAX request</dd>
		</dl>
		<script type="text/javascript">
			var dypo_PHPTest = <?=( $dypo_PHPTest == '' ? 'false' : 'true' )?>;
			var dypo_WPVersionTest = <?=( $dypo_WPVersionTest == '' ? 'false' : 'true' )?>;
			var dypo_JSONTest = <?=( $dypo_JSONTest == '' ? 'false' : 'true' )?>;
			var dypo_remoteAPITest = <?=( $dypo_remoteAPITest == '' ? 'false' : 'true' )?>;
			var dypo_AJAXTest = false;
			// submit an ajax request, to see what the return status is...
			jQuery.ajax({	type: 'POST',
							url: ajaxurl, 
							data: { "action" : 'dypo_ajaxTest' },
							complete: function ( reqObj, status) {
										if (status == 'success' && reqObj.responseText == 'true'){
											// it worked.  let's show that it worked.
											jQuery('#dypo_AJAXTest').addClass('dypo_pass').html('...AJAX request submitted successfully.');
											dypo_AJAXTest = true;
										}
										
										// at this point (and only this point), we know the results of all the tests
										if ( dypo_PHPTest && dypo_WPVersionTest && dypo_JSONTest && dypo_remoteAPITest && dypo_AJAXTest ) {
											// if they all passed, send a message via AJAX ('cause we know it works!) to wordpress
											// to update the dypo_envTest variable, and show a confirmation to the user before
											// fading away the whole test info area.
											jQuery.post( ajaxurl, { action : 'dypo_envTestSuccess' } ); 
											dypo_showMessage('<?_e('Congratulations.  Your setup is totally capable of running DynaPosty.  Now closing the pesky box.');?>', 'dypo_contentMessage', true, false, 5000, 2000);
											tb_show("Congratulations!", "#TB_inline?height=350&width=350&inlineId=dypo_congrats", null);
											// give 'em a chance to read the message, then hide the box.
											setTimeout( function(){ jQuery("#dypo_envTest").hide(2000); }, 5000 );
										} else {
											// if something failed, we need to show an error message.  and hide nothing.
											dypo_showMessage('<?_e('Warning - your server configuration may prevent the normal function of DynaPosty. See above.');?>','dypo_contentMessage', false, true );
										}
							}
						}
						);
		</script>
		
		<div class="dypo_success"></div>
	</div>
<?php
} // end function dypo_envTester

/*
*	Generates Hidden div which shows initial congrats and instructions to dynaposty user.
*/ 
function dypo_congrats() {
?>
<div id="dypo_congrats" style="display:none;">
	<h3><?_e('Yay! You\'re all set and ready to use DynaPosty!');?></h3>
	<p><?_e('All you have to do is');?>:</p>
	<ol>
		<li><?_e('Identify your url variable');?>. &nbsp;(<?_e('something like ?...<i>URLVAR</i>=abcdefg&...');?>)</li>
		<li><?_e('Determine your cookie settings');?>.</li>
		<li><?_e('Click to edit the table to create shortcodes and dynamic values');?>. (<?_e('Click on any non-bold field in the table - then you can edit it.');?>)</li>
	</ol>
	<p><center>
		<input type="button" class="button-secondary" onClick="tb_remove(); return false;" value="Close this box" />
	</center></p>
</div>
<?php
}// end function dypo_congrats
?>
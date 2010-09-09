<?
/*
*	DynaPosty
*	Admin area functions - main settings - displaying and saving
*/

add_action('admin_menu', 'dypo_configPage');
function dypo_configPage() {
	if ( function_exists('add_menu_page') )
		add_menu_page(__('DynaPosty Settings'), __('DynaPosty'), 'manage_options', 'dypo_config', 'dypo_configDisplay', DYPO_IMG_URL.'/icon_dynamite_17x15.png');
}

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

function dypo_configDisplay() {
	global $dypo_URLVar, $dypo_setCookie, $dypo_cookieExpire, $dypo_shortcodes, $dypo_valueSets, $dypo_values;

	// handle the file upload, if any.
	if ( array_key_exists( 'dypo_csvUpload', $_FILES ) ) {
		$fileMessage = '';
		$fileError = false;
		if ( $_FILES['dypo_csvUpload']['error'] == 0 ) {
			// okay, no error, let's try to parse this csv.  this is where the work is done.
			$parseResult = dypo_parseCSV( $_FILES['dypo_csvUpload']['tmp_name']); // pass in the tmp file name
			if ( $parseResult != '' ) {
				$fileMessage = __('We were unable to properly parse your CSV file.  Error: '.$parseResult);
				$fileError = true;
			} else {
				// if there was no error, $dypo_Values and $dypo_valueSets have been filled properly.
				// congratulate that user.
				$fileMessage = __('Here are the results of your upload. If you do not \"Save all Settings\", these values will not be saved.');
			}
		} else {
			// problem uploading file. show an error.
			$fileMessage = __('A problem occured uploding your CSV - did you choose a file?');
			$fileError = true;
		}
		if ( $fileMessage != '' ) {
			?>
			<script type="text/javascript"> 
				jQuery(document).ready( function () { 
					dypo_showMessage( "<?=$fileMessage?>", 'dypo_contentMessage', false, <?=( $fileError ? 'true' : 'false' )?> );
				} );
			</script>
			<?
		}
	}

?>
<div class="wrap">
	<div class="icon32" style="background:url('<?=DYPO_IMG_URL?>/icon_dynamite_40x35.png') no-repeat transparent;"><br/></div>
	<h2 style="clear:none;"><?_e("DynaPosty Settings");?></h2> 
	<div id="dypo_optionsContainer">
		<div class="dypo_messageContainer">
			<div id="dypo_contentLoading" style="display:none;"><img alt="" id="ajax-loading" src="images/wpspin_light.gif"/></div>
			<div id="dypo_contentMessage" class="dypo_message" style="display:none;">&nbsp;</div>
		</div>
		<div id="dypo_mainSettings">
			<table class="form-table">
			<tr>
				<th scope="row">
					<label for="dypo_URLVar">
						<?_e('Variable Name in URL');?>:
					</label>
				</th>
				<td>
					<input type="text" name="dypo_URLVar" id="dypo_URLVar" value="<?=$dypo_URLVar?>" onBlur=" jQuery('#dypo_URLVarPreview').html(this.value);" />
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="4">
				<?_e('I.E.');?>: <?bloginfo('url');?>?<span id="dypo_URLVarPreview"><?=$dypo_URLVar?></span>=<em><?_e('URLVariableValue');?></em>
				</th>
			</tr>
			<tr>
				<th scope="row">
					<label for="dypo_setCookie">
						<?_e('Set a cookie to save values');?>?
					</label>
				</th>
				<td style="width: 100px;">
					<input type="checkbox" name="dypo_setCookie" id="dypo_setCookie" value="true" <?=( $dypo_setCookie ? 'checked="checked"' : '' )?> />
				</td>
				<th scope="row">
					<label for="dypo_cookieExpire">
						<?_e('Save the cookie for ');?>?
					</label>
				</th>
				<td>
					<select id="dypo_cookieExpire" name="dypo_cookieExpire">
						<option value="15">15 <?_e('days');?> </option>
						<option value="30">30 <?_e('days');?> </option>
						<option value="60">60 <?_e('days');?> </option>
						<option value="90">90 <?_e('days');?> </option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="4">
				<table class="widefat" width="100%" id="dypo_shortcodeSettings">
				<thead>
				<tr class="dypo_editRow" title="<?_e('In this row, you can name the fields you are creating to make them easier to remember or understand.');?>">
					<th class="dypo_TableTitle" >
						<?_e('Field Name');?>
					</th>
					<th class="dypo_TableTitle" >
						<?_e('URL variable value');?> 
					</th>
					<?php
					$counter = 1;
					foreach( $dypo_shortcodes as $shortcode => $name ) {
					?>
					<th class="dypo_TableTitle dypo_editable dypo_col<?=$counter?>">
						<span id="dypo_shortcodeName|<?=$counter?>" style="font-weight:normal;"><?=$name?></span>
						<input class="dypo_textInput" type="text" id="dypo_editshortName|<?=$counter?>" value="<?=$name?>" />
						<? if ( $counter != 1 ) { ?>
						<a href="#" onClick="if (confirm('<?_e('Delete this Shortcode');?>?')) dypo_delShortcode(this, 'dypo_col'); return false;" title="<?_e('Delete this Shortcode');?>" class="dypo_delete">X</a>
						<? } // end if ?>
					</th>
					<?php
						$counter++;
					} // end foreach
					?>
				</tr>
				</thead>
				<tr class="dypo_editRow dypo_lightRow" title="<?_e('This is the code you will insert into your pages and posts to tell DynaPosty to look for the information in this table. The codes should be short and easy to remember, such as [zip] for Zip Code.');?>">
					<td>
						<strong><?_e('shortcode');?></strong>
					</td>
					<td>
						<strong>N/A</strong>
					</td>
					<?php
					$counter = 1;
					foreach( $dypo_shortcodes as $shortcode => $name ) {
					?>
					<td class="dypo_editable  dypo_col<?=$counter?>" >
						<span id="dypo_shortcode|<?=$counter?>"><?=$shortcode?></span>
						<input class="dypo_textInput dypo_noSpaces" type="text" id="dypo_editshortcode|<?=$counter?>" value="<?=$shortcode?>" />
					</td>
					<?php
						$counter++;
					} // end foreach
					?>				
				</tr>
				<tr class="dypo_editRow dypo_lightRow" title="<?_e('When your site visitor doesn\'t have a URL variable found here, this is the content that DynaPosty will display instead. It is important that you create a value for every field that is generic and will make sense to all site visitors. ');?>">
					<td>
						<strong><?_e('Default Values');?></strong>
					</td>
					<td>
						(<strong><em><?_e('none or unrecognized value');?></em></strong>)
					</td>
					<?php
					$counter = 1;
					foreach ( $dypo_shortcodes as $shortcode => $name ) {
					?>
					<td class="dypo_editable dypo_col<?=$counter?>" >
						<span id="dypo_default|<?=$counter?>"><?=$dypo_values['default'][$shortcode]?></span>
						<textarea class="dypo_textarea" id="dypo_edit_default|<?=$counter?>" ><?=$dypo_values['default'][$shortcode]?></textarea>
					</td>
					<?php
						$counter++;
					} // end foreach ( $dypo_shortcodes as $shortcode )
					?>
				</tr>
				<?php
				foreach( $dypo_valueSets as $vsID => $vsName ) {
				?>
				<tr id="<?=$vsID?>" class="dypo_row<?=$vsID?> dypo_editRow" >
					<td class="dypo_editable dypo_strong" >
						<span id="dypo_setName|<?=$vsID?>"><strong><?=$vsName?></strong></span>
						<input class="dypo_textInput" type="text" id="dypo_editsetName|<?=$vsID?>" value="<?=$vsName?>" />
						<? if ( $vsID != 1 ) { ?>
						<a href="#" onClick="if (confirm('<?_e('Delete this Set');?>?')) dypo_delValSet(this); return false;" title="<?_e('Delete this Value Set');?>" class="dypo_delete">X</a>
						<? } // end if ?>
					</td>
					<td class="dypo_editable" >
						<span id="dypo_val_<?=$vsID?>|urlvar"><?=$dypo_values[$vsID]['urlvar']?></span>
						<input class="dypo_textInput dypo_noSpaces" type="text" id="dypo_edit_<?=$vsID?>|urlvar" value="<?=$dypo_values[$vsID]['urlvar']?>" />
					</td>
					<?php
					$counter = 1;
					foreach ( $dypo_shortcodes as $shortcode => $name ) {
					?>
					<td class="dypo_editable dypo_col<?=$counter?>" >
						<span id="dypo_val_<?=$vsID?>|<?=$counter?>"><?=$dypo_values[$vsID][$shortcode]?></span>
						<textarea class="dypo_textarea" id="dypo_edit_<?=$vsID?>|<?=$counter?>" ><?=$dypo_values[$vsID][$shortcode]?></textarea>
					</td>
					<?php
						$counter++;
					} // end foreach ( $dypo_shortcodes as $shortcode )
					?>
				</tr>
				<?php
				} // end foreach( $dypo_valueSets as $valueSetName )
				?>
				<tr>
					<td colspan='100'>
					<a href="#" onClick="dypo_newValueSetRow('dypo_setName','dypo_editsetName','dypo_val_','dypo_edit_','dypo_row'); return false;"><?_e('Add a row');?> &darr;</a>
					<a href="#" onClick="dypo_newShortcodeCol('dypo_shortcodeName','dypo_shortcode','dypo_col'); return false;" style="float:right;"><?_e('Add a shortcode');?> &rarr;</a>
					</td>
				</tr>
				</table>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?_e('Upload a ');?> .csv : 
				</th>
				<td>
					<form action="" method="POST" enctype="multipart/form-data">
					<input type="file" id="dypo_csvUpload" name="dypo_csvUpload" value="" />
					<input type="submit" value="Upload and Preview" />
					</form>
				</td>
			</table>
			<tr>
				<th scope="row">
					<input type="submit" id="dypo_saveAll" name="dypo_saveAll" class="button-primary dypo_saveAll" value="Save All Settings" />
				</th>
			</table>
			<script type="text/javascript">
			//<![CDATA[
				// save all settings on this page.
				jQuery(document).ready( function(){ 
					// make cells editable when clicked.
					jQuery('.dypo_editable').click( function () {
						dypo_editCell(this);
					});

					// set the function which confirms when the user leaves the page without saving
					window.onbeforeunload = dypo_contentOnUnload;

					// set the function which saves all the data
					jQuery('.dypo_saveAll').click( function () {
						// first, let's clear out all invalid/unwanted characters in the input:text fields
						dypo_sanitizeInput( 'dypo_mainSettings', 'dypo_noSpaces' );

						// check for duplicate URL variables
						if (dypo_findDupeURLVars('dypo_edit_')) {
							dypo_showMessage( "<?_e('Duplicate URL variables detected, which is not allowed.  Settings NOT saved.');?>", 'dypo_contentMessage', false, true);
							return;
						}

						// then build the objects with the data stored in the inputs
						// using different id prefixes to identify different kinds of data 
						//    - shortcodes/names, value set names, and values for shortcodes 
						// the first call is special - if there are duplicate shortcodes, this is a no-go.
						// show an error, and stop what we're doing.
						var shortcodes = dypo_buildShortcodes('dypo_editshortName','dypo_editshortcode');
						if ( !shortcodes ){
							dypo_showMessage( "<?_e('Duplicate Shortcodes detected, which is not allowed.  Settings NOT saved.');?>", 'dypo_contentMessage', false, true);
							return;
						}

						// reset all the open editable cells back
						// to not being edited.
						dypo_resetEdits('dypo_editable', 'dypo_strong');

						var valueSets = dypo_buildValueSets('dypo_editsetName');
						var values = dypo_buildValues('dypo_edit_','dypo_editshortcode');
						
						// send the info off to wordpress
						// and show wordpress's response.
						dypo_ajax( ajaxurl, 
									{ 	"action" : 'dypo_saveOptions',
										"dypo_URLVar" : jQuery('#dypo_URLVar').val(),
										"dypo_setCookie" : jQuery('#dypo_setCookie').get(0).checked.toString(),
										"dypo_cookieExpire" : jQuery('#dypo_cookieExpire').val(),
										"dypo_shortcodes" : jQuery.toJSON(shortcodes),
										"dypo_valueSets" : jQuery.toJSON(valueSets),
										"dypo_values" : jQuery.toJSON(values),
									},
									'dypo_contentMessage',
									'dypo_contentLoading',
									false
									);
						// no unsaved edits - no need to ask the user about leaving.
						dypo_unsavedEdits = false;
					} );
				} );
			//]]></script>
		</div> <!-- end dypo_mainSettings -->
	</div> <!-- end dypo_optionsContainer -->
</div> <!-- end wrap -->


<?php

}
?>
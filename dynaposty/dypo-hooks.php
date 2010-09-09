<?php
/*
*	Dynaposty Wordpress Hooks
*/

// this is where the magic happens.   Not really,  just string replacing.
// * loads all the values from the database
// * checks to see if the URL matches anything we're looking for.
// * if so, find the shortcode value set appropriate,
// * and then register some shortcode handlers to do the string replacing.
add_action('init','dypo_init');
function dypo_init ( $atts=null, $content=null ) {
	global $dypo_URLVar, $dypo_setCookie, $dypo_cookieExpire, $dypo_shortcodes, $dypo_valueSets, $dypo_values;

	// get the current values for the current shortcode/valueset combinations
	// this always needs to happen.
	dypo_getValues();

	// don't do anything else if we're in an admin page. - no strings to replace.
	// but add a media buttons action, for the shortcode tool on the editor.
	if ( is_admin() ){
		add_action('media_buttons','dypo_mediaButtonIcon',100); //- this should be the last thing to show.
		return;
	}
	
	// check if the referrer's query string contains the val we're looking for
	// also, check the cookie to see if we've saved any indicator.

//	$refVars = parse_query($_SERVER['HTTP_REFERER']); // this is for checking referrer.  maybe later.
	
	$refVars = $_GET;
	$urlValue = ''; // lets try to fill this variable now.
	if ( array_key_exists( $dypo_URLVar, $refVars ) && strlen( $refVars[$dypo_URLVar] ) > 0 ) {
		// we found the item in the referrer QS - try to determine which valueset is appropriate, 

		$urlValue = $refVars[$dypo_URLVar];
		if ( $dypo_setCookie ) {
			// and set a cookie so that we remember this URL variable.
			setCookie( DYPO_URLVAR_COOKIE, $urlValue, time() + 60*60*24*$dypo_cookieExpire );
		}
		
	} elseif ( $dypo_setCookie && array_key_exists( DYPO_URLVAR_COOKIE, $_COOKIE ) && strlen( $_COOKIE[DYPO_URLVAR_COOKIE] ) > 0  ){
		// looks like the user wants to look for cookies, and 
		// we got a valid url Value from the cookie,
		$urlValue = $_COOKIE[DYPO_URLVAR_COOKIE];
	}

	// even if we can't find any info on this url value, we still need a value set.
	$valueSet = 'default';
	if ( $urlValue != '' ) {
		// ok, we got our url var value, lets try to find a valueset.
		
		// make a list of valid valuesets
		$valueSets = dypo_valueSetList();

		// find a valid valueset.
		$urlVars = dypo_getShortcodeValues( "shortcode = 'urlvar' AND val = '$urlValue' AND valueset IN ($valueSets) " );
		
		if ( count($urlVars) > 0 ) {
			// we found it, and now know which values to use.
			$valueSet = $urlVars[0]->valueset;
		}
	} 

	// OK!  at this point we have a valueset, hopefully.
	// lets get some shortcode values, and register shortcode handlers.
	
	// make a list of valid shortcodes
	$shortcodes = dypo_shortcodeList();
	
	// grab the values from the database
	$shortcodeValues = dypo_getShortcodeValues( " valueset = '$valueSet' AND shortcode IN ($shortcodes) " );
		
	foreach ( $shortcodeValues as $scVal ) {
		if ( $scVal->shortcode != 'urlvar' ) { // don't create shortcodes for url vars
			//create a lambda-style function which just returns the value.
			$newFunc = create_function( '', ' return "'.str_replace('"','\"',$scVal->val).'";' );
			add_shortcode( $scVal->shortcode, $newFunc );
		}
	}
}

// adds the shortcode inserter to an edit page
function dypo_mediaButtonIcon () {
	// make sure the hidden div gets displayed at the bottom.
	add_action('admin_footer', 'dypo_shortcodeInserter');
?>	<a class="thickbox" href="#TB_inline?height=300&width=300&inlineId=dypo_selectShortcode" title="<?_e('Add a DynaPosty Shortcode');?>">
	<img src="<?=DYPO_IMG_URL.'/icon_dynamite_gray_15x14.png'?>" alt="<?_e('Add a DynaPosty Shortcode');?>" border="0" onmouseover="this.src='<?=DYPO_IMG_URL.'/icon_dynamite_15x14.png'?>';" onmouseout="this.src='<?=DYPO_IMG_URL.'/icon_dynamite_gray_15x14.png'?>';"/>
	</a>
<?
} // end function dypo_mediaButtonIcon

// shows the hidden div which allows the user to choose and actually insert a shortcode
function dypo_shortcodeInserter() {
	global $dypo_shortcodes;
?>
	<div id="dypo_selectShortcode" style="display:none; height: 300px; width:300px;">
		<h3>Insert a DynaPosty Shortcode:</h3>
		<select id="dypo_shortcodeSelect" >
<?
	foreach( $dypo_shortcodes as $sc => $scName ) {
?>
			<option value="<?=$sc?>"><?=$sc?>&nbsp;</option>
<?		
	}// end foreach
?>
		</select>
	<p>
	<input type="button" class="button-primary" value="Insert Shortcode" onclick="dypo_insertShortcode('dypo_shortcodeSelect');"/>&nbsp;&nbsp;
	<input type="button" class="button-secondary" value="Cancel" onclick="tb_remove(); return false;;"/>&nbsp;&nbsp;&nbsp;
	</p>
	</div><!-- end dypo_selectShortcode -->
	
<?
} // end function dypo_shortcodeInserter


?>
<?
/*
*	DynaPosty Functions
*/

/*
*	tells us if we're on a dynaposty Admin page
*/
function isDyPoAdminPage() {
	if ( strpos( $_SERVER["SCRIPT_NAME"], '/wp-admin' ) === false ){
		// if the URL somehow doesn't have /wp-admin, this is not a dynaposty admin page
		return false;
	}
	if ( strpos( $_SERVER["SCRIPT_NAME"], 'page.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'page-new.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post-new.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post-new.php' ) !== false 
		|| strpos( $_SERVER["SCRIPT_NAME"], 'post-new.php' ) !== false 
		|| strpos( $_SERVER["QUERY_STRING"], 'page=dypo_config' ) !== false 
		) {
		return true;
	}
}

// makes a comma separated list of valid shortcodes, 
// with the special urlvar keyword added on the end
function dypo_shortcodeList () {
	global $dypo_shortcodes;
	
	$list = '';
	foreach ($dypo_shortcodes as $shortcode => $name ) {
		$list.= "'$shortcode',";
	}
	$list.= "'urlvar'"; // include the urlvars, which are not in the array of shortcodes	
	return $list;
}

// makes a comma separated list of valid valuesets, 
// with the special default set added on the end
function dypo_valueSetList () {
	global $dypo_valueSets;
	
	$list = '';
	foreach ($dypo_valueSets as $vsID => $vsName) {
		$list.= "'$vsID',";
	}
	$list.= "'default'"; // include the default values	
	return $list;
}

// gets the shortcode/value pairs from the database.
// and put them into the global 2D values array
function dypo_getValues() {
	global $wpdb, $dypo_values, $dypo_shortcodes, $dypo_valueSets;
	
	$shortcodeWhere = dypo_shortcodeList();
	$valueSetWhere = dypo_valueSetList();
	
	// put the comma-separated lists into the SQL where case
	$where = " shortcode IN ($shortcodeWhere) AND valueset IN ($valueSetWhere) ";
	
	// pass that off, and get all the returned values
	$results = dypo_getShortcodeValues($where);
	
	$dypo_values = array();
	foreach( $results as $val ) {
		// 2-dimensional array.  top-level array keys are value sets, values are arrays
		// within sub arrays, array keys are shortcodes, values are shortcode values.
		if ( array_key_exists( $val->valueset, $dypo_values ) && is_array($dypo_values[$val->valueset]) ) {
			// if the sub-array is already there, just append.
			$dypo_values[$val->valueset][$val->shortcode] = $val->val;
		} else {
			// if the sub-array has not been created yet, then create an array!
			$dypo_values[$val->valueset] = array( $val->shortcode => $val->val );
		}
	}
	// there.  that should do it.
}

// save shortcode values from the browser into the database
// expects a 2-dimensional array, much like is produced in dypo_getValues
function dypo_saveShortcodeValues ( $scVals ) {
	global $wpdb;
	
	// sanity checking.
	if ( !is_array($scVals) ) {
		echo('Something went wrong, scVals is not an array.');
		return;
	}
	// loop through all the value sets (which contain arrays of shortcode/values)
	foreach ( $scVals as $vsID => $sCodes )  {
		// more sanity checking.
		if ( !is_array($sCodes) ) {
			echo('Something went wrong, values in scVals are not arrays.');
			return;
		}
		
		// loop through all the shortcodes, and save each ValueSet/Shortcode/Value 
		// if it doesn't exist already
		foreach ( $sCodes as $shortcode => $scValue ) {
			$currentRecord = $wpdb->get_row( "SELECT * FROM ".DYPO_SHORTCODE_TABLE." WHERE shortcode='$shortcode' AND valueset='$vsID'");
			
			if ( !is_null($currentRecord) ) {
				// the record already exists.  But if the value is unchanged, 
				// we don't need to send an update
				if ( $currentRecord->val != $scValue ) {
					// ok, it was changed, let's save it.
					$wpdb->update( 	DYPO_SHORTCODE_TABLE, 
									array( 'val'=>$scValue ),
									array( 'shortcode'=>$shortcode, 'valueset'=>$vsID ),
									array( '%s' ),
									array( '%s', '%s')
								);
				}
			} else {
				// this value/shortcode combo does not exist yet.  Let's make it.
				$wpdb->insert( DYPO_SHORTCODE_TABLE, array( 'shortcode'=>$shortcode, 'valueset'=>$vsID, 'val'=>$scValue) );
			}
		}
	}

}

// alternatively dypo_getValues, we can set the values with a csv import.
// returns an error message if there was one.
function dypo_parseCSV ( $filename ) {
	global $dypo_shortcodes, $dypo_valueSets, $dypo_values;
	ini_set('auto_detect_line_endings', 1);
	
	$handle = fopen( $filename, "r");
	if ($handle !== false) {
		$counter = 0;
		
		// initialize the new value array (with the defaults)
		$newValueArray = array('default' => $dypo_values['default']);
		$newValSetArray = array();
		
		while (($data = fgetcsv($handle, 3000)) !== FALSE) {
			$newShortcodeArray = array();
			$num = count($data);
			if ( $num < 3 ) {
				return "You must have at least 3 values on line ".($counter+1)." (one Set Name, one URL Variable, and one shortcode)";
			}
			if ( $num > (count($dypo_shortcodes)+1) ) {
				return "On line ".($counter+1).", you have $num items, but not enough shortcode columns to accomodate them.";
			}
			// get the name of the value set first.
			$newValSetArray[$counter+1] = $data[0];
			// get the urlvar second.
			$newShortcodeArray['urlvar'] = trim($data[1]);
			// then fill the rest of the shortcodes, if the user has included them.
			$i = 2;
			foreach ( $dypo_shortcodes as $sc => $scName ) {
				if ( array_key_exists( $i, $data ) ) {
					$newShortcodeArray[$sc] = $data[$i];
				} else {
					break;
				}
				$i++;
			}
			// then replace the current valueset in the global values.
			$newValueArray[$counter+1] = $newShortcodeArray;
			$counter++;
		}
		fclose($handle);
		
		// ok, all seemed to go well!  update the global variables, and return.
		$dypo_values = $newValueArray;
		$dypo_valueSets = $newValSetArray;
		return ''; // no error message 
	} else {
		// couldn't open the file?
		fclose($handle);
		return 'Unable to read file.';
	}
}
 
// get shortcode values from the database.
function dypo_getShortcodeValues ( $where = ' 1 ' ) {
	global $wpdb;
	
	$sql = "SELECT * FROM ".DYPO_SHORTCODE_TABLE." WHERE $where ";

	return (array)($wpdb->get_results($sql));
}

// useful for debugging
if ( !function_exists( 'pre' ) ) {
function pre( $value = '!@#$%^&*()_!@#$%^&*()' ) {

	if ( $value === '!@#$%^&*()_!@#$%^&*()' ) {
		// dummy value, show the stack trace
		echo('<pre>'.var_export(debug_backtrace(),true).'</pre>'."<br />\n");
		return;
	}
	echo('<pre>'.var_export($value,true).'</pre>'."<br />\n");
}
}


function dypo_json_decode($json, $assoc = false) { 
	// at the moment, the $assoc = false argument does nothing but make this function
	// have the same number of arguments as the original json_decode
    // Author: walidator.info 2009
    $comment = false;
	$x = NULL;
    $out = '$x=';
   
    for ($i=0; $i<strlen($json); $i++)
    {
        if (!$comment)
        {
            if ($json[$i] == '{')        $out .= ' array(';
            else if ($json[$i] == '}')    $out .= ')';
            else if ($json[$i] == ':')    $out .= '=>';
            else                         $out .= $json[$i];           
        }
        else $out .= $json[$i];
        if ($json[$i] == '"')    $comment = !$comment;
    }
    eval($out . ';');
    return $x;
}

/**
*  Use this function to parse out the query array element from
*  the output of parse_url().
*/
function dypo_parseQuery($var){
	$var  = parse_url($var, PHP_URL_QUERY);
	$var  = html_entity_decode($var);
	$var  = explode('&', $var);
	$arr  = array();

	foreach($var as $val) {
		$x = explode('=', $val);
		$arr[$x[0]] = $x[1];
	}
	unset($val, $x, $var);
	return $arr;
}

/*
*	random string generator
*/
function dypo_randomString($length = 10, $letters = '1234567890qwertyuiopasdfghjklzxcvbnm') {
	$s = '';
	$lettersLength = strlen($letters)-1;
	for($i = 0 ; $i < $length ; $i++) {
		$s .= $letters[rand(0,$lettersLength)];
	}
	return $s;
} 
?>
<?php
/*
Plugin Name: DynaPosty
Plugin URI: http://www.mobiah.com/
Description: Dynamic content in your posts and pages, based on referring searches and links
Version: 0.1
Author: Mobiah 
*/

/*  Copyright 2010  Mobiah http://www.mobiah.com
*/

/*
*	GLOBAL PATHS and variables
*
*/
define("DYPO_PATH", dirname(__FILE__));
$pathExploded = explode( '/', DYPO_PATH );
define("DYPO_URL", WP_PLUGIN_URL.'/'.$pathExploded[ count($pathExploded)-1 ]);
define("DYPO_IMG_URL", DYPO_URL.'/images');
define("DYPO_JS_URL", DYPO_URL.'/js');
global $wpdb;
define("DYPO_SHORTCODE_TABLE", $wpdb->prefix."dypo_shortcodes_vals");
define("DYPO_URLVAR_COOKIE", 'dypo_urlvar');
define("DYPO_VERSION", "0.1");
define("DYPO_REPORTING_ACTION", 'dypo_reporting');
define("DYPO_REPORTING_URL", 'http://www.jeremyhou.com/mobiah/posty-reporting.php');

/*
*	Includes
*/
include_once( DYPO_PATH.'/dypo-admin.php' );
include_once( DYPO_PATH.'/dypo-config.php' );
include_once( DYPO_PATH.'/dypo-functions.php' );
include_once( DYPO_PATH.'/dypo-hooks.php' );
include_once( DYPO_PATH.'/dypo-install.php' );
include_once( DYPO_PATH.'/dypo-reporting.php' );


/*
*	Options stored in wp-options table
*/
define ('DYPO_OPTIONS', 'dypo_options');
global $dypo_options;  // the array which holds all options
$dypo_options = get_option( DYPO_OPTIONS, array());

// look for a unique identifier for this dynaposty installation.  In the future this may be replaced by
// actual registration for an API key.  For now, if we dont' find a key, generate a random one.
global $dypo_key;
if ( is_array($dypo_options) && array_key_exists( 'dypo_key', $dypo_options ) && $dypo_options['dypo_key'] != '' ) {
	$dypo_key = $dypo_options['dypo_key'];
} else {
	$dypo_key = dypo_randomString( 25 );  // make a key of random alphanumeric characters, length 25
	$dypo_options['dypo_key'] = $dypo_key;  // put it in the options array
	update_option( DYPO_OPTIONS, $dypo_options ); // and save it back to the options table
}

// this variable tells whether or not we have run the server environment compatibility tests.
// if it is blank, they have not been run.  one can set it to blank to re-run the tests.
global $dypo_envTest;
if ( is_array($dypo_options) && array_key_exists( 'dypo_envTest', $dypo_options ) ) {
	$dypo_envTest = $dypo_options['dypo_envTest'];
} else {
	$dypo_envTest = '';
}

// what url variable should we look for?
global $dypo_URLVar;
if ( is_array($dypo_options) && array_key_exists( 'dypo_URLVar', $dypo_options ) ) {
	$dypo_URLVar = $dypo_options['dypo_URLVar'];
} else {
	$dypo_URLVar = 'utm_content'; // standard adwords querystring value
}

// should we set a cookie to keep the user getting the same values for a given amount of time?
global $dypo_setCookie;
if ( is_array($dypo_options) && array_key_exists( 'dypo_setCookie', $dypo_options ) ) {
	$dypo_setCookie = $dypo_options['dypo_setCookie'];
} else {
	$dypo_setCookie = false; 
}

// if so, how long should the cookie stick around?
global $dypo_cookieExpire;
if ( is_array($dypo_options) && array_key_exists( 'dypo_cookieExpire', $dypo_options ) ) {
	$dypo_cookieExpire = $dypo_options['dypo_cookieExpire'];
} else {
	$dypo_cookieExpire = 15;  // defaults to 15 days
}

// an array of the shortcodes the user had specified. format: array( 'shortcode1=>'name1'', 'shortcode2'=>'name2' );
global $dypo_shortcodes;
if ( is_array($dypo_options) && array_key_exists( 'dypo_shortcodes', $dypo_options ) && is_array($dypo_options['dypo_shortcodes']) ) {
	$dypo_shortcodes = $dypo_options['dypo_shortcodes'];
} else {
	$dypo_shortcodes = array( 'shortcode1'=>'Shortcode Name', 'shortcode2'=>'Shortcode Name' );
}

// an array of the names of the value sets the user has entered.
global $dypo_valueSets;
if ( is_array($dypo_options) && array_key_exists( 'dypo_valueSets', $dypo_options ) && is_array($dypo_options['dypo_valueSets']) ) {
	$dypo_valueSets = $dypo_options['dypo_valueSets'];
} else {
	$dypo_valueSets = array( '1' => 'Set 1' , '2' => 'Set 2' ) ;
}

// the array containing all the values for shortcode/valueset pairs.  will be loaded from the database on init.
// 2-dimensional array.  top-level array keys are value sets, values are arrays
// within sub arrays, array keys are shortcodes, values are shortcode values.
global $dypo_values;

?>
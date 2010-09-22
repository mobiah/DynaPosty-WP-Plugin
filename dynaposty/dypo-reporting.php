<?php
/*
*	DynaPosty home-calling functions
*	
*/

/*
*	Gathers data and uses wordpress's post function to submit all data to a server
*	This includes: plugin name, plugin version, admin-selected options, server environment info, api key, and domain
*/
function dypo_report() {
	global $dypo_options, $dypo_key;

	$postData = array();
	$postData['timeout'] = 1;
	$postData['body'] = array();
	$postData['body']['plugin_name'] = 'dynaposty';
	$postData['body']['version'] = DYPO_VERSION;
	$postData['body']['options'] = $dypo_options;
	$postData['body']['environment'] = dypo_environmentInfo();
	$postData['body']['key'] = $dypo_key;
	$postData['body']['domain'] = $_SERVER['HTTP_HOST'];

	$doReport = wp_remote_post(DYPO_REPORTING_URL, $postData);

	return $doReport;

}
add_action(DYPO_REPORTING_ACTION, 'dypo_report');

/*
*	Gather info about the Server Environment
*/
function dypo_environmentInfo() {
	$serverInfo = array();
	
	$serverInfo['wp_version'] = get_bloginfo( 'version' );
	$serverInfo['wp_charset'] = get_bloginfo( 'charset' );
	$serverInfo['phpversion'] = phpversion();
	$serverInfo['phpsettings'] = ini_get_all();
	$serverInfo['phpextensions'] = get_loaded_extensions();
	$serverInfo['_SERVER'] = $_SERVER;
	
	return $serverInfo;
}
?>
<?
/*
*	DynaPosty activation/deactivation hooks
*	Including: 
*		Database installation and/or upgrade for DynaPosty
*		registering reporting functions
*/

// for registering activation/deactivation functions, we need to pass in the main plugin file, which in our case,
// is the "includer" of this file.
$backtrace = debug_backtrace();
$mainFile = $backtrace[0]['file'];

// a couple of hooks to set up scheduled reporting routines (or remove them if deactivating)
// see dypo-hooks.php
register_activation_hook( $mainFile, 'dypo_addReporting' );
register_deactivation_hook( $mainFile, 'dypo_removeReporting');

register_activation_hook( $mainFile, 'dypo_install');
function dypo_install() {
	global $wpdb, $dypo_options;
	
	$table_name = DYPO_SHORTCODE_TABLE;
	
	// we just need to install a table in the database - if it's already there, do nothing.
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		// the table doesn't exist, let's make it.
		$sql = "CREATE TABLE " . $table_name . " (
			id INT NOT NULL AUTO_INCREMENT,
			shortcode VARCHAR(200) NOT NULL,
			valueset VARCHAR(200) NOT NULL,
			val TEXT NOT NULL,
			PRIMARY KEY  id (id),
			KEY shortcode (shortcode),
			KEY valueset (valueset)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
			
		// record the database version
		$dypo_dbVersion = "1.0";
		$dypo_options['dypo_dbVersion'] = $dypo_dbVersion;
		update_option( DYPO_OPTIONS, $dypo_options );
		
		// now, dump some values in for the first time, so that the
		// admin page isn't empty when they first go there.
		$wpdb->insert( $table_name, array( 'shortcode' => 'shortcode1', 'valueset'=>'default', 'val'=>'Default Value 1') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'shortcode2', 'valueset'=>'default', 'val'=>'Default Value 2') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'urlvar', 'valueset'=>'1', 'val'=>'ExampleURLVariable1') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'shortcode1', 'valueset'=>'1', 'val'=>'Example Value 1') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'shortcode2', 'valueset'=>'1', 'val'=>'Example Value 2') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'urlvar', 'valueset'=>'2', 'val'=>'ExampleURLVariable2') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'shortcode1', 'valueset'=>'2', 'val'=>'Example Value 3') );
		$wpdb->insert( $table_name, array( 'shortcode' => 'shortcode2', 'valueset'=>'2', 'val'=>'Example Value 4') );

	} else {
		// the table exists, maybe in future versions, we'll need to change its structure here
	}
}

?>
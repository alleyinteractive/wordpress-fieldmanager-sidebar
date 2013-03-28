<?php
/**
 * @package Fieldmanager
 * @subpackage Sidebar
 * @version 0.1
 */
/*
Plugin Name: Fieldmanager Sidebar
Plugin URI: http://github.com/willgladstone/fieldmanager-sidebar
Description: A plugin that allows the addition of a sidebar & widgets to individual posts
Author: Will Gladstone
Version: 0.1
Author URI: http://www.alleyinteractive.com/
*/

function fieldmanager_sidebar_init(){
	require_once( dirname( __FILE__ ) . '/php/class-fieldmanager-sidebar.php' ); //Alter this. to your new plugin class
	require_once( dirname( __FILE__ ) . '/php/class-plugin-dependency.php' );
	require_once( dirname( __FILE__ ) . '/functions.php' );
}
add_action('plugins_loaded', 'fieldmanager_sidebar_init');


function fieldmanager_sidebar_dependency() {
	$fieldmanager_dependency = new Plugin_Dependency( 'Fieldmanager Sidebar', 'Fieldmanager', 'https://github.com/netaustin/wordpress-fieldmanager' ); //Change your plugin title here
	if( !$fieldmanager_dependency->verify() ) {
		// Cease activation
	 	die( $fieldmanager_dependency->message() );
	}
}
register_activation_hook( __FILE__, 'fieldmanager_sidebar_dependency' );

/**
 * Get the base URL for this plugin.
 * @return string URL pointing to Fieldmanager Plugin top directory.
 */
function fieldmanager_sidebar_get_baseurl() {
	return plugin_dir_url( __FILE__ );
}
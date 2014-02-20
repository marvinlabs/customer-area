<?php
/*
Plugin Name: Customer Area
Plugin URI: http://customer-area.marvinlabs.com
Version: 4.1.2
Description: Customer area give your customers the possibility to get a page on your site where they can access private content. 
Author: MarvinLabs
Author URI: http://www.marvinlabs.com
Text Domain: cuar
Domain Path: /languages
*/

/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if ( !defined( 'CUAR_PLUGIN_DIR' ) ) define( 'CUAR_PLUGIN_DIR', 	plugin_dir_path( __FILE__ ) );
if ( !defined( 'CUAR_INCLUDES_DIR' ) ) define( 'CUAR_INCLUDES_DIR', 	CUAR_PLUGIN_DIR . '/includes' );

define( 'CUAR_LANGUAGE_DIR', 		'customer-area/languages' );

define( 'CUAR_PLUGIN_URL', 			WP_PLUGIN_URL . '/customer-area/' ); // plugin_dir_url( __FILE__ ) );
define( 'CUAR_SCRIPTS_URL', 		CUAR_PLUGIN_URL . 'scripts' );
define( 'CUAR_ADMIN_THEME', 		'plugin%%default-wp38' );
define( 'CUAR_FRONTEND_THEME', 		'plugin%%default-v4' );
define( 'CUAR_PLUGIN_FILE', 		'customer-area/customer-area.php' );

define( 'CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION', FALSE );
//define( 'CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION', '2.1.0' ); 

/**
 * A function for debugging purposes
 */
if ( !function_exists( 'cuar_log_debug' ) ) {
function cuar_log_debug( $message ) {
	if (WP_DEBUG === true){
		if( is_array( $message ) || is_object( $message ) ){
			$msg = "CUAR \t" . print_r( $message, true );
		} else {
			$msg = "CUAR \t" . $message;
		}

		// ChromePhp::log( $msg );
		error_log( $msg );
	}
}
}

// Core classes
include_once( CUAR_INCLUDES_DIR . '/core-classes/settings.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/plugin.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/theme-utils.class.php' );

// Core addons
include_once( CUAR_INCLUDES_DIR . '/core-addons/admin-area/admin-area-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/help/help-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/post-owner/post-owner-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/capabilities/capabilities-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-pages/customer-pages-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/status/status-addon.class.php' );

// Core content types
include_once( CUAR_INCLUDES_DIR . '/core-addons/private-page/private-page-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/private-file/private-file-addon.class.php' );

// Core pages
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-home/customer-home-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-dashboard/customer-dashboard-addon.class.php' );

include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-account-home/customer-account-home-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-account/customer-account-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-logout/customer-logout-addon.class.php' );

include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-private-files-home/customer-private-files-home-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-private-files/customer-private-files-addon.class.php' );

include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages-home/customer-private-pages-home-addon.class.php' );
include_once( CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages/customer-private-pages-addon.class.php' );

// Template functions
include_once( CUAR_INCLUDES_DIR . '/functions/functions-general.php' );
include_once( CUAR_INCLUDES_DIR . '/functions/functions-private-content.php' );
include_once( CUAR_INCLUDES_DIR . '/functions/functions-private-files.php' );

// Start the plugin!
global $cuar_plugin;
$cuar_plugin = new CUAR_Plugin();
$cuar_plugin->run();

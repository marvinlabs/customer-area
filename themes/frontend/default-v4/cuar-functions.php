<?php 

if ( !function_exists( 'cuar_load_theme_scripts' ) ) {
	
	/**
	 * Load theme particular scripts
	 */
	function cuar_load_theme_scripts( $cuar_plugin ) {	
		$cuar_plugin->enable_library( 'bootstrap.dropdown' );		
	}
	add_action('cuar_before_addons_init', 'cuar_load_theme_scripts');
}
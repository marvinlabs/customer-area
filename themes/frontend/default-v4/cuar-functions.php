<?php 

if ( !function_exists( 'cuar_load_theme_scripts' ) ) {
	
	/**
	 * Load theme particular scripts
	 */
	function cuar_load_theme_scripts() {
		global $cuar_plugin;	
		$cuar_plugin->enable_library( 'bootstrap.dropdown' );		
		$cuar_plugin->enable_library( 'bootstrap.collapse' );		
	}
	add_action('wp_enqueue_scripts', 'cuar_load_theme_scripts');
}



if ( !function_exists( 'cuar_enable_bootstrap_nav_walker' ) ) {
	function cuar_enable_bootstrap_nav_walker( $args ) {
		require_once( CUAR_PLUGIN_DIR . '/libs/wp-bootstrap-navwalker/wp_bootstrap_navwalker.php' );		
		$new_args = $args;
		
		$new_args['depth'] = 2;
		$new_args['container'] = 'div';
		$new_args['container_class'] = 'collapse navbar-collapse cuar-nav-container';
		$new_args['menu_class'] = 'nav navbar-nav';
		$new_args['fallback_cb'] = 'wp_bootstrap_navwalker::fallback';
		$new_args['walker'] = new wp_bootstrap_navwalker();		
		
		return $new_args;
	}
	add_filter( 'cuar_get_main_menu_args', 'cuar_enable_bootstrap_nav_walker' );
}
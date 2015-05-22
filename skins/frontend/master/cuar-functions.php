<?php 

if ( !function_exists( 'cuar_load_theme_scripts' ) ) {
	
	/**
	 * Load theme particular scripts
	 */
	function cuar_load_skin_scripts() {
		$cuar_plugin = CUAR_Plugin::get_instance();
        $cuar_plugin->enable_library( 'bootstrap.affix' );
        $cuar_plugin->enable_library( 'bootstrap.alert' );
        $cuar_plugin->enable_library( 'bootstrap.button' );
        $cuar_plugin->enable_library( 'bootstrap.carousel' );
		$cuar_plugin->enable_library( 'bootstrap.collapse' );
		$cuar_plugin->enable_library( 'bootstrap.dropdown' );
        $cuar_plugin->enable_library( 'bootstrap.modal' );
        $cuar_plugin->enable_library( 'bootstrap.popover' );
        $cuar_plugin->enable_library( 'bootstrap.scrollspy' );
        $cuar_plugin->enable_library( 'bootstrap.tab' );
        $cuar_plugin->enable_library( 'bootstrap.tooltip' );
        $cuar_plugin->enable_library( 'bootstrap.transition' );

        wp_enqueue_script('master-main', CUAR_PLUGIN_URL . '/skins/frontend/master/assets/js/main.js', array('jquery'), $cuar_plugin->get_version());
	}
	add_action('wp_enqueue_scripts', 'cuar_load_skin_scripts');
}



if ( !function_exists( 'cuar_enable_bootstrap_nav_walker' ) ) {
	
	/**
	 * Use the bootstrap navwalker for our navigation menu to output bootstrap-friendly HTML.
	 */
	function cuar_enable_bootstrap_nav_walker( $args ) {
		require_once( CUAR_PLUGIN_DIR . '/libs/wp-bootstrap-navwalker/wp_bootstrap_navwalker.php' );		
		$new_args = $args;
		
		$new_args['depth'] = 2;
		$new_args['container'] = 'div';
		$new_args['container_class'] = 'cuar-nav-container cuar-collapse cuar-navbar-collapse';
		$new_args['menu_class'] = 'cuar-nav cuar-navbar';
		$new_args['fallback_cb'] = 'wp_bootstrap_navwalker::fallback';
		$new_args['walker'] = new wp_bootstrap_navwalker();		
		
		return $new_args;
	}
	add_filter( 'cuar/core/page/nav-menu-args', 'cuar_enable_bootstrap_nav_walker' );
}
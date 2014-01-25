<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CUAR_TemplateFunctions' ) ) :

/**
 * Gathers some helper functions to facilitate some theme customisations
*/
class CUAR_TemplateFunctions {

	public static function print_customer_area_menu() {
		global $cuar_plugin;
		$cp_addon = $cuar_plugin->get_addon( 'customer-page' ); 
		$cp_addon->print_main_menu_on_single_private_content();
	}
	
	public static function the_owner( $post_id = 0 ) {
		echo get_the_owner( $post_id );
	}
	
	public static function get_the_owner( $post_id = 0 ) {
		global $cuar_plugin;
		$po_addon = $cuar_plugin->get_addon( 'post-owner' ); 
		
		$post_id = $post_id==0 ? get_the_ID() : $post_id;		
		$owner_name = $po_addon->get_post_owner_displayname( $post_id );
		return apply_filters( 'cuar_the_owner', $owner_name, $post_id );
	}
}

endif; // class_exists CUAR_TemplateFunctions
<?php
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

if (!class_exists('CUAR_PrivatePageFrontendInterface')) :

/**
 * Frontend interface for private files
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivatePageFrontendInterface {
	
	public function __construct( $plugin, $private_page_addon ) {
		$this->plugin = $plugin;
		$this->private_page_addon = $private_page_addon;

		if ( $plugin->get_option( CUAR_PrivatePageAdminInterface::$OPTION_ENABLE_ADDON ) ) {	
			// Optionally output the file links in the post footer area
			if ( $this->plugin->get_option( CUAR_PrivatePageAdminInterface::$OPTION_SHOW_AFTER_POST_CONTENT ) ) {
				add_filter( 'the_content', array( &$this, 'after_post_content' ), 3000 );
			}		
					
			add_action( 'cuar_customer_area_content', array( &$this, 'print_customer_area_content' ), 10 );

			add_filter( 'cuar_customer_page_actions', array( &$this, 'add_actions' ), 10 );
			add_action( 'cuar_customer_area_content_show-private-pages', array( &$this, 'handle_show_private_pages_actions' ) );
			
			add_filter( "get_previous_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
			add_filter( "get_next_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
	
			add_action( 'wp_head', array( &$this, 'load_scripts' ) );
		}
	}

	/*------- FUNCTIONS TO PRINT IN THE FRONTEND ---------------------------------------------------------------------*/
	
	public function add_actions( $actions ) {		
		$actions[ "show-private-pages" ] = apply_filters( 'cuar_show_private_pages_action', array(
				"slug"						=> "show-private-pages",
				"label"						=> __( 'Pages', 'cuar' ),
				"hint"						=> __( 'Show all private pages', 'cuar' ),
				"highlight_on_single_post"	=> 'cuar_private_page',
				"children"					=> array()
			) );
			
		return $actions;
	}
	
	public function handle_show_private_pages_actions() {		
		$display_mode = $this->plugin->get_option( CUAR_PrivatePageAdminInterface::$OPTION_PAGE_LIST_MODE );
		
		$po_addon = $this->plugin->get_addon('post-owner');
		$current_user_id = get_current_user_id();
		
		if ( $display_mode=='category' ) {			
			$page_categories = get_terms( 'cuar_private_page_category', array(
					'parent'		=> 0,
					'hide_empty'	=> 0
				) );			
		} else if ( $display_mode=='year' || $display_mode=='month' ) {
			// Get user pages
			$args = array(
					'post_type' 		=> 'cuar_private_page',
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'date',
					'order' 			=> 'DESC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
				);			
			
			$pages_query = new WP_Query( apply_filters( 'cuar_user_pages_query_parameters', $args ) );
		} else {
			// Get user pages
			$args = array(
					'post_type' 		=> 'cuar_private_page',
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
				);			
				
			$pages_query = new WP_Query( apply_filters( 'cuar_user_pages_query_parameters', $args ) );
		}		
			
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-page',
				"list_private_pages-by-{$display_mode}.template.php",
				'templates',
				"list_private_pages.template.php" ));
	}
	
	public function after_post_content( $content ) {
		// If not on a matching post type, we do nothing
		if ( !is_singular('cuar_private_page') ) return $content;		

		ob_start();
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-page',
				'private-page-after_post_content.template.php',
				'templates' ));	
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
  		return $content . $out;
	}
	
	public function print_customer_area_content() {			
		$po_addon = $this->plugin->get_addon('post-owner');
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		$current_user_id = get_current_user_id();
		
		// Get user pages
		$args = array(
				'post_type' 		=> 'cuar_private_page',
				'posts_per_page' 	=> $cp_addon->get_dashboard_item_limit(),
				'orderby' 			=> 'modified',
				'order' 			=> 'DESC',
				'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
			);		
		$pages_query = new WP_Query( apply_filters( 'cuar_user_pages_query_parameters', $args ) );
				
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-page',
				"list_latest_private_pages.template.php",
				'templates' ));
	}

	public function print_category_pages( $category, $item_template, $current_user_id, $breadcrumb_sep, $breadcrumb = "", $parent = null ) {
		if ( !$category ) return;

		$po_addon = $this->plugin->get_addon('post-owner');
		$hide_empty_categories = $this->plugin->get_option( CUAR_PrivatePageAdminInterface::$OPTION_HIDE_EMPTY_CATEGORIES );

		$args = array(
				'post_type' 		=> 'cuar_private_page',
				'posts_per_page' 	=> -1,
				'orderby' 			=> 'title',
				'order' 			=> 'ASC',
				'tax_query'			=> array( array(
						'taxonomy' 			=> 'cuar_private_page_category',
						'include_children'	=> false,
						'field'				=> 'slug',
						'terms'				=> $category->slug,
						'operator'			=> 'IN'
					) ),
				'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
			);

		$pages_query = new WP_Query( apply_filters( 'cuar_user_pages_query_parameters', $args ) );

		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-page',
				"list_private_pages-in-category.template.php",
				'templates' ));
		
		// Output children
		$children = get_terms( 'cuar_private_page_category', array(
				'parent'		=> $category->term_id,
				'hide_empty'	=> 0
			) );
		
		if ( empty( $children ) ) return;		
		foreach ( $children as $child ) {
			$this->print_category_pages( $child, $item_template, $current_user_id, $breadcrumb_sep, $heading, $category );
		}
	}

	/**
	 * Disable the navigation on the single page templates for private files
	 */
	// TODO improve this by getting the proper previous/next file for the same owner
	public function disable_single_post_navigation( $where, $in_same_cat, $excluded_categories ) {
		if ( get_post_type()=='cuar_private_page' )	return "WHERE 1=0";		
		return $where;
	}

	/**
	 * Loads the required javascript files (only when not in admin area)
	 */
	public function load_scripts() {
		if ( is_admin() ) return;

		if ( $this->plugin->is_customer_area_page() ) {
			wp_enqueue_script( 'jquery-ui-accordion' );
		}
	}
	
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_PrivatePageAddOn */
	private $private_page_addon;
}
	
endif; // if (!class_exists('CUAR_PrivatePageFrontendInterface')) :
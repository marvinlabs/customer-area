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

if (!class_exists('CUAR_PrivateFileFrontendInterface')) :

/**
 * Frontend interface for private files
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivateFileFrontendInterface {
	
	public function __construct( $plugin, $private_file_addon ) {
		$this->plugin = $plugin;
		$this->private_file_addon = $private_file_addon;

		if ( $plugin->get_option( CUAR_PrivateFileAdminInterface::$OPTION_ENABLE_ADDON ) ) {
			// Optionally output the file links in the post footer area
			if ( $this->plugin->get_option( CUAR_PrivateFileAdminInterface::$OPTION_SHOW_AFTER_POST_CONTENT ) ) {
				add_filter( 'the_content', array( &$this, 'after_post_content' ), 3000 );
			}		
		
			add_filter( 'cuar_customer_page_actions', array( &$this, 'add_actions' ), 20 );
			add_action( 'cuar_customer_area_content_show-private-files', array( &$this, 'handle_show_private_files_actions' ) );

			add_action( 'cuar_customer_area_content', array( &$this, 'print_customer_area_content' ), 10 );
	
			add_filter( "get_previous_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
			add_filter( "get_next_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
	
			add_action( 'wp_head', array( &$this, 'load_scripts' ) );
		}
	}

	/*------- FUNCTIONS TO PRINT IN THE FRONTEND ---------------------------------------------------------------------*/
	
	public function add_actions( $actions ) {		
		$actions[ "show-private-files" ] = apply_filters( 'cuar_show_private_files_action', array(
				"slug"						=> "show-private-files",
				"label"						=> __( 'Files', 'cuar' ),
				"hint"						=> __( 'Show all private files', 'cuar' ),
				"highlight_on_single_post"	=> 'cuar_private_file',
				"children"					=> array()
			) );
			
		return $actions;
	}
	
	public function handle_show_private_files_actions() {
		$display_mode = $this->plugin->get_option( CUAR_PrivateFileAdminInterface::$OPTION_FILE_LIST_MODE );

		$po_addon = $this->plugin->get_addon('post-owner');
		$current_user_id = get_current_user_id();
		
		if ( $display_mode=='category' ) {			
			$file_categories = get_terms( 'cuar_private_file_category', array(
					'parent'		=> 0,
					'hide_empty'	=> 0
				) );			
		} else if ( $display_mode=='year' || $display_mode=='month' ) {
			// Get user files
			$args = array(
					'post_type' 		=> 'cuar_private_file',
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'date',
					'order' 			=> 'DESC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
				);			
			
			$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );
		} else {
			// Get user files
			$args = array(
					'post_type' 		=> 'cuar_private_file',
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
				);			
				
			$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );
		}		
			
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-file',
				"list_private_files-by-{$display_mode}.template.php",
				'templates',
				"list_private_files.template.php" ));
	}

	public function print_customer_area_content() {
		$po_addon = $this->plugin->get_addon('post-owner');
		$cp_addon = $this->plugin->get_addon('customer-page');
		$current_user_id = get_current_user_id();
		
		// Get user files
		$args = array(
				'post_type' 		=> 'cuar_private_file',
				'posts_per_page' 	=> $cp_addon->get_dashboard_item_limit(),
				'orderby' 			=> 'modified',
				'order' 			=> 'DESC',
				'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
			);
		
		$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );
		
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-file',
				"list_latest_private_files.template.php",
				'templates' ));
	}
	
	public function after_post_content( $content ) {
		// If not on a matching post type, we do nothing
		if ( !is_singular('cuar_private_file') ) return $content;		

		ob_start();
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-file',
				'private-file-after_post_content.template.php',
				'templates' ));	
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
  		return $content . $out;
	}

	public function print_category_files( $category, $item_template, $current_user_id, $breadcrumb_sep, $breadcrumb = "", $parent = null ) {
		if ( !$category ) return;

		$po_addon = $this->plugin->get_addon('post-owner');
		$hide_empty_categories = $this->plugin->get_option( CUAR_PrivateFileAdminInterface::$OPTION_HIDE_EMPTY_CATEGORIES );

		$args = array(
				'post_type' 		=> 'cuar_private_file',
				'posts_per_page' 	=> -1,
				'orderby' 			=> 'title',
				'order' 			=> 'ASC',
				'tax_query'			=> array( array(
						'taxonomy' 			=> 'cuar_private_file_category',
						'include_children'	=> false,
						'field'				=> 'slug',
						'terms'				=> $category->slug,
						'operator'			=> 'IN'
					) ),
				'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
			);

		$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );

		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-file',
				"list_private_files-in-category.template.php",
				'templates' ));
		
		// Output children
		$children = get_terms( 'cuar_private_file_category', array(
				'parent'		=> $category->term_id,
				'hide_empty'	=> 0
			) );
		
		if ( empty( $children ) ) return;		
		foreach ( $children as $child ) {
			$this->print_category_files( $child, $item_template, $current_user_id, $breadcrumb_sep, $heading, $category );
		}
	}
	
	/**
	 * Disable the navigation on the single page templates for private files
	 */
	// TODO improve this by getting the proper previous/next file for the same owner
	public function disable_single_post_navigation( $where, $in_same_cat, $excluded_categories ) {
		if ( get_post_type()=='cuar_private_file' )	return "WHERE 1=0";		
		return $where;
	}

	/**
	 * Loads the required javascript files (only when not in admin area)
	 */
	public function load_scripts() {
		if ( is_admin() ) return;

		$obj = get_queried_object();
		if ( is_page( $obj ) && $obj->ID==$this->plugin->get_customer_page_id() ) {
			wp_enqueue_script( 'jquery-ui-accordion' );
		}
	}
	
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_PrivateFileAddOn */
	private $private_file_addon;
}
	
endif; // if (!class_exists('CUAR_PrivateFileFrontendInterface')) :
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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon.class.php' );

require_once(dirname(__FILE__) . '/private-page-admin-interface.class.php');

if (!class_exists('CUAR_PrivatePageAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_PrivatePageAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'private-pages', '4.0.0' );
	}
	
	public function get_addon_name() {
		return __( 'Private Pages', 'cuar' );
	}

	public function run_addon( $plugin ) {
		if ( $this->is_enabled() ) {
			add_action( 'init', array( &$this, 'register_custom_types' ) );
			add_filter( 'cuar/core/post-types/content', array( &$this, 'register_private_post_types' ) );
			add_filter( 'cuar/core/types/content', array( &$this, 'register_content_type' ) );
			
			add_filter( 'cuar/core/permission-groups', array( &$this, 'get_configurable_capability_groups' ) );
		}
				
		// Init the admin interface if needed
		if ( is_admin() ) {
			$this->admin_interface = new CUAR_PrivatePageAdminInterface( $plugin, $this );
		}
	}	
	
	/**
	 * Set the default values for the options
	 * 
	 * @param array $defaults
	 * @return array
	 */
	public function set_default_options( $defaults ) {
		$defaults = parent::set_default_options($defaults);
		
		$defaults[ self::$OPTION_ENABLE_ADDON ] = true;
		
		return $defaults;
	}

	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function is_enabled() {
		return $this->plugin->get_option( self::$OPTION_ENABLE_ADDON );
	}
		
	/*------- FUNCTIONS TO ACCESS THE POST META ----------------------------------------------------------------------*/
	
	/**
	 * Get the number of times the page has been viewed
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function get_page_view_count( $post_id ) {
		$count = get_post_meta( $post_id, 'cuar_private_page_view_count', true );	
		if ( !$count || empty( $count ) ) return 0;	
		return intval( $count );
	}
	
	/**
	 * Get the number of times the page has been viewed
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function increment_page_view_count( $post_id ) {
		update_post_meta( $post_id, 
			'cuar_private_page_view_count', 
			$this->get_page_download_count( $post_id ) + 1 );
	}

	/*------- INITIALISATIONS ----------------------------------------------------------------------------------------*/
	
	public function get_configurable_capability_groups( $capability_groups ) {
		$capability_groups[ 'cuar_private_page' ] = array(
				'label'		=> __( 'Private Pages', 'cuar' ),
				'groups'	=> array(
						'back-office' => array(
								'group_name' => __( 'Back-office', 'cuar' ),
								'capabilities' => array(
										'cuar_pp_list_all' 	=> __( 'List all pages', 'cuar' ),
										'cuar_pp_edit' 		=> __( 'Create/Edit pages', 'cuar' ),
										'cuar_pp_delete'	=> __( 'Delete pages', 'cuar' ),
										'cuar_pp_read' 		=> __( 'Access pages', 'cuar' ),
										'cuar_pp_manage_categories' 	=> __( 'Manage categories', 'cuar' ),
										'cuar_pp_edit_categories' 		=> __( 'Edit categories', 'cuar' ),
										'cuar_pp_delete_categories' 	=> __( 'Delete categories', 'cuar' ),
										'cuar_pp_assign_categories' 	=> __( 'Assign categories', 'cuar' ),
									)
							),
						'front-office'	=> array(
								'group_name' => __( 'Front-office', 'cuar' ),
								'capabilities' => array(
										'cuar_view_pages'					=> __( 'View private pages', 'cuar' ),
										'cuar_view_any_cuar_private_page' 	=> __( 'View any private page', 'cuar' ),
									)
							)
					)
			);
		
		return $capability_groups;
	}
	
	/**
	 * Declare our content type
	 * @param array $types
	 * @return array
	 */
	public function register_content_type($types) {
		$types['cuar_private_page'] = array(
				'label-singular'		=> _x( 'Page', 'cuar_private_page', 'cuar' ),
				'label-plural'			=> _x( 'Pages', 'cuar_private_page', 'cuar' ),
				'content-page-addon'	=> 'customer-private-pages',
                'type'                  => 'content'
			);
		return $types;
	}
	
	/**
	 * Declare that our post type is owned by someone
	 * @param array $types
	 * @return array
	 */
	public function register_private_post_types($types) {
		$types[] = "cuar_private_page";
		return $types;
	}
	
	/**
	 * Register the custom post type for files and the associated taxonomies
	 */
	public function register_custom_types() {
		$labels = array(
				'name' 				=> _x( 'Private Pages', 'cuar_private_page', 'cuar' ),
				'singular_name' 	=> _x( 'Private Page', 'cuar_private_page', 'cuar' ),
				'add_new' 			=> _x( 'Add New', 'cuar_private_page', 'cuar' ),
				'add_new_item' 		=> _x( 'Add New Private Page', 'cuar_private_page', 'cuar' ),
				'edit_item' 		=> _x( 'Edit Private Page', 'cuar_private_page', 'cuar' ),
				'new_item' 			=> _x( 'New Private Page', 'cuar_private_page', 'cuar' ),
				'view_item' 		=> _x( 'View Private Page', 'cuar_private_page', 'cuar' ),
				'search_items' 		=> _x( 'Search Private Pages', 'cuar_private_page', 'cuar' ),
				'not_found' 		=> _x( 'No private pages found', 'cuar_private_page', 'cuar' ),
				'not_found_in_trash'=> _x( 'No private pages found in Trash', 'cuar_private_page', 'cuar' ),
				'parent_item_colon' => _x( 'Parent Private Page:', 'cuar_private_page', 'cuar' ),
				'menu_name' 		=> _x( 'Private Pages', 'cuar_private_page', 'cuar' ),
			);

		$args = array(
				'labels' 				=> $labels,
				'hierarchical' 			=> false,
				'supports' 				=> array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'excerpt'),
				'taxonomies' 			=> array( 'cuar_private_page_category' ),
				'public' 				=> true,
				'show_ui' 				=> true,
				'show_in_menu' 			=> false,
				'show_in_nav_menus' 	=> false,
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> true,
				'has_archive' 			=> false,
				'query_var' 			=> 'cuar_private_page',
				'can_export' 			=> false,
				'rewrite' 				=> false,
				'capabilities' 			=> array(
						'edit_post' 			=> 'cuar_pp_edit',
						'edit_posts' 			=> 'cuar_pp_edit',
						'edit_others_posts' 	=> 'cuar_pp_edit',
						'publish_posts' 		=> 'cuar_pp_edit',
						'read_post' 			=> 'cuar_pp_read',
						'read_private_posts' 	=> 'cuar_pp_list_all',
						'delete_post' 			=> 'cuar_pp_delete',
						'delete_posts' 			=> 'cuar_pp_delete'
					)
			);

		register_post_type( 'cuar_private_page', apply_filters( 'cuar/private-content/pages/register-post-type-args', $args ) );

		$labels = array(
				'name' 						=> _x( 'Page Categories', 'cuar_private_page_category', 'cuar' ),
				'singular_name' 			=> _x( 'Page Category', 'cuar_private_page_category', 'cuar' ),
				'search_items' 				=> _x( 'Search Page Categories', 'cuar_private_page_category', 'cuar' ),
				'popular_items' 			=> _x( 'Popular Page Categories', 'cuar_private_page_category', 'cuar' ),
				'all_items' 				=> _x( 'All Page Categories', 'cuar_private_page_category', 'cuar' ),
				'parent_item' 				=> _x( 'Parent Page Category', 'cuar_private_page_category', 'cuar' ),
				'parent_item_colon' 		=> _x( 'Parent Page Category:', 'cuar_private_page_category', 'cuar' ),
				'edit_item' 				=> _x( 'Edit Page Category', 'cuar_private_page_category', 'cuar' ),
				'update_item' 				=> _x( 'Update Page Category', 'cuar_private_page_category', 'cuar' ),
				'add_new_item' 				=> _x( 'Add New Page Category', 'cuar_private_page_category', 'cuar' ),
				'new_item_name' 			=> _x( 'New Page Category', 'cuar_private_page_category', 'cuar' ),
				'separate_items_with_commas'=> _x( 'Separate page categories with commas', 'cuar_private_page_category',
						'cuar' ),
				'add_or_remove_items' 		=> _x( 'Add or remove page categories', 'cuar_private_page_category', 'cuar' ),
				'choose_from_most_used' 	=> _x( 'Choose from the most used page categories', 'cuar_private_page_category',
						'cuar' ),
				'menu_name' 				=> _x( 'Page Categories', 'cuar_private_page_category', 'cuar' ),
			);
	  
		$args = array(
				'labels' 			=> $labels,
				'public' 			=> true,
				'show_in_menu' 		=> false,
				'show_in_nav_menus' => 'customer-area',
				'show_ui' 			=> true,
				'show_tagcloud' 	=> false,
				'show_admin_column'	=> true,
				'hierarchical' 		=> true,
				'query_var' 		=> true,
				'rewrite' 			=> false,
				'capabilities' 			=> array(
						'manage_terms' 		=> 'cuar_pp_manage_categories',
						'edit_terms' 		=> 'cuar_pp_edit_categories',
						'delete_terms' 		=> 'cuar_pp_delete_categories',
						'assign_terms' 		=> 'cuar_pp_assign_categories',
					)
			);
	  
		register_taxonomy( 'cuar_private_page_category', array( 'cuar_private_page' ), apply_filters( 'cuar/private-content/pages/category/register-taxonomy-args', $args ) );
	}

	// General options
	public static $OPTION_ENABLE_ADDON					= 'enable_private_pages';
	
	/** @var CUAR_PrivatePageAdminInterface */
	private $admin_interface;
}

// Make sure the addon is loaded
new CUAR_PrivatePageAddOn();

endif; // if (!class_exists('CUAR_PrivatePageAddOn')) 

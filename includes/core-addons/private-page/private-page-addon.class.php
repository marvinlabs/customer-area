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

require_once( CUAR_INCLUDES_DIR . '/addon.class.php' );
require_once( CUAR_INCLUDES_DIR . '/helpers/template-functions.class.php' );

require_once( dirname(__FILE__) . '/private-page-admin-interface.class.php' );
require_once( dirname(__FILE__) . '/private-page-frontend-interface.class.php' );

if (!class_exists('CUAR_PrivatePageAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_PrivatePageAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'private-pages', __( 'Private Pages', 'cuar' ), '2.0.0' );
	}

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;

		if ( $plugin->get_option( CUAR_PrivatePageAdminInterface::$OPTION_ENABLE_ADDON ) ) {
			add_action( 'init', array( &$this, 'register_custom_types' ) );
			add_filter( 'cuar_private_post_types', array( &$this, 'register_private_post_types' ) );
			
			add_action( 'init', array( &$this, 'add_post_type_rewrites' ) );
			add_filter( 'post_type_link', array( &$this, 'built_post_type_permalink' ), 1, 3);
			
			add_filter( 'cuar_configurable_capability_groups', array( &$this, 'declare_configurable_capabilities' ) );
		}
				
		// Init the admin interface if needed
		if ( is_admin() ) {
			$this->admin_interface = new CUAR_PrivatePageAdminInterface( $plugin, $this );
		} else {
			$this->frontend_interface = new CUAR_PrivatePageFrontendInterface( $plugin, $this );
		}
	}	
	
	/**
	 * When the plugin is upgraded
	 * 
	 * @param unknown $from_version
	 * @param unknown $to_version
	 */
	public function plugin_version_upgrade( $from_version, $to_version ) {
		// If upgrading from before 1.6.0 we should flush rewrite rules
		if ( $from_version<'1.6.0' ) {
			global $wp_rewrite;  
			$wp_rewrite->flush_rules();
		}
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
	
	public function declare_configurable_capabilities( $capability_groups ) {
		$capability_groups[] = array(
				'group_name' => __( 'Private Pages (back-office)', 'cuar' ),
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
			);

		$capability_groups[] = array(
				'group_name' => __( 'Private Pages', 'cuar' ),
				'capabilities' => array(
						'cuar_view_any_cuar_private_page' 		=> __( 'View any private page', 'cuar' ),
				)
		);
		
		return $capability_groups;
	}
	
	/**
	 * Declare that our post type is owned by someone
	 * @param unknown $types
	 * @return string
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
				'supports' 				=> array( 'title', 'editor', 'author', 'thumbnail', 'comments' ),
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
						'read_private_posts' 	=> 'cuar_pp_edit',
						'delete_post' 			=> 'cuar_pp_delete'
					)
			);

		register_post_type( 'cuar_private_page', apply_filters( 'cuar_private_page_post_type_args', $args ) );

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
				'hierarchical' 		=> true,
				'query_var' 		=> true,
				'rewrite' 			=> array(
						'slug' 				=> 'private-page-category'
					),
				'capabilities' 			=> array(
						'manage_terms' 		=> 'cuar_pp_manage_categories',
						'edit_terms' 		=> 'cuar_pp_edit_categories',
						'delete_terms' 		=> 'cuar_pp_delete_categories',
						'assign_terms' 		=> 'cuar_pp_assign_categories',
					)
			);
	  
		register_taxonomy( 'cuar_private_page_category', array( 'cuar_private_page' ), $args );
	}

	/**
	 * Add the rewrite rule for the private files.  
	 */
	function add_post_type_rewrites() {
		global $wp_rewrite;
		
		$pf_slug = 'private-page';
		
		$wp_rewrite->add_rewrite_tag('%cuar_private_page%', '([^/]+)', 'cuar_private_page=');
		$wp_rewrite->add_permastruct( 'cuar_private_page',
				$pf_slug . '/%year%/%monthnum%/%day%/%cuar_private_page%',
				false);
	}

	/**
	 * Build the permalink for the private files
	 * 
	 * @param unknown $post_link
	 * @param unknown $post
	 * @param unknown $leavename
	 * @return unknown|mixed
	 */
	function built_post_type_permalink( $post_link, $post, $leavename ) {		
		// Only change permalinks for private files
		if ( $post->post_type!='cuar_private_page') return $post_link;
	
		// Only change permalinks for published posts
		$draft_or_pending = isset( $post->post_status )
		&& in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );
		if( $draft_or_pending and !$leavename ) return $post_link;
	
		// Change the permalink
		global $wp_rewrite;
	
		$permalink = $wp_rewrite->get_extra_permastruct( 'cuar_private_page' );
		$permalink = str_replace( "%cuar_private_page%", $post->post_name, $permalink );
	
		$post_date = strtotime( $post->post_date );
		$permalink = str_replace( "%year%", 	date( "Y", $post_date ), $permalink );
		$permalink = str_replace( "%monthnum%", date( "m", $post_date ), $permalink );
		$permalink = str_replace( "%day%", 		date( "d", $post_date ), $permalink );
		
		$permalink = home_url() . "/" . user_trailingslashit( $permalink );
		$permalink = str_replace( "//", "/", $permalink );
		$permalink = str_replace( ":/", "://", $permalink );
	
		return $permalink;
	}
	
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_PrivatePageAdminInterface */
	private $admin_interface;

	/** @var CUAR_PrivatePageFrontendInterface */
	private $frontend_interface;
}

// Make sure the addon is loaded
global $cuar_pp_addon;
$cuar_pp_addon = new CUAR_PrivatePageAddOn();

endif; // if (!class_exists('CUAR_PrivatePageAddOn')) 

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


if (!class_exists('CUAR_CustomerPagesAddOn')) :

/**
 * Add-on to show the customer page
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerPagesAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'customer-pages', __( 'Customer Pages', 'cuar' ), '4.0.0' );
	}

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;

		// Add a WordPress menu 
		register_nav_menus( array(
				'cuar_main_menu' => 'Customer Area Navigation Menu'
			) );
		
		// Settings
		add_filter( 'cuar_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 6, 1 );
		
		add_action( 'cuar_addon_print_settings_cuar_core', array( &$this, 'print_core_settings' ), 50, 2 );
		add_filter( 'cuar_addon_validate_options_cuar_core', array( &$this, 'validate_core_settings' ), 50, 3 );
		
		add_action( 'cuar_addon_print_settings_cuar_customer_pages', array( &$this, 'print_pages_settings' ), 50, 2 );
		add_filter( 'cuar_addon_validate_options_cuar_customer_pages', array( &$this, 'validate_pages_settings' ), 50, 3 );
		
		if ( $this->is_auto_menu_on_single_private_content_pages_enabled() ) {
			add_filter( 'the_content', array( &$this, 'get_main_menu_for_single_private_content' ), 50 );
		}
		
		if ( $this->is_auto_menu_on_customer_area_pages_enabled() ) {
			add_filter( 'the_content', array( &$this, 'get_main_menu_for_customer_area_pages' ), 51 );
		}
	}	
	
	public function set_default_options($defaults) {
		$defaults [self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT] 	= false;
		$defaults [self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES] 		= true;
		$defaults [self::$OPTION_CATEGORY_ARCHIVE_SLUG] 				= _x( 'category', 'Private content category archive slug', 'cuar' );
		$defaults [self::$OPTION_DATE_ARCHIVE_SLUG] 					= _x( 'archive', 'Private content date archive slug', 'cuar' );
		return $defaults;
	}
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	/** 
	 * Get the WordPress page ID corresponding to a given customer area page slug
	 * 
	 * @param string $slug	The customer area identifier of the page we are looking for
	 * 
	 * @return mixed|boolean
	 */
	public function get_page_id( $slug ) {
		if ( empty( $slug ) ) return false;
		
		$page_id = $this->plugin->get_option( $this->get_page_option_name( $slug ), -1 );
		return $page_id<0 ? false : $page_id;
	}
	
	public function is_auto_menu_on_single_private_content_pages_enabled() {
		return $this->plugin->get_option( self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT );
	}
	
	public function is_auto_menu_on_customer_area_pages_enabled() {
		return $this->plugin->get_option( self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES );
	}
	
	public function get_category_archive_slug() {
		return $this->plugin->get_option( self::$OPTION_CATEGORY_ARCHIVE_SLUG );
	}
	
	public function get_date_archive_slug() {
		return $this->plugin->get_option( self::$OPTION_DATE_ARCHIVE_SLUG );
	}
	
	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	/**
	 * List all the pages expected by Customer Area and its add-ons. 
	 * 
	 * @return array see structure for each item in function get_customer_area_page
	 */
	public function get_customer_area_pages() {
		if ( $this->pages==null) {
			$this->pages = apply_filters( 'cuar_customer_pages', array() );
		}
			
		return $this->pages;
	}
	
	/**
	 * List all the pages expected by Customer Area and its add-ons. 
	 * 
	 * Structure of the item is
	 * 	'slug'					=> string
	 * 	'label' 				=> string
	 * 	'hint' 					=> string
	 * 	'title' 				=> string
	 * 	'parent_slug'			=> string (optional, defaults to 'dashboard')
	 * 	'friendly_post_type' 	=> string (optional, defaults to null)
	 *  'requires_login'		=> boolean (optional, defaults to true)
	 * 
	 * @return array
	 */
	public function get_customer_area_page( $slug ) {
		$customer_area_pages = $this->get_customer_area_pages();
		return isset( $customer_area_pages[ $slug ] ) ? $customer_area_pages[ $slug ] : false;
	}
	
	/**
	 * Are we currently viewing a customer area page?
	 * @return boolean
	 */
	public function is_customer_area_page( $page_id=0 ) {
		if ( $page_id<=0 ) { 
			$page_id = get_queried_object_id();
		}
		
		// We expect a page. You should not make customer area pages in posts or any other custom post type.
		if ( !is_page( $page_id ) ) return false;
		
		// Test if the current page is one of the root pages
		$customer_area_pages = $this->get_customer_area_pages();
		foreach ( $customer_area_pages as $p ) {
			if ( $this->get_page_id( $p['slug'] )==$page_id  ) return true;
		}
		
		// Not found
		return false;
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/
	
	public function get_main_navigation_menu( $echo=false ) {
		$out = '';
		
		if ( !is_user_logged_in() ) return $out;
		
		if ( has_nav_menu( 'cuar_main_menu' ) ) {
			$defaults = apply_filters( 'cuar_get_main_menu_args', array(
					'theme_location'  => 'cuar_main_menu',
					'menu'            => '',
					'container'       => 'div',
					'container_class' => '',
					'container_id'    => '',
					'menu_class'      => 'menu cuar-nav-menu',
					'menu_id'         => '',
					'echo'            => false,
					'fallback_cb'     => '',
					'before'          => '',
					'after'           => '',
					'link_before'     => '',
					'link_after'      => '',
					'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'depth'           => 0,
					'walker'          => ''
				));
			
			$out .= wp_nav_menu( $defaults );
		} else {
			// Get page IDs that are currently set
			$customer_area_pages = $this->get_customer_area_pages();
			$page_ids = array();
			
			// Filter out some pages (like logout, ...)
			$customer_area_pages = apply_filters( 'cuar_get_nav_customer_area_pages', $customer_area_pages );
			
			foreach ( $customer_area_pages as $slug => $p ) {
				$page_id = $this->get_page_id( $slug );
				if ( $page_id ) $page_ids[] = $page_id;
			}
			
			if ( empty( $page_ids ) ) return '';
			
			// Get the associated post objects
			$pages = get_posts( array(
					'post_type'			=> 'page', 
					'numberposts'		=> 0,
					'post__in' 			=> $page_ids,
					'orderby'			=> 'menu_order',
					'order'				=> 'ASC'
				) );
			
			$page_count = count( $pages );
			for ( $i=0; $i<$page_count; ++$i ) {
				$pages[$i] = wp_setup_nav_menu_item( $pages[$i] );
			}
			
			$args = array( 'theme_location'  => 'cuar_main_menu' );

			$defaults = apply_filters( 'cuar_get_main_menu_args', array(
					'theme_location'  => 'cuar_main_menu',
					'menu'            => '',
					'container'       => 'div',
					'container_class' => '',
					'container_id'    => '',
					'menu_class'      => 'menu cuar-nav-menu',
					'menu_id'         => '',
					'echo'            => false,
					'fallback_cb'     => '',
					'before'          => '',
					'after'           => '',
					'link_before'     => '',
					'link_after'      => '',
					'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'depth'           => 0,
					'walker'          => ''
			));
			
			$args = wp_parse_args( $args, $defaults );
			$args = apply_filters( 'cuar_get_main_menu_args', $args );
			$args = apply_filters( 'wp_nav_menu_args', $args );
			$args = (object) $args;
			
			_wp_menu_item_classes_by_context( $pages );

			$items = walk_nav_menu_tree( $pages, 1, $args );
			
			$out .= '<div class="menu-customer-area-container">';
			$out .= sprintf( '<ul id="%1$s" class="%2$s">%3$s</ul>', 'menu-customer-area', 'menu cuar-nav-menu', $items );
			$out .= '</div>';
		}
		
		if ( $echo ) echo $out;
		
		return $out;
	}
	
	/**
	 * Output the customer area navigation menu
	 * 
	 * @param unknown $content
	 * @return unknown|string
	 */
	public function get_main_menu_for_single_private_content( $content ) {		
		// Only on single private content pages
		$post_types = $this->plugin->get_private_post_types();
		$print_content = false;
		
		foreach ( $post_types as $type ) {
			if ( is_singular( $type ) ) {
				$print_content = true;
				break;
			}
		}
		
		if ( $print_content ) {
			$content = '<div class="cuar-menu-container">' . $this->get_main_navigation_menu() . '</div>' . $content;
		}
		
		return $content;
	}
	
	/**
	 * Output the customer area navigation menu
	 * 
	 * @param unknown $content
	 * @return unknown|string
	 */
	public function get_main_menu_for_customer_area_pages( $content ) {
		// Only on customer area pages
		if ( !$this->is_customer_area_page() ) return $content;

		$content = '<div class="cuar-menu-container">' . $this->get_main_navigation_menu() . '</div>' . $content;
		
		return $content;
	}

	/*------- SETTINGS ----------------------------------------------------------------------------------------------*/
	
	/** 
	 * Get the WordPress page ID corresponding to a given customer area page slug
	 * 
	 * @param string $slug	The customer area identifier of the page we are looking for
	 * 
	 * @return mixed|boolean
	 */
	public function get_page_option_name( $slug ) {
		return self::$OPTION_CUSTOMER_PAGE . $slug;
	}
	
	public function add_settings_tab( $tabs ) {
		$tabs[ 'cuar_customer_pages' ] = __( 'Site Pages', 'cuar' );
		return $tabs;
	}
	
	public function print_pages_settings($cuar_settings, $options_group) {			
		add_settings_section(
				'cuar_core_pages',
				__('Customer Pages', 'cuar'),
				array( &$this, 'print_page_settings_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		$customer_area_pages = $this->get_customer_area_pages();
		foreach ( $customer_area_pages as $p ) {
			add_settings_field(
					$this->get_page_option_name( $p['slug'] ),
					$p[ 'label' ],
					array( &$cuar_settings, 'print_post_select_field' ),
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					'cuar_core_pages',
					array(
							'option_id' 			=> $this->get_page_option_name( $p['slug'] ),
							'post_type'		 		=> 'page',
							'show_create_button'	=> true,
							'after'					=> isset( $p['hint'] ) && !empty( $p['hint'] ) ? '<p class="description">' . $p['hint'] . '</p>' : '' 
						)
				);
		}
	}
	
	public function print_core_settings($cuar_settings, $options_group) {	
		add_settings_section(
				'cuar_core_nav_menu',
				__('Main Navigation Menu', 'cuar'),
				array( &$this, 'print_nav_menu_settings_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);
		
		add_settings_field(
				self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES,
				__('Customer Area pages', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_nav_menu',
				array(
						'option_id' => self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES,
						'type' 		=> 'checkbox',
						'after'		=>  __( 'Automatically print the Customer Area navigation menu on the Customer Area pages.', 'cuar' )
										. '<p class="description">'
										. __( 'By checking this box, the menu will automatically be shown automatically on the Customer Area pages (the ones defined in the tab named "Site Pages"). '
											. 'It may however not appear at the place you would want it. If that is the case, you can refer to our documentation to see how to edit your theme.', 'cuar' )
										. '</p>' 
					)
			);
		
		add_settings_field(
				self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT,
				__('Private content single pages', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_nav_menu',
				array(
						'option_id' => self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT,
						'type' 		=> 'checkbox',
						'after'		=>  __( 'Automatically print the Customer Area navigation menu on private content single pages.', 'cuar' )
										. '<p class="description">'
										. __( 'By checking this box, the menu will automatically be shown automatically on the pages displaying a single private content (a private page or a private file for example). '
											. 'It may however not appear at the place you would want it. If that is the case, you can refer to our documentation to see how to edit your theme.', 'cuar' )
										. '</p>' 
					)
			);
		
		add_settings_section(
				'cuar_core_permalinks',
				__('Permalinks', 'cuar'),
				array( &$this, 'print_empty_settings_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_CATEGORY_ARCHIVE_SLUG,
				__('Category Archive', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_permalinks',
				array(
						'option_id' => self::$OPTION_CATEGORY_ARCHIVE_SLUG,
						'type' 		=> 'text',
						'is_large'	=> false,
						'after'		=> '<p class="description">'
								. __( 'Slug that is used in the URL for category archives of private content. For example, the list of files in the "my-awesome-category" category would look '
									. 'like:<br/>http://example.com/customer-area/files/<b>my-slug</b>/my-awesome-category', 'cuar' )
								. '</p>'
					)
			);

		add_settings_field(
				self::$OPTION_DATE_ARCHIVE_SLUG,
				__('Date Archive', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_permalinks',
				array(
						'option_id' => self::$OPTION_DATE_ARCHIVE_SLUG,
						'type' 		=> 'text',
						'is_large'	=> false,
						'after'		=> '<p class="description">'
								. __( 'Slug that is used in the URL for date archives of private content. For example, the list of files for 2014 would look ' 
									. 'like:<br/>http://example.com/customer-area/files/<b>my-slug</b>/2014', 'cuar' )
								. '</p>'
					)
			);
		
	}
	
	public function print_empty_settings_section_info() {
	}
	
	public function print_nav_menu_settings_section_info() {
		echo '<p class="cuar-section-info">' . __( 'Since version 4.0.0, Customer Area handles navigation using menus.', 'cuar' );		
		echo ' ' 
				. sprintf( __( 'You can customize the Customer Area menu in the <a href="%1$s">Appearance &raquo; Menus</a> panel. ' 
							. 'If you do not define any custom menu there, Customer Area will generate a default menu for you with all the pages '
							. 'you have set below.', 'cuar' ),
						admin_url( 'nav-menus.php' ) ) 
				. '</p>';
	}

	public function print_page_settings_section_info() {
		echo '<p class="cuar-section-info">' 
				. __( 'Since version 4.0.0, Customer Area is using various pages to show the content for your customers. Create those pages from here or simply indicate existing ones. ' , 'cuar' ) 
				. '</p>';
	}
	
	public function validate_core_settings($validated, $cuar_settings, $input) {
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT );
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES );

		$cuar_settings->validate_not_empty( $input, $validated, self::$OPTION_CATEGORY_ARCHIVE_SLUG );
		$cuar_settings->validate_not_empty( $input, $validated, self::$OPTION_DATE_ARCHIVE_SLUG );
		
		return $validated;
	}
	
	public function validate_pages_settings($validated, $cuar_settings, $input) {
		$customer_area_pages = $this->get_customer_area_pages();		
		
		// If we are requested to create the page, do it now
		foreach ( $customer_area_pages as $p ) {
			$option_id = $this->get_page_option_name( $p['slug'] );
			$create_button_name = $cuar_settings->get_submit_create_post_button_name( $option_id );
			
			if ( isset( $_POST[ $create_button_name ] ) ) {
				$existing_page_id = $this->get_page_id( $p['slug'] );
				
				if ( $existing_page_id>0 ) {
					wp_delete_post( $existing_page_id );
				}
				
				$page_id = apply_filters( 'cuar_do_create_page_' . $p['slug'], 0, null, null, null );	
				if ( $page_id>0 ) {
					$input[ $option_id ] = $page_id;
				}
			}
			
			$cuar_settings->validate_post_id( $input, $validated, $option_id );
		}
		
		$cuar_settings->flush_rewrite_rules();
		
		return $validated;
	}

	// Options
	public static $OPTION_CUSTOMER_PAGE = 'customer_page_';
	public static $OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT	= 'customer_page_auto_menu_on_single_content';
	public static $OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES	= 'customer_page_auto_menu_on_pages';
	public static $OPTION_CATEGORY_ARCHIVE_SLUG	= 'cuar_permalink_category_archive_slug';
	public static $OPTION_DATE_ARCHIVE_SLUG	= 'cuar_permalink_date_archive_slug';

	/** @var array */
	private $pages = null;
	
	/** @var CUAR_Plugin */
	private $plugin;
}

// Make sure the addon is loaded
new CUAR_CustomerPagesAddOn();

endif; // if (!class_exists('CUAR_CustomerPagesAddOn')) :
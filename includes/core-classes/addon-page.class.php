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
require_once( CUAR_INCLUDES_DIR . '/core-classes/addon-page-shortcode.class.php' );

if (!class_exists('CUAR_AbstractPageAddOn')) :

/**
 * The base class for addons that should render a page
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AbstractPageAddOn extends CUAR_AddOn {

	public function __construct( $addon_id = null, $addon_name = null, $min_cuar_version = null ) {
		parent::__construct( $addon_id, $addon_name, $min_cuar_version );
	}
	
	protected function set_page_parameters( $priority, $description ) {
		$this->page_priority = $priority;
		$this->page_description = $description;
		
		if ( !isset( $this->page_description['requires_login'] )) {
			$this->page_description['requires_login'] = true;
		}
		
		if ( !isset( $this->page_description['parent_slug'] )) {
			$this->page_description['parent_slug'] = null;
		}
	}
	
	protected function set_page_shortcode( $shortcode_name, $shorcode_params = array() ) {
		$this->shortcode = new CUAR_AddOnPageShortcode( $this, $shortcode_name, $shorcode_params );
	}
	
	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	protected abstract function get_page_addon_path();	
	
	public function register_page_description( $pages ) {
		if ( $this->page_description!=null ) { 
			$pages[ $this->get_slug() ] = $this->page_description;
		}
		
		return $pages;
	}
	
	public function get_slug() {
		return $this->page_description['slug'];
	}
	
	public function get_label() {
		return $this->page_description['label'];
	}
	
	public function get_title() {
		return $this->page_description['title'];
	}
	
	public function requires_login() {
		return $this->page_description['requires_login'];
	}
	
	public function get_page_id() {
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		return $cp_addon->get_page_id( $this->get_slug() );
	}
	
	public function get_page_url() {
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		return $cp_addon->get_page_url( $this->get_slug() );
	}
	
	/**
	 * Create the corresponding WordPress page.
	 * 
	 * @param string $parent_slug The Customer Area identifier (@see CUAR_CustomerPageAddOn::get_customer_area_page) for the parent page. 
	 * @param string $page_title If null, the page title will be taken from the page description array
	 * @param string $page_content If null, and if we have a shortcode, the content will simply be the default shortcode declaration
	 *  
	 * @return Ambigous <number, WP_Error, unknown>
	 */
	public function create_default_page( $void ) {		
		$page_data = array(
				'post_status' 		=> 'publish',
				'post_type' 		=> 'page',
				'comment_status' 	=> 'closed',
				'menu_order'		=> $this->page_priority
			);
		
		// If a slug is specified, we will try to find that page from the options
		if ( $this->page_description['parent_slug']!=null && !empty( $this->page_description['parent_slug'] ) ) {
			$cp_addon = $this->plugin->get_addon( 'customer-pages' );
			$page_id = $cp_addon->get_page_id( $this->page_description['parent_slug'] );
			
			if ( $page_id>0 ) {
				$page_data['post_parent'] = $page_id;
			}
		}
		
		// If title or descriptions are not specified, we'll give some defaults
		$page_data['post_title'] = $this->get_title();		
		if ( empty( $page_data['post_title'] ) ) $page_data['post_title'] = $this->get_label();
		if ( empty( $page_data['post_title'] ) ) $page_data['post_title'] = $this->get_slug();
		
		if ( $this->shortcode!=null ) {
			$page_data['post_content'] = $this->shortcode->get_sample_shortcode();
		} else {
			$page_data['post_content'] = '';
		}
		
		// Create the page
		$page_id = wp_insert_post( $page_data );
		
		return $page_id;
	}
	
	public function print_page( $args = array(), $shortcode_content = '' ) {
		if ( $this->page_description['requires_login'] && !is_user_logged_in() ) {
			$this->plugin->login_then_redirect_to_page( $this->get_slug() );	
		} else {
			include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-classes',
					'customer-page.template.php',
					'templates' ));
		}
	}
	
	public function has_page_sidebar() {
		return false;
	}
	
	public function print_page_header( $args = array(), $shortcode_content = '' ) {
		$this->print_page_part( 'header' );
	}
	
	public function print_page_sidebar( $args = array(), $shortcode_content = '' ) {
		if ( $this->has_page_sidebar() ) {
			$this->print_page_part( 'sidebar' );
		}
	}
	
	public function print_page_content( $args = array(), $shortcode_content = '' ) {
		$this->print_page_part( 'content' );
	}
	
	public function print_page_footer( $args = array(), $shortcode_content = '' ) {
		$this->print_page_part( 'footer' );
	}

	protected function print_page_part( $part ) {
		$slug = $this->get_slug();
		
		$template = $this->plugin->get_template_file_path(
				$this->get_page_addon_path(),
				$slug . "-" . $part . ".template.php",
				'templates' );

		do_action( 'cuar_before_page_' . $part );
		do_action( 'cuar_before_page_' . $part . '_' . $slug );		
		
		if ( !empty( $template ) ) include( $template );
		
		do_action( 'cuar_after_page_' . $part . '_' . $slug );
		do_action( 'cuar_after_page_' . $part );
		
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

	public function run_addon( $plugin ) {
		add_filter( 'cuar_customer_pages', array( &$this, 'register_page_description' ), $this->page_priority );
		add_filter( 'cuar_do_create_page_' . $this->get_slug(), array( &$this, 'create_default_page' ), 10, 4 );
	}
	
	protected function register_sidebar( $id, $name ) {
		register_sidebar( array(
				'id' 				=> $id,
				'name' 				=> $name,
				'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
				'after_widget' 		=> "</aside>",
				'before_title' 		=> '<h3 class="widget-title">',
				'after_title' 		=> '</h3>',
			) );
	}
	
	/** @var int order for the page */
	protected $page_priority = 10;
	
	/** @var array describes the page */
	protected $page_description = null;
	
	/** @var CUAR_AddOnPageShortcode shortcode that displays the page */
	protected $shortcode = null;
}

endif; // CUAR_AbstractPageAddOn
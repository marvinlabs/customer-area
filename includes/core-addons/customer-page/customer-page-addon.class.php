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

require_once( dirname(__FILE__) . '/customer-page-shortcode.class.php' );
// require_once( dirname(__FILE__) . '/private-file-frontend-interface.class.php' );
// require_once( dirname(__FILE__) . '/private-file-theme-utils.class.php' );

if (!class_exists('CUAR_CustomerPageAddOn')) :

/**
 * Add-on to show the customer page
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerPageAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'customer-page', __( 'Customer Page', 'cuar' ), '2.0.0' );
	}

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;
		$this->customer_page_shortcode = new CUAR_CustomerPageShortcode( $plugin );

		add_filter( 'cuar_customer_page_actions', array( &$this, 'add_home_action' ), 1 );
		add_filter( 'cuar_customer_page_actions', array( &$this, 'add_logout_action' ), 1000 );

		// Settings
		add_action( 'cuar_addon_print_settings_cuar_core', array( &$this, 'print_settings' ), 50, 2 );
		add_filter( 'cuar_addon_validate_options_cuar_core', array( &$this, 'validate_settings' ), 50, 3 );
		
		if ( $this->get_customer_page_id() < 0 && !isset( $_GET['run-setup-wizard'] )) {
			add_action( 'admin_notices', array( &$this, 'show_no_page_warning' ), 5 );
		}
		
		if ( $plugin->get_option( self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT ) ) {
			add_filter( 'the_content', array( &$this, 'get_main_menu_for_single_private_content' ), 50 );
		}
	}	
	
	/*------- INITIALISATIONS ---------------------------------------------------------------------------------------*/
	
	public function show_no_page_warning() {
		$warning = __( 'We could not detect the Customer Area page on your site. This may be because you have not yet setup the plugin, or because you are upgrading from an older version.', 'cuar' );
		$warning .= '<ul><li>&raquo; ';
		$warning .= sprintf( __( 'If you already have this page, just visit the <a href="%s">plugin settings</a> to set it manually.', 'cuar' ), admin_url( 'admin.php?page=' .  CUAR_Settings::$OPTIONS_PAGE_SLUG ) );
		$warning .= '</li><li>&raquo; ';
		$warning .= sprintf( __( 'If you have not yet setup the plugin, we have a <a href="%s">quick setup wizard</a>', 'cuar' ), admin_url( 'admin.php?page=' .  CUAR_Settings::$OPTIONS_PAGE_SLUG . '&run-setup-wizard=1' ) );
		$warning .= '</li></ul>';
		
		$this->plugin->add_admin_notice( $warning );		
	}
	
	public function add_home_action( $actions ) {
		$actions['show-dashboard'] = apply_filters( 'cuar_home_action', array(
				"slug"		=> 'show-dashboard',
				"url"		=> $this->get_customer_page_url(),
				"label"		=> __( 'Dashboard', 'cuar' ),
				"hint"		=> __( 'Your customer area welcome page', 'cuar' )
			) );
		return $actions;
	}
	
	public function add_logout_action( $actions ) {
		$actions['logout'] = apply_filters( 'cuar_logout_action', array(
				"url"		=> wp_logout_url( $this->get_customer_page_url() ),
				"label"		=> __( 'Logout', 'cuar' ),
				"hint"		=> __( 'Disconnect from your customer area', 'cuar' )
		) );
		return $actions;
	}
	
	public function get_customer_page_id() {
		return $this->plugin->get_option( self::$OPTION_CUSTOMER_PAGE_POST_ID );
	}
	
	public function set_customer_page_id( $post_id ) {
		return $this->plugin->update_option( self::$OPTION_CUSTOMER_PAGE_POST_ID, $post_id );
	}
	
	public function get_customer_page_url( $action = '', $redirect = '' ) {
		$url = trailingslashit( get_permalink( $this->get_customer_page_id() ) );
		
		if ( !empty( $action ) && !empty( $redirect ) ) {
			$url .= '?action=' . $action . '&redirect=' . $redirect; 
		} else if ( !empty( $redirect ) ) {
			$url .= '?redirect=' . $redirect; 
		} else if ( !empty( $action ) ) {
			$url .= '?action=' . $action; 
		} 
		
		return $url;
	}

	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/
	
	public function print_customer_area() {
		$cp_addon = $this->plugin->get_addon('customer-page');
		if ( $cp_addon->get_customer_page_id() <= 0 ) {
			$cp_addon->set_customer_page_id( get_the_ID() );
		}
		
		// If not logged-in, we should do so.
		if ( !is_user_logged_in() ) {
			$this->print_customer_area_for_guest();
		} else {
			$this->print_customer_area_for_user();
		}
	}

	// Build the HTML output for a guest user
	private function print_customer_area_for_guest() {
		if ( isset( $_GET['redirect'] ) ) {
			$redirect_to_url = $_GET['redirect'];
		} else {
			$redirect_to_url = $this->get_customer_page_url();
		}
		 
		do_action( 'cuar_before_login_required_template' );
		 
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-page',
				'customer-page-login-required.template.php',
				'templates' ));
	
		do_action( 'cuar_after_login_required_template' );
	}

	// Build the HTML output for a logged-in user.
	private function print_customer_area_for_user() {	
		$this->prepare_main_menu();
		
		do_action( 'cuar_before_customer_area_template' );

		$this->print_header();

		do_action( 'cuar_before_customer_area_content' ); 

		if ( isset( $this->top_level_action ) ) {
			do_action( 'cuar_customer_area_content_' . $this->top_level_action['slug'] ); 
		} else if ( !empty( $this->current_action ) ) {
			do_action( 'cuar_customer_area_content_' . $this->current_action );
		} else{
			do_action( 'cuar_customer_area_content' );
		}
		
		do_action( 'cuar_after_customer_area_content' ); 		 
		do_action( 'cuar_after_customer_area_template' );
	}
	
	private function prepare_main_menu() {
		global $current_user;
		
		if ( !empty( $this->actions ) ) return;
		
		$post_types = $this->plugin->get_private_post_types();
		$is_viewing_single_type = ( is_singular() && in_array( get_post_type(), $post_types ) ) ? get_post_type() : null;
		
		$this->current_action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		$this->top_level_action = null;
		$this->title = '';
		$this->actions = apply_filters( 'cuar_customer_page_actions', array() );
		
		$base_url = trailingslashit( $this->get_customer_page_url() );		
		if (!empty($this->actions)) {
			foreach ($this->actions as $action) {
				if ( (isset( $action["slug"] ) && $this->current_action==$action['slug']) ) {
					$this->title = $action["label"];
					$this->top_level_action = $action;
					break;
				} else if ( isset( $action['children'] ) && array_key_exists( $this->current_action, $action['children'] ) ) {
					$href = $this->get_customer_page_url( $action["slug"] );
					$this->title = sprintf( '<a href="%1$s">%2$s</a> &raquo; %3$s',
							esc_attr( $href ), $action["label"], $action['children'][ $this->current_action ]['label'] );
					break;
				} else if ( isset( $action["highlight_on_single_post"] ) && $is_viewing_single_type!=null && $is_viewing_single_type==$action["highlight_on_single_post"] ) {
					$this->current_action = $action["slug"];
				}
			}
		}
		
		if ( empty( $this->title ) ) {
			$this->title = apply_filters( 'cuar_customer_page_default_title', sprintf( __('Hello %s,', 'cuar'), $current_user->display_name ) );
		}
		
	}
	
	public function print_main_menu() {
		$this->prepare_main_menu();
		
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-page',
				'customer-page-menu.template.php',
				'templates' ));
	}

	public function print_header() {
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-page',
				'customer-page-header.template.php',
				'templates' ));
	}

	public function get_main_menu_for_single_private_content( $content ) {
		// Only on single private content pages
		$post_types = $this->plugin->get_private_post_types();
		if ( !is_singular() || !in_array( get_post_type(), $post_types ) ) return $content;

		ob_start();		
		$this->print_main_menu();
		$menu = ob_get_contents();
		ob_end_clean();
		
		$content = '<div class="cuar-menu-single-container">' . $menu . '</div>' . $content;
		
		return $content;
	}

	/*------- SETTINGS ----------------------------------------------------------------------------------------------*/

	public function get_dashboard_item_limit() {
		return $this->plugin->get_option( self::$OPTION_DASHBOARD_CONTENT_LIMIT );
	}
	
	public function print_settings($cuar_settings, $options_group) {
		add_settings_section(
				'cuar_core_frontend',
				__('Frontend Integration', 'cuar'),
				array( &$this, 'print_empty_settings_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_CUSTOMER_PAGE_POST_ID,
				__('Customer Page', 'cuar'),
				array( &$cuar_settings, 'print_post_select_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_frontend',
				array(
					'option_id' => self::$OPTION_CUSTOMER_PAGE_POST_ID,
					'post_type' => 'page',
					'after'		=> 
							'<p class="description">' 
							. __( 'This page is the one where you have inserted the [customer-area] shortcode. This should be set automatically when you first visit that page. '
									. 'If for any reason it is not correct, you can change it though.', 'cuar' )
							. '</p>' )
			);

		add_settings_field(
				self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT,
				__('Customer Area Menu', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_frontend',
				array(
					'option_id' => self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT,
					'type' 		=> 'checkbox',
					'after'		=>  __( 'Automatically print the Customer Area menu on single private content pages.', 'cuar' )
							. '<p class="description">' 
							. __( 'By default, you will only see the Customer Area menu everywhere but on the pages displaying a single private content (a private page or a private file for example). '
								. 'By checking this box, the menu will automatically be shown on those pages, but it might however not appear where you want it. If you are not happy with it, you can '
								. 'refer to our documentation to see how to change the place where it gets displayed.', 'cuar' )
							. '</p>' )
			);

		add_settings_field(
				self::$OPTION_DASHBOARD_CONTENT_LIMIT,
				__('Number of items on dashboard', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_core_frontend',
				array(
					'option_id' => self::$OPTION_DASHBOARD_CONTENT_LIMIT,
					'type' 		=> 'text',
					'after'		=> '<p class="description">' 
							. __( 'Sets the number of items to be display in each "latest" section of the dashboard (-1 will make it show all items).', 'cuar' )
							. '</p>' )
			);
	}
	
	public function print_empty_settings_section_info() {
	}
	
	public function validate_settings($validated, $cuar_settings, $input) {
		$cuar_settings->validate_post_id( $input, $validated, self::$OPTION_CUSTOMER_PAGE_POST_ID );
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT );
		$cuar_settings->validate_int( $input, $validated, self::$OPTION_DASHBOARD_CONTENT_LIMIT, -1 );
			
		return $validated;
	}
	
	public static function set_default_options($defaults) {
		$defaults [self::$OPTION_CUSTOMER_PAGE_POST_ID] = -1;
		$defaults [self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT] = false;
		$defaults [self::$OPTION_DASHBOARD_CONTENT_LIMIT] = 5;
			
		return $defaults;
	}
	
	private $title;
	private $actions;
	private $current_action;
	private $top_level_action;	
	
	// Frontend options
	public static $OPTION_CUSTOMER_PAGE_POST_ID					= 'customer_page_post_id';
	public static $OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT	= 'customer_page_auto_menu_on_single_content';
	public static $OPTION_DASHBOARD_CONTENT_LIMIT				= 'customer_page_dashboard_limit';
	
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_CustomerPageShortcode */
	private $customer_page_shortcode;
}

// Make sure the addon is loaded
global $cuar_cp_addon;
$cuar_cp_addon = new CUAR_CustomerPageAddOn();
	
// This filter needs to be executed too early to be registered in the constructor
add_filter( 'cuar_default_options', array( 'CUAR_CustomerPageAddOn', 'set_default_options' ) );

endif; // if (!class_exists('CUAR_CustomerPageAddOn')) :
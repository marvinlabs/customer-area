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

if (!class_exists('CUAR_AdminAreaAddOn')) :

/**
 * Add-on to organise the administration area (menus, ...) 
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_AdminAreaAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'admin-area', '4.0.0' );
	}
	
	public function get_addon_name() {
		return __( 'Administration Area', 'cuar' );
	}

	public function run_addon( $plugin ) {		
		add_action( 'admin_bar_menu', array( &$this, 'build_adminbar_menu' ), 32 );
			
		if ( is_admin() ) {
			add_action( 'admin_menu', array( &$this, 'build_admin_menu' ) );
			add_action( 'cuar/core/on-plugin-update', array( &$this, 'plugin_version_upgrade' ), 10, 2 );
			add_filter( 'cuar/core/permission-groups', array( &$this, 'get_configurable_capability_groups' ), 5 );

			add_filter( 'admin_init', array( &$this, 'add_dashboard_metaboxes' ) );
		} 
	}

	/*------- CUSTOMISATION OF THE MAIN PAGE --------------------------------------------------------------*/
	
	public function add_dashboard_metaboxes() {	
		add_meta_box('cuar_dashboard_news', __( 'News', 'cuar' ), 
				array( &$this, 'get_news_sidebox_content' ), 'customer-area', 'normal' );
		add_meta_box('cuar_dashboard_faq', __( 'Frequently Asked Questions', 'cuar' ), 
				array( &$this, 'get_faq_sidebox_content' ), 'customer-area', 'normal' );
		add_meta_box('cuar_dashboard_res', __( 'Resources', 'cuar' ), 
				array( &$this, 'get_resources_sidebox_content' ), 'customer-area', 'side' );
	}
	
	public function get_resources_sidebox_content( $args = null ) {	
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
			
		$out = '<ul>';		
		$out .= '<li>&raquo; ' . sprintf( __(
				'<a href="%1$s" target="_blank">Documentation</a> for the main plugin and for the add-ons.', 'cuar' ),
				'http://customer-area.marvinlabs.com/documentation/') . '</li>';
		$out .= '<li>&raquo; ' . sprintf( __( 
				'<a href="%1$s" target="_blank">Frequently Asked Questions</a>. ', 'cuar' ),
				'http://wordpress.org/plugins/customer-area/faq/') . '</li>';
		$out .= '<li>&raquo; ' . sprintf( __( 
				'A dedicated <a href="%1$s" target="_blank">wordpress.org support forum</a>, only for the main plugin (English only as per the wordpress.org rules).', 'cuar' ),
				'http://wordpress.org/support/plugin/customer-area') . '</li>';
		$out .= '<li>&raquo; ' . sprintf( __( 
				'<a href="%1$s" target="_blank">Support forums</a> on our website for the main plugin as well as for the add-ons (English, French and Spanish are ok there). ', 'cuar' ),
				'http://customer-area.marvinlabs.com/support/') . '</li>';
		$out .= '</ul>';

		if ( $echo ) echo $out;
		
		return $out;		
	}
	
	public function get_news_sidebox_content( $args = null ) {	
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
			
		$out = $this->get_feed_sidebox_content( 'http://customer-area.marvinlabs.com/category/news/feed/rss/' );
		
		if ( $echo ) echo $out;
		
		return $out;
	}
	
	public function get_faq_sidebox_content( $args = null ) {	
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
			
		$out = $this->get_feed_sidebox_content( 'http://customer-area.marvinlabs.com/category/faq/feed/rss/' );
		
		if ( $echo ) echo $out;
		
		return $out;
	}
	
	private function get_feed_sidebox_content( $feed_link ) {	
		$out = '<div class="rss-widget"><ul>';
		
		include_once(ABSPATH . WPINC . '/feed.php');
		$feed = fetch_feed( $feed_link );

		foreach ( $feed->get_items() as $item ) {
			$out .= '<li>';
			$out .= sprintf( '<a class="rsswidget" href="%1$s">%2$s</a> <span class="rss-date">%3$s</span>', 
					$item->get_permalink(), 
					$item->get_title(), 
					$item->get_date( get_option('date_format') ));
			
			$out .= sprintf( '<div class="rssSummary">%1$s</div>', $item->get_description() );
			$out .= '</li>';
		}
		
		$out .= '</ul></div>';
		
		return $out;
	}

	/*------- OTHER FUNCTIONS --------------------------------------------------------------*/
	
	/**
	 * Configurable capabilities
	 *  
	 * @param array $groups
	 * @return number
	 */
	public function get_configurable_capability_groups( $capability_groups ) {
		$capability_groups[ 'cuar_general' ] = array(
				'label'		=> __( 'General', 'cuar' ),
				'groups'	=> array(
						'back-office' 	=> array( 
								'group_name' => __( 'Back-office', 'cuar' ), 
								'capabilities' => array( 
										'view-customer-area-menu' => __( 'View the menu', 'cuar' ) 
								)
							) 
					)
			);
		
		return $capability_groups;
	}

	/**
	 * Build the administration menu
	 */
	public function build_admin_menu() {
	    // Add the top-level admin menu
	    $page_title = __( 'Customer Area', 'cuar' );
	    $menu_title = __( 'Customer Area', 'cuar' );
	    $menu_slug = 'customer-area';
	    $capability = 'view-customer-area-menu';
	    $function = array( &$this, 'print_customer_area_dashboard' );
	    $icon = "";
	    $position = '2.1.cuar';
	    
	    $this->pagehook = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon, $position );
	    
	    // Now add the submenu pages from add-ons
	    $submenu_items = apply_filters( 'cuar/core/admin/main-menu-pages', array() );
	    
	    foreach ( $submenu_items as $item ) {
		    $submenu_page_title = $item[ 'page_title' ];
		    $submenu_title 		= $item[ 'title' ];
		    $submenu_slug 		= $item[ 'slug' ];
		    $submenu_function 	= $item[ 'function' ];
		    $submenu_capability = $item[ 'capability' ];
		    
		    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, 
		    		$submenu_capability, $submenu_slug, $submenu_function);
	    }
	}

	/**
	 * Build the admin bar menu
	 */
	public function build_adminbar_menu( $wp_admin_bar ) {	
		$wp_admin_bar->add_menu(array(
				'id' 	=> 'customer-area',
				'title' => __( 'Customer Area', 'cuar' ),
				'href' 	=> admin_url( 'admin.php?page=customer-area' )
			));

		$wp_admin_bar->add_menu(array(
				'parent'=> 'customer-area',
				'id' 	=> 'customer-area-front-office',
				'title' => __( 'Your private area', 'cuar' ),
				'href' 	=> '#'
		));

		$submenu_items = apply_filters( 'cuar/core/admin/adminbar-menu-items', array() );		
		foreach ( $submenu_items as $item ) {
			$wp_admin_bar->add_menu( $item );
		}

		if ( current_user_can( 'manage_options' ) ) {
			$wp_admin_bar->add_menu(array(
					'parent'=> 'customer-area',
					'id' 	=> 'customer-area-settings',
					'title' => __( 'Settings', 'cuar' ),
					'href' 	=> admin_url( 'admin.php?page=cuar-settings' )
				));
		}

		if ( current_user_can( 'manage_options' ) ) {			
			$wp_admin_bar->add_menu(array(
					'parent'=> 'customer-area',
					'id' 	=> 'customer-area-support',
					'title' => __( 'Help & support', 'cuar' ),
					'href' 	=> admin_url( 'admin.php?page=cuar-settings&cuar_tab=cuar_troubleshooting' )
				));

			$wp_admin_bar->add_menu(array(
					'parent'=> 'customer-area',
					'id' 	=> 'customer-area-addons',
					'title' => __( 'Add-ons', 'cuar' ),
					'href' 	=> admin_url( 'admin.php?page=cuar-settings&cuar_tab=cuar_addons' )
				));
		}
	}

	public function print_customer_area_dashboard() {
		include( dirname( __FILE__ ) . '/templates/customer-area-dashboard.template.php' );
	}
	
	/**
	 * When the plugin is upgraded
	 * 
	 * @param unknown $from_version
	 * @param unknown $to_version
	 */
	public function plugin_version_upgrade( $from_version, $to_version ) {
		// If upgrading from before 1.5.0 we must add some caps to admin & editors
		if ( $from_version<'1.5.0' ) {
			$admin_role = get_role( 'administrator' );
			if ( $admin_role ) {
				$admin_role->add_cap( 'view-customer-area-menu' );
			}
			$editor_role = get_role( 'editor' );
			if ( $editor_role ) {
				$editor_role->add_cap( 'view-customer-area-menu' );
			}
		}
	}
		
	/** @var string */
	private $pagehook;
}

// Make sure the addon is loaded
new CUAR_AdminAreaAddOn();

endif; // if (!class_exists('CUAR_AdminAreaAddOn')) 

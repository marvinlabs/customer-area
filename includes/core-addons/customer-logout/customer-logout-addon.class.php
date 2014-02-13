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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon-page.class.php' );

if (!class_exists('CUAR_CustomerLogoutAddOn')) :

/**
 * Add-on to logout the current user
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_CustomerLogoutAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-logout', __( 'Customer Page - Logout', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 850, array(
					'slug'					=> 'customer-logout',
					'label'					=> __( 'Logout', 'cuar' ),
					'title'					=> __( 'Logout', 'cuar' ),
					'hint'					=> __( 'This page logs the current user out and redirects him to the logout page', 'cuar' ),
					'parent_slug'			=> 'customer-account-home',
					'requires_logout'		=> false
				)
			);
		
		$this->set_page_shortcode( 'customer-area-logout' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );	

		add_filter( 'cuar_get_nav_customer_area_pages', array( &$this, 'hide_page_in_default_nav_menu' ) );
		add_filter( 'cuar_default_logout_url', array( &$this, 'get_default_logout_url' ), 20, 3 );
	}

	protected function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-logout';
	}

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function hide_page_in_default_nav_menu( $pages ) {
		if ( !is_user_logged_in() ) {
			unset( $pages['user-logout'] );
		}
		return $pages;
	}

	public function print_page( $args = array(), $shortcode_content = '' ) {
		wp_logout();
		$logout_url = apply_filters( 'cuar_default_logout_url', null, 'customer-dashboard', null );	
		wp_redirect( $logout_url );			
	}

	/*------- FORM URLS ---------------------------------------------------------------------------------------------*/
	
	public function get_requested_redirect_url() {
		return isset( $_GET['cuar_redirect'] ) ? $_GET['cuar_redirect'] : '';
	}
	
	public function get_default_logout_url( $current_url = null, $redirect_slug = 'customer-dashboard', $redirect_url = null ) {
		if ( $current_url!=null ) return $current_url;
		
		$cp_addon = $this->plugin->get_addon('customer-pages');

		// Where should we redirect?
		if ( !empty( $redirect_slug ) ) {
			$redirect_page_id = $cp_addon->get_page_id( $redirect_slug );
			if ( $redirect_page_id>0 ) {
				$redirect_url = get_permalink( $redirect_page_id );
			} 
		} 
		
		if ( $redirect_url==null ) {
			$redirect_url = get_home_url();
		}
		
		// The account page ID
		$page_id = $cp_addon->get_page_id( 'user-account' );		
		if ( $page_id>0 ) {
			$permalink = get_permalink( $page_id );
			$logout_url = $permalink . '?cuar_redirect=' . $redirect_url;
		} else {
			$logout_url = wp_login_url( $redirect_url );
		}
		
		return $logout_url;
	}
}

// Make sure the addon is loaded
new CUAR_CustomerLogoutAddOn();

endif; // if (!class_exists('CUAR_CustomerLogoutAddOn')) :
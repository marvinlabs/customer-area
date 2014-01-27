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

if (!class_exists('CUAR_CustomerAccountAddOn')) :

/**
 * Add-on to show the customer dashboard page
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_CustomerAccountAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-account', __( 'Customer Account', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 800, array(
					'slug'					=> 'user-account',
					'label'					=> __( 'My Account', 'cuar' ),
					'title'					=> __( 'My Account', 'cuar' ),
					'hint'					=> __( 'This page shows a summary of the user account / login links / ...', 'cuar' ),
					'parent_slug'			=> 'dashboard',
					'requires_login'		=> false
				)
			);
		
		$this->set_page_shortcode( 'customer-area-account' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );	

		add_filter( 'cuar_default_login_url', array( &$this, 'get_default_login_url' ), 20, 3 );
	}

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function print_page_content( $args = array(), $shortcode_content = '' ) {
		if ( !is_user_logged_in() ) {
			$this->print_login_page( $args, $shortcode_content );
		} else {
			$this->print_account_page( $args, $shortcode_content );
		}
	}
	
	private function print_login_page( $args = array(), $shortcode_content = '' ) {
		$redirect_url = $this->get_requested_redirect_url();
		
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-account',
				'customer-account-login-required.template.php',
				'templates' ));
	}
	
	private function print_account_page( $args = array(), $shortcode_content = '' ) {
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-account',
				'customer-account-summary.template.php',
				'templates' ));
	}

	/*------- FORM URLS ---------------------------------------------------------------------------------------------*/
	
	public function get_requested_redirect_url() {
		return isset( $_GET['cuar_redirect'] ) ? $_GET['cuar_redirect'] : '';
	}
	
	public function get_default_login_url( $current_url = null, $redirect_slug = 'dashboard', $redirect_url = null ) {
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
			$login_url = $permalink . '?cuar_redirect=' . $redirect_url;
		} else {
			$login_url = wp_login_url( $redirect_url );
		}
		
		return $login_url;
	}
}

// Make sure the addon is loaded
new CUAR_CustomerAccountAddOn();
	
// This filter needs to be executed too early to be registered in the constructor
// add_filter( 'cuar_default_options', array( 'CUAR_CustomerAccountAddOn', 'set_default_options' ) );

endif; // if (!class_exists('CUAR_CustomerAccountAddOn')) :
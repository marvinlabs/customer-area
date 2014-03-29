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
		parent::__construct( 'customer-account', '4.6.0' );
		
		$this->set_page_parameters( 810, array(
					'slug'					=> 'customer-account',
					'parent_slug'			=> 'customer-account-home'
				)
			);
		
		$this->set_page_shortcode( 'customer-area-account' );
	}
	
	public function get_label() {
		return __( 'Account - Details', 'cuar' );
	}
	
	public function get_title() {
		return __( 'Account details', 'cuar' );
	}		
		
	public function get_hint() {
		return __( 'This page shows a summary of the user account', 'cuar' );
	}	

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );	
	}

	protected function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-account';
	}

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function print_account_fields() {
		do_action( 'cuar_customer_account_before_fields' );

		$current_user = $this->get_current_user();
		
		$up_addon = $this->plugin->get_addon('user-profile');
		$fields = $up_addon->get_profile_fields();
		
		foreach ( $fields as $field ) {
			$field->render_read_only_field( $current_user->ID );
		}
		
		do_action( 'cuar_customer_account_after_fields' );
	}
	
	protected function get_current_user() {
		if ( $this->current_user==null ) {
			$user_id = apply_filters( 'cuar_user_id_for_profile_page', get_current_user_id() );
			$this->current_user = get_userdata( $user_id );
		}
		return $this->current_user;
	}
	
	private $current_user = null;
}

// Make sure the addon is loaded
new CUAR_CustomerAccountAddOn();

endif; // if (!class_exists('CUAR_CustomerAccountAddOn')) :
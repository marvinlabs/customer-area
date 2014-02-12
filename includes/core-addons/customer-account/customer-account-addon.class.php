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
require_once( CUAR_INCLUDES_DIR . '/core-classes/field-renderer.class.php' );

if (!class_exists('CUAR_CustomerAccountAddOn')) :

/**
 * Add-on to show the customer dashboard page
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_CustomerAccountAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-account', __( 'Customer Page - User Account', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 800, array(
					'slug'					=> 'customer-account',
					'label'					=> __( 'My Account', 'cuar' ),
					'title'					=> __( 'My Account', 'cuar' ),
					'hint'					=> __( 'This page shows a summary of the user account', 'cuar' ),
					'parent_slug'			=> 'customer-home'
				)
			);
		
		$this->set_page_shortcode( 'customer-area-account' );
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
		
		$renderer = new CUAR_FieldRenderer( $this->plugin );	
			
		$renderer->start_section( 'contact', __( 'Contact', 'cuar' ) );
		$renderer->add_simple_field( 'first_name', __( 'First Name'), $current_user->first_name, '-' );
		$renderer->add_simple_field( 'last_name', __( 'Last Name'), $current_user->last_name, '-' );
		$renderer->add_simple_field( 'email', __( 'Email'), $current_user->user_email, false, 'wide' );
		$renderer->add_simple_field( 'website', __( 'Website'), $current_user->user_url, false, 'wide link' );
		
		do_action( 'cuar_add_account_fields', $current_user, $renderer );
		
		$renderer->print_fields();
		
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
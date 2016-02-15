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

if (!class_exists('CUAR_PaymentsCheckoutAddOn')) :

/**
 * Add-on to show the form to validate a payment
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PaymentsCheckoutAddOn extends CUAR_AbstractPageAddOn {

	public function __construct() {
		parent::__construct( 'payments-checkout', '6.4.0' );

		$this->set_page_parameters( 100, array(
					'slug'					=> 'payments-checkout',
					'parent_slug'			=> 'customer-home',
					'hide_in_menu' 			=> true,
				)
			);

		$this->set_page_shortcode( 'customer-area-payment-checkout' );
	}

	public function get_label() {
		return __( 'Payments - Checkout', 'cuar' );
	}

	public function get_title() {
		return __( 'Checkout', 'cuar' );
	}

	public function get_hint() {
		return __( 'This page allows a user to pay something', 'cuar' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );
	}

	public function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/payments-checkout';
	}

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/


}

// Make sure the addon is loaded
new CUAR_PaymentsCheckoutAddOn();

endif; // if (!class_exists('CUAR_PaymentsCheckoutAddOn')) :
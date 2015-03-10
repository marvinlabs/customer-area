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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon-root-page.class.php' );

if (!class_exists('CUAR_CustomerAccountHomeAddOn')) :

/**
 * Add-on to put private pages in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerAccountHomeAddOn extends CUAR_RootPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-account-home', '4.0.0', 'customer-account' );
		
		$this->set_page_parameters( 800, array(
					'slug'					=> 'customer-account-home',
					'parent_slug'			=> 'customer-home',
				)
			);
		
		$this->set_page_shortcode( 'customer-account-home' );
	}
	
	public function get_label() {
		return __( 'Account - Home', 'cuar' );
	}
	
	public function get_title() {
		return __( 'My account', 'cuar' );
	}		
		
	public function get_hint() {
		return __( 'This page shows a summary of the user account', 'cuar' );
	}	

	public function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-account';
	}
}

// Make sure the addon is loaded
new CUAR_CustomerAccountHomeAddOn();

endif; // if (!class_exists('CUAR_CustomerAccountHomeAddOn')) 

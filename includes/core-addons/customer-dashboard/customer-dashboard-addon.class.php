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

if (!class_exists('CUAR_CustomerDashboardAddOn')) :

/**
 * Add-on to show the customer dashboard page
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_CustomerDashboardAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-dashboard', __( 'Customer Dashboard', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 5, array(
					'slug'					=> 'customer-dashboard',
					'label'					=> __( 'Dashboard', 'cuar' ),
					'title'					=> __( 'Dashboard', 'cuar' ),
					'hint'					=> __( 'Usually this is the main page of your Customer Area where the user will see a summary of his documents and messages.', 'cuar' )
				)
			);
		
		$this->set_page_shortcode( 'customer-area' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );		
			
		// Register the main widget area
		$this->register_sidebar( 'cuar_customer_dashboard_content', __( 'Customer Area Dashboard', 'cuar' ) );
	}	

	protected function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-dashboard';
	}

}

// Make sure the addon is loaded
new CUAR_CustomerDashboardAddOn();

endif; // if (!class_exists('CUAR_CustomerDashboardAddOn')) :
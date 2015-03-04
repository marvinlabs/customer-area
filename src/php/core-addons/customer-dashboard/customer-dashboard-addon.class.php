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
		parent::__construct( 'customer-dashboard', '4.0.0' );
		
		$this->set_page_parameters( 5, array(
					'slug'					=> 'customer-dashboard',
					'parent_slug'			=> 'customer-home',
				)
			);
		
		$this->set_page_shortcode( 'customer-area-dashboard' );
	}
	
	public function get_label() {
		return __( 'Dashboard', 'cuar' );
	}
	
	public function get_title() {
		return __( 'Dashboard', 'cuar' );
	}		
		
	public function get_hint() {
		return __( "Shows a summary of the user's private content (files, pages, messages, ...).", 'cuar' );
	}	

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );		
		
		// Widget area for our sidebar
		$this->enable_sidebar();		
	}	

	public function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-dashboard';
	}

}

// Make sure the addon is loaded
new CUAR_CustomerDashboardAddOn();

endif; // if (!class_exists('CUAR_CustomerDashboardAddOn')) :
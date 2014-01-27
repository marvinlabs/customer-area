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
					'slug'					=> 'dashboard',
					'label'					=> __( 'Dashboard', 'cuar' ),
					'title'					=> __( 'Dashboard', 'cuar' ),
					'hint'					=> __( 'Usually this is the main page of your Customer Area where the user will see a summary of his documents and messages.', 'cuar' )
				)
			);
		
		$this->set_page_shortcode( 'customer-area' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );		
	}	

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function print_page_content( $args = array(), $shortcode_content = '' ) {
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-dashboard',
				"customer-dashboard.template.php",
				'templates' ));
	}
}

// Make sure the addon is loaded
new CUAR_CustomerDashboardAddOn();
	
// This filter needs to be executed too early to be registered in the constructor
// add_filter( 'cuar_default_options', array( 'CUAR_CustomerDashboardAddOn', 'set_default_options' ) );

endif; // if (!class_exists('CUAR_CustomerDashboardAddOn')) :
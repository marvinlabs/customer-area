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

if ( !class_exists( 'CUAR_CustomerAreaHomeAddOn' ) ) :

/**
 * Add-on to show the customer dashboard page
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_CustomerAreaHomeAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-home', __( 'Customer Page - Home', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 1, array(
					'slug'					=> 'customer-home',
					'label'					=> __( 'Home', 'cuar' ),
					'permalink'				=> __( 'customer-area', 'cuar' ),
					'title'					=> __( 'Customer Area', 'cuar' ),
					'hint'					=> __( 'This is the main page for your customers. It will redirect to the login page or to the customer area dashboard.', 'cuar' ),
					'always_include_in_menu'=> true
				)
			);
		
		$this->set_page_shortcode( 'customer-area' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );		
	}	

	protected function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-home';
	}
	
	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

	public function print_page_header( $args = array(), $shortcode_content = '' ) {
		if ( is_user_logged_in() ) {
			$cp_addon = $this->plugin->get_addon('customer-pages');
			wp_redirect( $cp_addon->get_page_url( apply_filters( 'cuar_get_home_redirect_slug', 'customer-dashboard' ) ), 302 );
			exit;
		}
		
		parent::print_page_header( $args, $shortcode_content );
	}

}

// Make sure the addon is loaded
new CUAR_CustomerAreaHomeAddOn();

endif; // if (!class_exists('CUAR_CustomerAreaHomeAddOn')) :
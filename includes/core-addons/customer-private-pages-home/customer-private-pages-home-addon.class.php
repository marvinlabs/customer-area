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

if (!class_exists('CUAR_CustomerPrivatePagesHomeAddOn')) :

/**
 * Add-on to put private pages in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerPrivatePagesHomeAddOn extends CUAR_RootPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-private-pages-home', '4.0.0', 'customer-private-pages' );
		
		$this->set_page_parameters( 600, array(
					'slug'					=> 'customer-private-pages-home',
					'parent_slug'			=> 'customer-home',
					'friendly_post_type'	=> 'cuar_private_page',
					'friendly_taxonomy'		=> 'cuar_private_page_category',
					'required_capability'	=> 'cuar_view_pages'
				)
			);
		
		$this->set_page_shortcode( 'customer-area-private-pages-home' );
	}
	
	public function get_label() {
		return __( 'Private Pages - Home', 'cuar' );
	}
	
	public function get_title() {
		return __( 'Pages', 'cuar' );
	}		
		
	public function get_hint() {
		return __( 'Root page for the customer pages.', 'cuar' );
	}	

	public function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages';
	}
}

// Make sure the addon is loaded
new CUAR_CustomerPrivatePagesHomeAddOn();

endif; // if (!class_exists('CUAR_CustomerPrivatePagesHomeAddOn')) 

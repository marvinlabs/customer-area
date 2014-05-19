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

if (!class_exists('CUAR_CustomerPrivateFilesHomeAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerPrivateFilesHomeAddOn extends CUAR_RootPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-private-files-home', '4.0.0', 'customer-private-files' );
		
		$this->set_page_parameters( 500, array(
					'slug'					=> 'customer-private-files-home',
					'parent_slug'			=> 'customer-home',
					'friendly_post_type'	=> 'cuar_private_file',
					'friendly_taxonomy'		=> 'cuar_private_file_category',
					'required_capability'	=> 'cuar_view_files'
				)
			);
		
		$this->set_page_shortcode( 'customer-area-private-files-home' );
	}
	
	public function get_label() {
		return __( 'Private Files - Home', 'cuar' );
	}
	
	public function get_title() {
		return __( 'Files', 'cuar' );
	}		
		
	public function get_hint() {
		return __( 'Root page for the customer files.', 'cuar' );
	}	

	public function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-private-files';
	}
}

// Make sure the addon is loaded
new CUAR_CustomerPrivateFilesHomeAddOn();

endif; // if (!class_exists('CUAR_CustomerPrivateFilesHomeAddOn')) 

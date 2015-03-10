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
require_once( CUAR_INCLUDES_DIR . '/core-classes/widget-content-list.class.php' );

if (!class_exists('CUAR_PrivateFilesWidget')) :

/**
 * Widget to show private files in a list
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_PrivateFilesWidget extends CUAR_ContentListWidget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
				'cuar_private_files', 
				__('WPCA - Recent Files', 'cuar'),
				array( 
						'description' => __( 'Shows a list of files for the logged-in user', 'cuar' ), 
					)
			);
	}

	protected function get_post_type() {
		return 'cuar_private_file';
	}
	
	protected function get_default_title() {
		return __( 'Recent Files', 'cuar' );
	}
	
	protected function get_default_no_content_message() {
		return __( 'No files', 'cuar' );
	}
	
	protected function get_associated_taxonomy() {
		return 'cuar_private_file_category';
	}
	
}

endif; // if (!class_exists('CUAR_PrivateFilesWidget')) 

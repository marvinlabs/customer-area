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
require_once( CUAR_INCLUDES_DIR . '/core-classes/widget-content-dates.class.php' );

if (!class_exists('CUAR_PrivateFileDatesWidget')) :

/**
 * Widget to show private file categories
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_PrivateFileDatesWidget extends CUAR_ContentDatesWidget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
				'cuar_private_file_archives', 
				__('WPCA - File Archives', 'cuar'),
				array( 
						'description' => __( 'Shows the private file yearly archives of the Customer Area', 'cuar' ), 
					)
			);
	}

	protected function get_post_type() {
		return 'cuar_private_file';
	}
	
	protected function get_default_title() {
		return __( 'Archives', 'cuar' );
	}
	
	protected function get_link( $year, $month=0 ) {
		$cuar_plugin = CUAR_Plugin::get_instance();
		$cfp_addon = $cuar_plugin->get_addon( 'customer-private-files' );
		return $cfp_addon->get_date_archive_url( $year, $month );
	}
	
}

endif; // if (!class_exists('CUAR_PrivateFileDatesWidget')) 

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

/**
 * A shortcode to display the navigation menu
 */
class CUAR_MenuShortcode extends CUAR_Shortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
        parent::__construct('customer-area-menu');
	}

    /**
     * Actually process the shortcode (output stuff, ...)
     *
     * @param array  $params  The parameters
     * @param string $content The content between the shortcode tags
     *
     * @return string The shortcode final output
     */
	public function process_shortcode( $params, $content ) {
        ob_start();
        cuar_the_customer_area_menu();
		$out = ob_get_contents();
        ob_end_clean();

		return $out;
	}
}

new CUAR_MenuShortcode();
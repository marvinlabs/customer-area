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

if (!class_exists('CUAR_AddOnPageShortcode')) :

/**
 * Handles the shortcodes rendering add-on pages
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_AddOnPageShortcode {

	public function __construct( $page_addon, $shortcode, $default_args = array() ) {
		$this->page_addon = $page_addon;
		$this->shortcode = $shortcode;
		$this->default_args = $default_args;
		
		$this->setup();	
	}
	
	/**
	 * Setup the WordPress hooks we need
	 */
	public function setup() {
		if ( !is_admin() ) {
			add_shortcode( $this->shortcode, array( &$this, 'process_shortcode' ) );
		}
	}
	
	/**
	 * Replace the [customer-area] shortcode with a page representing the customer area. The shortcode takes no
	 * parameter and does not accept any content.
	 * 
	 * @param array $attrs
	 * @param string $content
	 */
	public function process_shortcode( $params = array(), $content = null ) {
		$args = shortcode_atts( $this->default_args, $params, $this->shortcode );
		
		ob_start();		
		$this->page_addon->print_page( $args, $content );		
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
		return $out;
	}
	
	public function get_sample_shortcode( $include_defaults=true ) {
		$out = '[' . $this->shortcode . ' ';
		
		if ( $this->default_args!=null && !empty( $this->default_args )) {
			foreach ( $this->default_args as $p => $v ) {
				$out .= $p . "='" . $v . "' ";
			}
		}
		
		$out .= '/]';
		
		return $out;
	}
	
	public function get_shortcode_name() {
		return $this->shortcode;
	}
	
	/** @var CUAR_AbstractPageAddOn The page to be rendered */
	private $page_addon;

	/** @var string The shortcode to be used */
	private $shortcode;

	/** @var string The default values for the shortcode arguments */
	private $default_args;
}

endif; // if (!class_exists('CUAR_AddOnPageShortcode')) :
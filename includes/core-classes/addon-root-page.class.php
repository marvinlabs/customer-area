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

if ( !class_exists( 'CUAR_RootPageAddOn' ) ) :

/**
 * A page that simply serves as a root page in a menu and redirects to another page if visited
 *
 * @author Vincent Prat @ MarvinLabs
 */
abstract class CUAR_RootPageAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct( $addon_id = null, $min_cuar_version = null, $redirect_slug = 'customer-dashboard' ) {
		parent::__construct( $addon_id, $min_cuar_version );		
		$this->redirect_slug = $redirect_slug;
	}
	
	protected function set_page_parameters( $priority, $description ) {
		parent::set_page_parameters( $priority, $description );
		
		if ( !isset( $this->page_description['friendly_post_type'] )) {
			$this->page_description['friendly_post_type'] = null;
		}
		
		if ( !isset( $this->page_description['friendly_taxonomy'] )) {
			$this->page_description['friendly_taxonomy'] = null;
		}
	}
	
	public function get_friendly_post_type() {
		return $this->page_description['friendly_post_type'];
	}
	
	public function get_friendly_taxonomy() {
		return $this->page_description['friendly_taxonomy'];
	}
	
	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

	public function print_page_header( $args = array(), $shortcode_content = '' ) {
		if ( is_user_logged_in() ) {
			$cp_addon = $this->plugin->get_addon('customer-pages');
			wp_redirect( $cp_addon->get_page_url( apply_filters( 'cuar_root_page_redirect_slug', $this->redirect_slug, $this->get_slug() ) ), 302 );
			exit;
		}
		
		parent::print_page_header( $args, $shortcode_content );
	}

	protected $redirect_slug = 'customer-dashboard';
}

endif; // if (!class_exists('CUAR_RootPageAddOn')) :
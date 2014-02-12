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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon-content-page.class.php' );

require_once( dirname(__FILE__) . '/widget-private-page-categories.class.php' );
require_once( dirname(__FILE__) . '/widget-private-page-dates.class.php' );
require_once( dirname(__FILE__) . '/widget-private-pages.class.php' );

if (!class_exists('CUAR_CustomerPrivatePagesAddOn')) :

/**
 * Add-on to put private pages in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerPrivatePagesAddOn extends CUAR_AbstractContentPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-pages-page', __( 'Customer Page - Private Pages', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 600, array(
					'slug'					=> 'customer-private-pages',
					'label'					=> __( 'Private Pages', 'cuar' ),
					'title'					=> __( 'Pages', 'cuar' ),
					'hint'					=> __( 'Page to list the customer pages.', 'cuar' ),
					'parent_slug'			=> 'customer-home',
					'friendly_post_type'	=> 'cuar_private_page',
					'friendly_taxonomy'		=> 'cuar_private_page_category'
				)
			);
		
		$this->set_page_shortcode( 'customer-area-private-pages' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );		
		$this->pf_addon = $plugin->get_addon( 'private-pages' );
		
		// This page can also list archive for private content
		$this->enable_content_archives_permalinks();
		$this->enable_single_private_content_permalinks();
		
		// Widget area for our sidebar
		if ( $this->pf_addon->is_enabled() ) {
			$this->enable_sidebar( array( 'CUAR_PrivatePageCategoriesWidget', 'CUAR_PrivatePageDatesWidget', 'CUAR_PrivatePagesWidget' ) );
		}
		
		if ( is_admin() ) { 
			$this->enable_settings( 'cuar_private_pages' );
		}
	}	

	protected function get_page_addon_path() {
		return CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages';
	}

	protected function get_category_archive_page_subtitle( $category ) {
		return sprintf( __( 'Pages under %1$s', 'cuar' ), $category->name );
	}
	
	protected function get_date_archive_page_subtitle( $year, $month=0 ) {
		if ( isset( $month ) && ( (int)($month)>0 ) ) {
			$month_name = date("F", mktime(0, 0, 0, (int)$month, 10));
			$page_subtitle = sprintf( __( 'Pages published in %2$s %1$s', 'cuar' ), $year, $month_name );
		} else {
			$page_subtitle = sprintf( __( 'Pages published in %1$s', 'cuar' ), $year );
		}
	
		return $page_subtitle;
	}
	
	protected function get_default_page_subtitle() {
		return __( 'Recent Pages', 'cuar' );
	}
	
	protected function get_default_dashboard_block_title() {
		return __( 'Recent Pages', 'cuar' );
	}
	
	/** @var CUAR_PrivatePageAddOn */
	private $pf_addon;
}

// Make sure the addon is loaded - THIS IS DONE IN THE PRIVATE PAGE ADD-ON ITSELF
new CUAR_CustomerPrivatePagesAddOn();

endif; // if (!class_exists('CUAR_CustomerPrivatePagesAddOn')) 

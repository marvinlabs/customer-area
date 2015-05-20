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

if (!class_exists('CUAR_PrivatePageAdminInterface')) :

/**
 * Administation area for private files
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivatePageAdminInterface {
	
	public function __construct( $plugin, $private_page_addon ) {
		$this->plugin = $plugin;
		$this->private_page_addon = $private_page_addon;

		// Settings
		add_filter( 'cuar/core/settings/settings-tabs', array( &$this, 'add_settings_tab' ), 510, 1 );
		add_action( 'cuar/core/settings/print-settings?tab=cuar_private_pages', array( &$this, 'print_settings' ), 10, 2 );
		add_filter( 'cuar/core/settings/validate-settings?tab=cuar_private_pages', array( &$this, 'validate_options' ), 10, 3 );
		
		if ( $this->private_page_addon->is_enabled() ) {
			// File list page
			add_action( 'parse_query' , array( &$this, 'restrict_edit_post_listing' ) );			
			add_action( 'cuar/core/addons/after-init', array( &$this, 'customize_post_list_pages' ) );
			add_action( 'restrict_manage_posts', array( &$this, 'restrict_manage_posts' ) );
		}		
	}
	
	/*------- CUSTOMISATION OF THE LISTING OF POSTS -----------------------------------------------------------------*/
	
	public function customize_post_list_pages() {
		$type = "cuar_private_page";
		
		// Removed because we don't need the category column for now (automatically created). Uncomment if we add a column some day
		// add_filter( "manage_edit-{$type}_columns", array( &$this, 'register_post_list_columns' ), 5 );
		// add_action( "manage_{$type}_posts_custom_column", array( &$this, 'display_post_list_column'), 8, 2 );
	}
	
	public function register_post_list_columns( $columns ) {
		return $columns;
	}
	
	public function display_post_list_column( $column_name, $post_id ) {
	}
	
	public function restrict_manage_posts() {
		// only display these taxonomy filters on desired custom post_type listings
		global $typenow;
		if ($typenow == 'cuar_private_page') {
			
			$filters = array( 'cuar_private_page_category' );
	
			foreach ($filters as $tax_slug) {
				// retrieve the taxonomy object
				$tax_obj = get_taxonomy( $tax_slug );
				$tax_name = $tax_obj->labels->name;
				
				// retrieve array of term objects per taxonomy
				$terms = get_terms($tax_slug);
	
				// output html for taxonomy dropdown filter
				echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
				echo "<option value=''>Show All $tax_name</option>";
				
				foreach ($terms as $term) {
					$selected = selected( isset( $_GET[$tax_slug] ) ? $_GET[$tax_slug] : null, $term->slug, false );
					
					echo '<option value="' . $term->slug . '" ' . $selected . '>' . $term->name .' (' . $term->count .')</option>';
				}
				echo "</select>";
			}
		}
	}
	
	/*------- CUSTOMISATION OF THE EDIT PAGE OF A PRIVATE PAGES ------------------------------------------------------*/

	/**
	 * @param WP_Query $query
	 */
	public function restrict_edit_post_listing( $query ) {
		global $pagenow;
		if ( !is_admin() || $pagenow!='edit.php' ) return;
	
		$post_type = $query->get( 'post_type' );
		if ( $post_type!='cuar_private_page' ) return;
		
		if ( !current_user_can( 'cuar_pp_list_all' ) ) {
			$query->set( 'author', get_current_user_id() );
		}
	}

	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/

	public function add_settings_tab( $tabs ) {
		$tabs[ 'cuar_private_pages' ] = __( 'Private Pages', 'cuar' );
		return $tabs;
	}
	
	/**
	 * Add our fields to the settings page
	 * 
	 * @param CUAR_Settings $cuar_settings The settings class
	 */
	public function print_settings( $cuar_settings, $options_group ) {
		add_settings_section(
				'cuar_private_pages_addon_general',
				__('General settings', 'cuar'),
				array( &$this, 'print_empty_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				CUAR_PrivatePageAddOn::$OPTION_ENABLE_ADDON,
				__('Enable add-on', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_pages_addon_general',
				array(
					'option_id' => CUAR_PrivatePageAddOn::$OPTION_ENABLE_ADDON,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'Check this to enable the private pages add-on.', 'cuar' ) )
			);
	}
	
	/**
	 * Validate our options
	 * 
	 * @param CUAR_Settings $cuar_settings
	 * @param array $input
	 * @param array $validated
	 */
	public function validate_options( $validated, $cuar_settings, $input ) {		
		$cuar_settings->validate_boolean( $input, $validated, CUAR_PrivatePageAddOn::$OPTION_ENABLE_ADDON );		
		return $validated;
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_empty_section_info() {
	}
		
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_PrivatePageAddOn */
	private $private_page_addon;
}

endif; // if (!class_exists('CUAR_PrivatePageAdminInterface')) :
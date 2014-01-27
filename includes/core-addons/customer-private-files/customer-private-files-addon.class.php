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

require_once( dirname(__FILE__) . '/widget-private-file-categories.class.php' );
require_once( dirname(__FILE__) . '/widget-private-file-dates.class.php' );
require_once( dirname(__FILE__) . '/widget-private-files.class.php' );

if (!class_exists('CUAR_CustomerPrivateFilesAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CustomerPrivateFilesAddOn extends CUAR_AbstractPageAddOn {
	
	public function __construct() {
		parent::__construct( 'customer-files-page', __( 'Customer Files', 'cuar' ), '4.0.0' );
		
		$this->set_page_parameters( 200, array(
					'slug'					=> 'customer-private-files',
					'label'					=> __( 'Private Files', 'cuar' ),
					'title'					=> __( 'My Files', 'cuar' ),
					'hint'					=> __( 'Page to list the customer files.', 'cuar' ),
					'parent_slug'			=> 'dashboard',
					'friendly_post_type'	=> 'cuar_private_file'
				)
			);
		
		$this->set_page_shortcode( 'customer-area-private-files' );
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );		
		$this->pf_addon = $plugin->get_addon( 'private-files' );
		
		// This page can also list archive for private content
		$this->enable_content_archives();
		
		// Widget area for our sidebar
		if ( $this->pf_addon->is_enabled() ) {
			// Register widget classes
			$widgets = array( 'CUAR_PrivateFileCategoriesWidget', 'CUAR_PrivateFileDatesWidget', 'CUAR_PrivateFilesWidget' );
			foreach ( $widgets as $w ) {
				add_action('widgets_init', create_function('', 'return register_widget("' . $w . '");') );
			}
			
			// Register the sidebar on the files page
			$this->register_sidebar( 'cuar_customer_files_sidebard', __( 'Customer Area - Files Sidebar', 'cuar' ) );
		}
		
		if ( is_admin() ) { 
			// Settings
			add_action( 'cuar_addon_print_settings_cuar_private_files', array( &$this, 'print_settings' ), 10, 2 );
			add_filter( 'cuar_addon_validate_options_cuar_private_files', array( &$this, 'validate_options' ), 10, 3 );
		} else {
			// Optionally output the file links in the post footer area
			if ( $this->plugin->get_option( self::$OPTION_SHOW_AFTER_POST_CONTENT ) ) {
				add_filter( 'the_content', array( &$this, 'after_post_content' ), 3000 );
			}
			
			add_filter( "get_previous_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
			add_filter( "get_next_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
		}
	}	
	
	/*------- SINGLE POST PAGES -------------------------------------------------------------------------------------*/
		
	/**
	 * Disable the navigation on the single page templates for private files
	 */
	// TODO improve this by getting the proper previous/next file for the same owner
	public function disable_single_post_navigation( $where, $in_same_cat, $excluded_categories ) {
		if ( get_post_type()=='cuar_private_file' )	return "WHERE 1=0";		
		return $where;
	}
	
	public function after_post_content( $content ) {
		// If not on a matching post type, we do nothing
		if ( !is_singular('cuar_private_file') ) return $content;		

		ob_start();
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-private-files',
				'customer-private-files-single-post-footer.template.php',
				'templates' ));	
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
  		return $content . $out;
	}

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function print_page_content( $args = array(), $shortcode_content = '' ) {
		$po_addon = $this->plugin->get_addon('post-owner');
		$current_user_id = get_current_user_id();
		
		$year = get_query_var( 'cuar_year' );
		$month = get_query_var( 'cuar_month' );
		$category = get_query_var( 'cuar_category' );		
		
		$display_mode = 'default';
		$page_subtitle = '';
		
		if ( !empty( $category ) ) {
			$cat = get_term_by( 'slug', $category, 'cuar_private_file_category' );
			
			// Category archive, only show the files from that category
			$display_mode = 'category_archive';
			$page_subtitle = sprintf( __( 'Files under %1$s', 'cuar' ), $cat->name );
		
			$args = array(
					'post_type' 		=> 'cuar_private_file',
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id ),
					'tax_query' => array(
							array(
									'taxonomy' 		=> 'cuar_private_file_category',
									'field' 		=> 'slug',
									'terms' 		=> $category
								)
						)
				);
			
			$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );
		} else if ( !empty( $year ) ) {
			// Date archive, only show the files from that year/month
			$display_mode = 'date_archive';
		
			$args = array(
					'post_type' 		=> 'cuar_private_file',
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id ),
					'year' 				=> $year
				);
			
			if ( !empty( $month ) ) {
				$args['monthnum'] = (int) $month;
				
				$month_name = date("F", mktime(0, 0, 0, $month, 10));
				$page_subtitle = sprintf( __( 'Files published in %2$s %1$s', 'cuar' ), $year, $month_name );
			} else {
				$page_subtitle = sprintf( __( 'Files published in %1$s', 'cuar' ), $year );
			}
			
			$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );
		} else {
			// Default view
			$page_subtitle = __( 'Recent Files', 'cuar' );
			
			$args = array(
					'post_type' 		=> 'cuar_private_file',
					'posts_per_page' 	=> 5,
					'orderby' 			=> 'date',
					'order' 			=> 'DESC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
				);
			
			$files_query = new WP_Query( apply_filters( 'cuar_user_files_query_parameters', $args ) );
		}
		
		if ( $files_query->have_posts() ) {
			$item_template = $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-addons/customer-private-files',
					"customer-private-files-content-item-{$display_mode}.template.php",
					'templates',
					"customer-private-files-content-item.template.php" );
			
			include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-addons/customer-private-files',
					"customer-private-files-content-{$display_mode}.template.php",
					'templates',
					"customer-private-files-content.template.php" ));
		} else {
			include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-addons/customer-private-files',
					"customer-private-files-content-empty-{$display_mode}.template.php",
					'templates',
					"customer-private-files-content-empty.template.php" ));
		}
	}
	
	public function has_page_sidebar() {
		return true;
	}

	public function print_page_sidebar( $args = array(), $shortcode_content = '' ) {
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/customer-private-files',
				"customer-private-files-sidebar.template.php",
				'templates' ));
	}

	/*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/
	
	/**
	 * Add our fields to the settings page
	 *
	 * @param CUAR_Settings $cuar_settings The settings class
	 */
	public function print_settings( $cuar_settings, $options_group ) {		
		add_settings_section(
				'cuar_private_files_addon_frontend',
				__('Frontend Integration', 'cuar'),
				array( &$this, 'print_empty_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_SHOW_AFTER_POST_CONTENT,
				__('Show after post', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_files_addon_frontend',
				array(
					'option_id' => self::$OPTION_SHOW_AFTER_POST_CONTENT,
					'type' 		=> 'checkbox',
					'after'		=> 
							__( 'Show additional information after the post in the single post view.', 'cuar' )
							. '<p class="description">' 
							. __( 'You can disable this if you have your own "single-cuar_private_file.php" template file.', 'cuar' )
							. '</p>' )
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
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_SHOW_AFTER_POST_CONTENT );
		
		return $validated;
	}
	
	/**
	 * Set the default values for the options
	 *
	 * @param array $defaults
	 * @return array
	 */
	public static function set_default_options( $defaults ) {
		$defaults[ self::$OPTION_SHOW_AFTER_POST_CONTENT ] = true;
			
		return $defaults;
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_empty_section_info() {
		// echo '<p>' . __( 'Options for the private files add-on.', 'cuar' ) . '</p>';
	}

	// Settings
	public static $OPTION_SHOW_AFTER_POST_CONTENT		= 'private_files_auto_show_in_single_post_footer';
	
	/** @var CUAR_PrivateFileAddOn */
	private $pf_addon;
}

// Make sure the addon is loaded - THIS IS DONE IN THE PRIVATE FILE ADD-ON ITSELF
new CUAR_CustomerPrivateFilesAddOn();

// This filter needs to be executed too early to be registered in the constructor
add_filter( 'cuar_default_options', array( 'CUAR_CustomerPrivateFilesAddOn', 'set_default_options' ) );

endif; // if (!class_exists('CUAR_CustomerPrivateFilesAddOn')) 

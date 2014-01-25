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
		global $cuar_po_addon;
		
		$this->plugin = $plugin;
		$this->private_page_addon = $private_page_addon;

		// Settings
		add_filter( 'cuar_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 10, 1 );
		add_action( 'cuar_addon_print_settings_cuar_private_pages', array( &$this, 'print_settings' ), 10, 2 );
		add_filter( 'cuar_addon_validate_options_cuar_private_pages', array( &$this, 'validate_options' ), 10, 3 );
		
		if ( $plugin->get_option( self::$OPTION_ENABLE_ADDON ) ) {
			// Admin menu
			add_action('cuar_admin_submenu_pages', array( &$this, 'add_menu_items' ), 11 );			
			add_action( "admin_footer", array( &$this, 'highlight_menu_item' ) );

			add_action( 'parse_query' , array( &$this, 'restrict_edit_post_listing' ) );
		}		
	}
			
	/**
	 * Highlight the proper menu item in the customer area
	 */
	public function highlight_menu_item() {
		global $post;
		
		// For posts
		if ( isset( $_REQUEST['taxonomy'] ) && $_REQUEST['taxonomy']=='cuar_private_page_category' ) {		
			$highlight_top 	= '#toplevel_page_customer-area';
			$unhighligh_top = '#menu-posts';
		} else if ( isset( $post ) && get_post_type( $post )=='cuar_private_page' ) {		
			$highlight_top 	= '#toplevel_page_customer-area';
			$unhighligh_top = '#menu-posts';
		} else {
			$highlight_top 	= null;
			$unhighligh_top = null;
		}
		
		if ( $highlight_top && $unhighligh_top ) {
?>
<script type="text/javascript">
jQuery(document).ready( function($) {
	$('<?php echo $unhighligh_top; ?>')
		.removeClass('wp-has-current-submenu')
		.addClass('wp-not-current-submenu');
	$('<?php echo $highlight_top; ?>')
		.removeClass('wp-not-current-submenu')
		.addClass('wp-has-current-submenu current');
});     
</script>
<?php
		}
	}

	/**
	 * Add the menu item
	 */
	public function add_menu_items( $submenus ) {
		$separator = '<span style="display:block;  
				        margin: 0px 5px 12px -5px; 
				        padding:0; 
				        height:1px; 
				        line-height:1px; 
				        background:#ddd;
						opacity: 0.5; "></span>';
		
		$my_submenus = array(
				array(
					'page_title'	=> __( 'Private Pages', 'cuar' ),
					'title'			=> $separator . __( 'Private Pages', 'cuar' ),
					'slug'			=> "edit.php?post_type=cuar_private_page",
					'function' 		=> null,
					'capability'	=> 'cuar_pp_edit'
				),
				array(
					'page_title'	=> __( 'New Private Page', 'cuar' ),
					'title'			=> __( 'New Private Page', 'cuar' ),
					'slug'			=> "post-new.php?post_type=cuar_private_page",
					'function' 		=> null,
					'capability'	=> 'cuar_pp_edit'
				),
				array(
					'page_title'	=> __( 'Private Page Categories', 'cuar' ),
					'title'			=> __( 'Private Page Categories', 'cuar' ),
					'slug'			=> "edit-tags.php?taxonomy=cuar_private_page_category",
					'function' 		=> null,
					'capability'	=> 'cuar_pp_manage_categories'
				)
			); 
	
		foreach ( $my_submenus as $submenu ) {
			$submenus[] = $submenu;
		}
	
		return $submenus;
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
				array( &$this, 'print_frontend_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_ENABLE_ADDON,
				__('Enable add-on', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_pages_addon_general',
				array(
					'option_id' => self::$OPTION_ENABLE_ADDON,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'Check this to enable the private pages add-on.', 'cuar' ) )
			);
		
		add_settings_section(
				'cuar_private_pages_addon_frontend',
				__('Frontend Integration', 'cuar'),
				array( &$this, 'print_frontend_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_SHOW_AFTER_POST_CONTENT,
				__('Show after post', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_pages_addon_frontend',
				array(
					'option_id' => self::$OPTION_SHOW_AFTER_POST_CONTENT,
					'type' 		=> 'checkbox',
					'after'		=> 
							__( 'Show additional information after the post in the single post view.', 'cuar' )
							. '<p class="description">' 
							. __( 'You can disable this if you have your own "single-cuar_private_page.php" template file.', 'cuar' )
							. '</p>' )
			);
		
		add_settings_field(
				self::$OPTION_PAGE_LIST_MODE, 
				__('Page list', 'cuar'),
				array( &$cuar_settings, 'print_select_field' ), 
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_pages_addon_frontend',
				array( 
					'option_id' => self::$OPTION_PAGE_LIST_MODE, 
					'options'	=> array( 
						'plain' 	=> __( "Don't group pages", 'cuar' ),
						'month'		=> __( 'Group by month', 'cuar' ),
						'year' 		=> __( 'Group by year', 'cuar' ),
						'category' 	=> __( 'Group by category', 'cuar' ) ),
	    			'after'	=> '<p class="description">'
	    				. __( 'You can choose how pages will be organized by default in the customer area.', 'cuar' )
	    				. '</p>' )
			);	

		add_settings_field(
				self::$OPTION_HIDE_EMPTY_CATEGORIES,
				__('Empty categories', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_pages_addon_frontend',
				array(
					'option_id' => self::$OPTION_HIDE_EMPTY_CATEGORIES,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'When listing pages by category, empty categories will be hidden if you check this.', 
							'cuar' ) )
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
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_ENABLE_ADDON );
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_SHOW_AFTER_POST_CONTENT );
		$cuar_settings->validate_enum( $input, $validated, self::$OPTION_PAGE_LIST_MODE, 
				array( 'plain', 'year', 'month', 'category' ) );
		$cuar_settings->validate_boolean( $input, $validated, self::$OPTION_HIDE_EMPTY_CATEGORIES );
		
		return $validated;
	}
	
	/**
	 * Set the default values for the options
	 * 
	 * @param array $defaults
	 * @return array
	 */
	public static function set_default_options( $defaults ) {
		$defaults[ self::$OPTION_ENABLE_ADDON ] = true;
		$defaults[ self::$OPTION_SHOW_AFTER_POST_CONTENT ] = true;
		$defaults[ self::$OPTION_PAGE_LIST_MODE ] = 'plain';
		$defaults[ self::$OPTION_HIDE_EMPTY_CATEGORIES ] = true;

		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'cuar_pp_edit' );
			$admin_role->add_cap( 'cuar_pp_delete' );
			$admin_role->add_cap( 'cuar_pp_read' );
			$admin_role->add_cap( 'cuar_pp_manage_categories' );
			$admin_role->add_cap( 'cuar_pp_edit_categories' );
			$admin_role->add_cap( 'cuar_pp_delete_categories' );
			$admin_role->add_cap( 'cuar_pp_assign_categories' );
			$admin_role->add_cap( 'cuar_pp_list_all' );
			$admin_role->add_cap( 'cuar_view_any_cuar_private_page' );
		}
		
		return $defaults;
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_frontend_section_info() {
		// echo '<p>' . __( 'Options for the private files add-on.', 'cuar' ) . '</p>';
	}

	// General options
	public static $OPTION_ENABLE_ADDON					= 'enable_private_pages';
	public static $OPTION_SHOW_AFTER_POST_CONTENT		= 'pf_frontend_show_after_post_content';

	// Frontend options
	public static $OPTION_PAGE_LIST_MODE				= 'frontend_page_list_mode';
	public static $OPTION_HIDE_EMPTY_CATEGORIES			= 'frontend_hide_empty_page_categories';
		
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_PrivatePageAddOn */
	private $private_page_addon;
}
	
// This filter needs to be executed too early to be registered in the constructor
add_filter( 'cuar_default_options', array( 'CUAR_PrivatePageAdminInterface', 'set_default_options' ) );

endif; // if (!class_exists('CUAR_PrivatePageAdminInterface')) :
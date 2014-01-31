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

if (!class_exists('CUAR_AbstractContentPageAddOn')) :

/**
 * The base class for addons that should render a page containing content from custom private posts
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AbstractContentPageAddOn extends CUAR_AbstractPageAddOn {

	public function __construct( $addon_id = null, $addon_name = null, $min_cuar_version = null ) {
		parent::__construct( $addon_id, $addon_name, $min_cuar_version );
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
	
	protected abstract function get_category_archive_page_subtitle( $category );	
	protected abstract function get_date_archive_page_subtitle( $year, $month=0 );	
	protected abstract function get_default_page_subtitle();
	protected abstract function get_default_dashboard_block_title();
	
	/**
	 * Set the default values for the options
	 *
	 * @param array $defaults
	 * @return array
	 */
	public function set_default_options( $defaults ) {
		$defaults = parent::set_default_options($defaults);
		
		$slug = $this->get_slug();
		
		$defaults[ $slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER ] 		= true;
		$defaults[ $slug . self::$OPTION_SHOW_IN_DASHBOARD ] 				= true;
		$defaults[ $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD ] 	= 5;
			
		return $defaults;
	}
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function is_show_in_dashboard_enabled() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_SHOW_IN_DASHBOARD, true );
	}
	
	public function is_show_in_single_post_footer_enabled() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_SHOW_IN_DASHBOARD, true );
	}
	
	public function get_max_item_number_on_dashboard() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD, 5 );
	}

	/*------- ARCHIVES ----------------------------------------------------------------------------------------------*/
	
	/**
	 * The path of the page (slug + parent slugs)
	 */
	private function get_full_page_path( $page_id=0 ) { 
		if ( $page_id==0 ) {
			$cp_addon = $this->plugin->get_addon( 'customer-pages' );
			$page_id = $cp_addon->get_page_id( $this->get_slug() );
		}
		
		return untrailingslashit( str_replace( trailingslashit( home_url() ), '', get_permalink( $page_id ) ) );
	}
	
	/**
	 * Allow this page to get URLs for content archives
	 */
	protected function enable_content_archives_permalinks() {		
		add_filter( 'rewrite_rules_array', array( &$this, 'insert_archive_rewrite_rules' ) );
		add_filter( 'query_vars', array( &$this, 'insert_archive_query_vars' ) );
	}

	/**
	 * Add rewrite rules for the archive subpages.
	 * @param unknown $rules
	 * @return array
	 */
	public function insert_archive_rewrite_rules( $rules ) {
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		
		$page_id = $cp_addon->get_page_id( $this->get_slug() );
		$page_slug = untrailingslashit( str_replace( trailingslashit( home_url() ), '', get_permalink( $page_id ) ) );
		
		$newrules = array();
		
		// Category archives
		if ( $this->get_friendly_taxonomy()!=null ) {
			$rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_category=$matches[1]';		
			$rewrite_regex = '^' . $page_slug . '/' . $cp_addon->get_category_archive_slug() . '/([^/]+)/?$';
			$newrules[ $rewrite_regex ] = $rewrite_rule;
		}
			
		// Year archives
		$rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_year=$matches[1]';
		$rewrite_regex = '^' . $page_slug . '/' . $cp_addon->get_date_archive_slug() . '/([0-9]{4})/?$';
		$newrules[ $rewrite_regex ] = $rewrite_rule;
		
		// Month archives
		$rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_year=$matches[1]&cuar_month=$matches[2]';
		$rewrite_regex = '^' . $page_slug . '/' . $cp_addon->get_date_archive_slug() . '/([0-9]{4})/([0-9]{2})/?$';
		$newrules[ $rewrite_regex ] = $rewrite_rule;
		
		return $newrules + $rules;
	}

	/**
	 * Add query variables for the archive subpages.
	 * @param unknown $vars
	 * @return array
	 */
	public function insert_archive_query_vars( $vars ) {
		if ( $this->get_friendly_taxonomy()!=null ) {
	    	array_push($vars, 'cuar_category');
		}
		
	    array_push($vars, 'cuar_year');
	    array_push($vars, 'cuar_month');
	    
	    return $vars;
	}
	
	/**
	 * Get the URL for the archive corresponding to a given date.
	 * 
	 * @param unknown $year
	 * @param unknown $month optional, pass a negative number to get year archive
	 * @return string
	 */
	public function get_date_archive_url( $year, $month=0 ) {
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		
		$page_id = $cp_addon->get_page_id( $this->get_slug() );
		if ( $page_id==false ) return '';
		
		$url = trailingslashit( get_permalink( $page_id ) );
		$url .= $cp_addon->get_date_archive_slug() . '/';
		$url .= $year . '/';
		
		if ( $month>0 ) {
			$url .= sprintf( '%02d', $month );
		}
		
		return $url;
	}
	
	/**
	 * Get the URL for the archive corresponding to a given category.
	 * 
	 * @param unknown $term
	 * @return string|unknown
	 */
	public function get_category_archive_url( $term ) {
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		
		$page_id = $cp_addon->get_page_id( $this->get_slug() );
		if ( $page_id==false ) return '';
		
		$url = trailingslashit( get_permalink( $page_id ) );
		$url .= $cp_addon->get_category_archive_slug() . '/';
		$url .= $term->slug;
		
		return $url;
	}

	/*------- SINGLE PRIVATE CONTENT --------------------------------------------------------------------------------*/
	
	/**
	 * Allow this page to get URLs for single private content pages
	 */
	protected function enable_single_private_content_permalinks() {
		if ( !isset( $this->page_description['friendly_post_type'] ) ) {
			warn( 'Cannot enable single content permalinks for page without declaring its friendly_post_type' );
			return;
		}
		
		add_filter( 'rewrite_rules_array', array( &$this, 'insert_single_post_rewrite_rules' ) );
		add_filter( 'query_vars', array( &$this, 'insert_single_post_query_vars' ) );		
		add_filter( 'post_type_link', array( &$this, 'filter_single_private_content_link' ), 10, 2 );
	}

	/**
	 * Add rewrite rules for single private content pages.
	 * @param unknown $rules
	 * @return array
	 */
	public function insert_single_post_rewrite_rules( $rules ) {
		$page_slug = $this->get_full_page_path();
		
		$newrules = array();
		
		// Single post rule
		$rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type'] . '=$matches[4]';		
		$rewrite_regex = '^' . $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/?$';
		$newrules[ $rewrite_regex ] = $rewrite_rule;
		
		// Single post rule with action
		$rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type'] . '=$matches[4]&cuar_action=$matches[5]';		
		$rewrite_regex = '^' . $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/([^/]+)/?$';
		$newrules[ $rewrite_regex ] = $rewrite_rule;
		
		return $newrules + $rules;
	}

	/**
	 * Add query variables for single private content pages.
	 * @param unknown $vars
	 * @return array
	 */
	public function insert_single_post_query_vars( $vars ) {
	    array_push( $vars, 'cuar_action' );
	    array_push( $vars, $this->page_description['friendly_post_type'] );
	    return $vars;
	}
	
	/**
	 * Output the correct permalink for our single private content pages
	 * 
	 * @param unknown $permalink
	 * @param unknown $post
	 * @return Ambigous <string, mixed>
	 */
	function filter_single_private_content_link( $permalink, $post ) {
		$post_type = $this->page_description['friendly_post_type'];
		
		if ( $post_type == $post->post_type 
				&& ''!=$permalink 
				&& !in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
			$permalink = $this->get_single_private_content_url( $post );
		}
		return $permalink;
	}
	
	/**
	 * Get the URL to view a given post
	 * 
	 * @param unknown $post_id
	 */
	public function get_single_private_content_url( $post ) {
		$cp_addon = $this->plugin->get_addon( 'customer-pages' );
		$page_id = $cp_addon->get_page_id( $this->get_slug() );
		if ( $page_id==false ) return '';
		
		$post = get_post( $post );
		
		$date = mysql2date( 'Y m d', $post->post_date );
		$date = explode( " ", $date );

		$url = trailingslashit( get_permalink( $page_id ) );
		$url .= sprintf( '%04d/%02d/%02d/%s', $date[0], $date[1], $date[2], $post->post_name );		
		
		if ( !empty( $action ) ) {
			$url .= '/' . $action;		
		}
		
		return $url;
	}
	
	public function get_single_private_content_action_url( $post, $action='' ) {		
		$url = $this->get_single_private_content_url( $post );
		
		if ( !empty( $action ) ) {
			$url .= '/' . $action;		
		}
		
		return $url;
	}
			
	/**
	 * Disable the navigation on the single page templates for private files
	 */
	// TODO improve this by getting the proper previous/next file for the same owner
	public function disable_single_post_navigation( $where, $in_same_cat, $excluded_categories ) {
		if ( get_post_type()==$this->get_friendly_post_type() )	return "WHERE 1=0";		
		return $where;
	}
		
	/*------- SIDEBAR HANDLING --------------------------------------------------------------------------------------*/
	
	protected function enable_sidebar( $widget_classes ) {
		$this->is_sidebar_enabled = true;
		
		// Register widget classes
		foreach ( $widget_classes as $w ) {
			add_action( 'widgets_init', create_function( '', 'return register_widget("' . $w . '");' ) );
		}
			
		// Register the sidebar
		$this->register_sidebar( $this->get_slug() . '-sidebar', sprintf( __( '%s Sidebar', 'cuar' ), $this->get_label() ) );
	}
	
	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function get_friendly_post_type() {
		return $this->page_description['friendly_post_type'];
	}
	
	public function get_friendly_taxonomy() {
		return $this->page_description['friendly_taxonomy'];
	}
	
	public function has_page_sidebar() {
		return $this->is_sidebar_enabled;
	}
	
	public function print_page_content( $args = array(), $shortcode_content = '' ) {
		$po_addon = $this->plugin->get_addon('post-owner');
		$current_user_id = get_current_user_id();
		$page_slug = $this->get_slug();
		
		$year = get_query_var( 'cuar_year' );
		$month = get_query_var( 'cuar_month' );
		$category = get_query_var( 'cuar_category' );		
		
		$display_mode = 'default';
		$page_subtitle = '';
		
		if ( !empty( $category ) && $this->get_friendly_taxonomy()!=null ) {
			$cat = get_term_by( 'slug', $category, $this->get_friendly_taxonomy() );
			
			// Category archive, only show the files from that category
			$display_mode = 'category_archive';
			$page_subtitle = $this->get_category_archive_page_subtitle( $cat );
		
			$args = array(
					'post_type' 		=> $this->get_friendly_post_type(),
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id ),
					'tax_query' => array(
							array(
									'taxonomy' 		=> $this->get_friendly_taxonomy(),
									'field' 		=> 'slug',
									'terms' 		=> $category
								)
						)
				);
		} else if ( !empty( $year ) ) {
			// Date archive, only show the files from that year/month
			$display_mode = 'date_archive';
		
			$args = array(
					'post_type' 		=> $this->get_friendly_post_type(),
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id ),
					'year' 				=> $year
				);
			
			if ( !empty( $month ) ) {
				$args['monthnum'] = (int) $month;
			}
			
			$page_subtitle = $this->get_date_archive_page_subtitle( $year, $month );
		} else {
			// Default view			
			$args = array(
					'post_type' 		=> $this->get_friendly_post_type(),
					'posts_per_page' 	=> 5,
					'orderby' 			=> 'date',
					'order' 			=> 'DESC',
					'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
				);
			
			$page_subtitle = $this->get_default_page_subtitle();
		}
				
		$args = apply_filters( $page_slug . 'cuar_query_parameters-' .  $page_slug, $args );
		$args = apply_filters( $page_slug . 'cuar_query_parameters-' .  $page_slug . '-' . $display_mode, $args );		
		$content_query = new WP_Query( $args );

		$page_subtitle = apply_filters( 'cuar_page_subtitle-' .  $page_slug, $page_subtitle );
		$page_subtitle = apply_filters( 'cuar_page_subtitle-' .  $page_slug . '-' . $display_mode, $page_subtitle );
		
		if ( $content_query->have_posts() ) {
			$item_template = $this->plugin->get_template_file_path(
					$this->get_page_addon_path(),
					$this->get_slug() . "-content-item-{$display_mode}.template.php",
					'templates',
					$this->get_slug() . "-content-item.template.php" );
			
			include( $this->plugin->get_template_file_path(
					$this->get_page_addon_path(),
					$this->get_slug() . "-content-{$display_mode}.template.php",
					'templates',
					$this->get_slug() . "-content.template.php" ));
		} else {
			include( $this->plugin->get_template_file_path(
					$this->get_page_addon_path(),
					$this->get_slug() . "-content-empty-{$display_mode}.template.php",
					'templates',
					$this->get_slug() . "-content-empty.template.php" ));
		}
	}
	
	/*------- SINGLE POST PAGES -------------------------------------------------------------------------------------*/
	
	public function print_single_private_content_footer( $content ) {
		// If not on a matching post type, we do nothing
		if ( !is_singular( $this->get_friendly_post_type() ) ) return $content;		

		ob_start();
		include( $this->plugin->get_template_file_path(
				$this->get_page_addon_path(),
				$this->get_slug() . '-single-post-footer.template.php',
				'templates' ));	
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
  		return $content . $out;
	}
	
	/*------- DASHBOARD BLOCK ---------------------------------------------------------------------------------------*/
	
	public function print_dashboard_content( $content ) {
		$po_addon = $this->plugin->get_addon('post-owner');
		$current_user_id = get_current_user_id();
		$page_slug = $this->get_slug();
		
		$args = array(
				'post_type' 		=> $this->get_friendly_post_type(),
				'posts_per_page' 	=> $this->get_max_item_number_on_dashboard(),
				'orderby' 			=> 'date',
				'order' 			=> 'DESC',
				'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( $current_user_id )
			);
			
		$page_subtitle = $this->get_default_dashboard_block_title();
		$page_subtitle = apply_filters( 'cuar_dashboard_block_title-' .  $page_slug, $page_subtitle );

		$args = apply_filters( $page_slug . 'cuar_dashboard_block_query_parameters-' .  $page_slug, $args );

		$content_query = new WP_Query( $args );
		
		if ( $content_query->have_posts() ) {
			$item_template = $this->plugin->get_template_file_path(
					$this->get_page_addon_path(),
					$this->get_slug() . "-content-item-dashboard.template.php",
					'templates',
					$this->get_slug() . "-content-item.template.php" );
			
			include( $this->plugin->get_template_file_path(
					$this->get_page_addon_path(),
					$this->get_slug() . "-content-dashboard.template.php",
					'templates',
					$this->get_slug() . "-content.template.php" ));
		} else {
			include( $this->plugin->get_template_file_path(
					$this->get_page_addon_path(),
					$this->get_slug() . "-content-empty-dashboard.template.php",
					'templates',
					$this->get_slug() . "-content-empty.template.php" ));
		}
	}
	
	/*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

	public function enable_settings( $target_tab, $enabled_settings = array( 'dashboard', 'single-post-footer' ) ) {
		$this->enabled_settings = $enabled_settings;
		
		if ( is_admin() && !empty( $this->enabled_settings ) ) {
			// Settings
			add_action( 'cuar_addon_print_settings_' . $target_tab, array( &$this, 'print_settings' ), 10, 2 );
			add_filter( 'cuar_addon_validate_options_' . $target_tab, array( &$this, 'validate_options' ), 10, 3 );
		}
	}
	
	protected function get_settings_section() {
		return $this->get_slug() . '_frontend';
	}
	
	/**
	 * Add our fields to the settings page
	 *
	 * @param CUAR_Settings $cuar_settings The settings class
	 */
	public function print_settings( $cuar_settings, $options_group ) {
		if ( empty( $this->enabled_settings ) ) return;
		 		
		$slug = $this->get_slug();
		
		add_settings_section(
				$this->get_settings_section(),
				__('Frontend Integration', 'cuar'),
				array( &$this, 'print_empty_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		if ( in_array( 'single-post-footer', $this->enabled_settings ) ) {
			add_settings_field(
					$slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER,
					__('Show after post', 'cuar'),
					array( &$cuar_settings, 'print_input_field' ),
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array(
						'option_id' 	=> $slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER,
						'type' 			=> 'checkbox',
						'default_value' => 1,
						'after'			=> 
								__( 'Show additional information after the post in the single post view.', 'cuar' )
								. '<p class="description">' 
								. sprintf( __( 'You can disable this if you have your own theme template file for single posts. The theme file to look for or create should be called: %s.', 'cuar' ),
										'<code>single-' . $this->get_friendly_post_type() . '.php</code>' )
								. '</p>' )
				);
		}

		if ( in_array( 'dashboard', $this->enabled_settings ) ) {
			add_settings_field(
					$slug . self::$OPTION_SHOW_IN_DASHBOARD,
					__('Dashboard', 'cuar'),
					array( &$cuar_settings, 'print_input_field' ),
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array(
						'option_id' 	=> $slug . self::$OPTION_SHOW_IN_DASHBOARD,
						'type' 			=> 'checkbox',
						'default_value' => 1,
						'after'			=> __( 'Show recent content on the dashboard.', 'cuar' ) )
				);

			add_settings_field(
					$slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD,
					'',
					array( &$cuar_settings, 'print_input_field' ),
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array(
						'option_id' 	=> $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD,
						'type' 			=> 'text',
						'default_value' => 1,
						'after'			=> '<p class="description">' . __( 'Define how many items to allow on the dashboard page. -1 will show all items.', 'cuar' ) )
				);
		}
	}
	
	/**
	 * Validate our options
	 *
	 * @param CUAR_Settings $cuar_settings
	 * @param array $input
	 * @param array $validated
	 */
	public function validate_options( $validated, $cuar_settings, $input ) {
		$slug = $this->get_slug();
		
		$cuar_settings->validate_boolean( $input, $validated, $slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER );
		$cuar_settings->validate_boolean( $input, $validated, $slug . self::$OPTION_SHOW_IN_DASHBOARD );
		$cuar_settings->validate_int( $input, $validated, $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD );
		
		return $validated;
	}
	
	public function print_empty_section_info() {
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );

		if ( $this->get_friendly_post_type()!=null ) {
			add_filter( "get_previous_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
			add_filter( "get_next_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );
		}
		
		if ( !is_admin() ) {
			// Optionally output the file links in the post footer area
			if ( $this->is_show_in_single_post_footer_enabled() ) {
				add_filter( 'the_content', array( &$this, 'print_single_private_content_footer' ), 3000 );
			}

			// Optionally output the latest files on the dashboard
			if ( $this->is_show_in_dashboard_enabled() ) {
				add_filter( 'cuar_before_page_content_customer-dashboard', array( &$this, 'print_dashboard_content' ), 10 );
			}
		}
	}

	// Settings
	public static $OPTION_SHOW_IN_SINGLE_POST_FOOTER	= '-show_in_single_post_footer';
	public static $OPTION_SHOW_IN_DASHBOARD				= '-show_in_dashboard';
	public static $OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD	= '-max_items_on_dashboard';
	
	protected $enabled_settings = array();
	
	/** @var boolean did we enable a sidebar for this page? */
	protected $is_sidebar_enabled = false;
}

endif; // CUAR_AbstractContentPageAddOn
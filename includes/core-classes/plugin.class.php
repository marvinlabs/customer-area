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
	
if (!class_exists('CUAR_Plugin')) :

/**
 * The main plugin class
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_Plugin {
	
	public function __construct() {
	}
	
	public function run() {		
		add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ), 3 );
		
		add_action( 'plugins_loaded', array( &$this, 'load_settings' ), 5 );
		add_action( 'plugins_loaded', array( &$this, 'load_addons' ), 10 );
		
		add_action( 'init', array( &$this, 'load_scripts' ), 7 );
		add_action( 'init', array( &$this, 'load_styles' ), 8 );		
		add_action( 'init', array( &$this, 'load_defaults' ), 9 );	
		
		add_action( 'admin_init', array( &$this, 'check_versions' ) );
		
		if ( is_admin() ) {		
			add_action( 'admin_notices', array( &$this, 'print_admin_notices' ) );
			add_action( 'admin_notices', array( &$this, 'show_attention_needed_message' ) );
		} else {
			add_action( 'plugins_loaded', array( &$this, 'load_theme_functions' ), 7 );
		}
	}

	public static function get_instance() {
		global $cuar_plugin;
		return $cuar_plugin;
	}
	
	/*------- MAIN HOOKS INTO WP ------------------------------------------------------------------------------------*/
	
	public function load_settings() {
		$this->settings = new CUAR_Settings( $this );		
	}
	
	/**
	 * Load the translation file for current language. Checks in wp-content/languages first
	 * and then the customer-area/languages.
	 *
	 * Edits to translation files inside customer-area/languages will be lost with an update
	 * **If you're creating custom translation files, please use the global language folder.**
	 */
	public function load_textdomain( $domain = 'cuar', $plugin_name = 'customer-area' ) {
		if ( empty( $domain ) ) $domain = 'cuar';
		if ( empty( $plugin_name ) ) $plugin_name = 'customer-area';
		
		load_plugin_textdomain( $domain, false, $plugin_name . '/languages' );
	}

	/**
	 * Loads the required javascript files (only when not in admin area)
	 */
	public function load_scripts() {
		if ( is_admin() ) return;
	}
	
	/**
	 * Loads the required css (only when not in admin area)
	 */
	public function load_styles() {
		if ( is_admin() ) {
			wp_enqueue_style(
				'cuar.admin',
				$this->get_admin_theme_url() . '/style.css' );
		} else if ( $this->get_option( CUAR_Settings::$OPTION_INCLUDE_CSS ) ) {
			wp_enqueue_style(
					'cuar.frontend',
					$this->get_frontend_theme_url() . '/style.css',
					array( 'dashicons' ), 
					$this->get_version()
				);
		}
	}
	
	/**
	 * 
	 */
	public function check_versions() {
		$plugin_data = get_plugin_data( WP_CONTENT_DIR . '/plugins/' . CUAR_PLUGIN_FILE, false, false );
		$current_version = $plugin_data[ 'Version' ];
		$active_version = $this->get_option( CUAR_Settings::$OPTION_CURRENT_VERSION );
		if ( !isset( $active_version ) ) $active_version = '1.4.0';
		
		if ( CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION!==FALSE ) {
			do_action( 'cuar/core/on-plugin-update', CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION, $current_version );
		} else {
			if ( $active_version != $current_version ) {
				do_action( 'cuar/core/on-plugin-update', $active_version, $current_version );
			} 
			if ( empty( $active_version ) || $active_version != $current_version ) {
				$this->settings->update_option( CUAR_Settings::$OPTION_CURRENT_VERSION, $current_version );
			}
		}		
	}
	
	/**
	 * Initialise some defaults for the plugin (add basic capabilities, ...)
	 */
	public function load_defaults() {	
		// Start a session when we save a post in order to store error logs
		if (!session_id()) session_start();
	}

	/*------- TEMPLATING & THEMING ----------------------------------------------------------------------------------*/

	public function get_theme( $theme_type ) {
		return explode( '%%', $this->get_option( $theme_type=='admin' ? CUAR_Settings::$OPTION_ADMIN_THEME : CUAR_Settings::$OPTION_FRONTEND_THEME, '' ) );		
	}
	
	public function get_theme_url( $theme_type ) {
		$theme = $this->get_theme( $theme_type );
		
		if ( count( $theme )==1 ) {
			// Still not on CUAR 4.0? Option value is already the URL
			return $theme[0];
		} else if ( count( $theme )==2 ) {
			$base = '';
			switch ( $theme[0] ) {
				case 'plugin':
					$base = untrailingslashit(CUAR_PLUGIN_URL) . '/themes/';
					break;
				case 'user-theme':
					$base = untrailingslashit(get_stylesheet_directory_uri()) . '/customer-area/themes/';
					break;
				case 'wp-content':
					$base = untrailingslashit(WP_CONTENT_URL) . '/customer-area/themes/';
					break;
			} 			
			return $base . $theme_type . '/' . $theme[1];
		}
		
		return '';
	}
	
	public function get_theme_path( $theme_type ) {
		$theme = $this->get_theme( $theme_type );
		
		if ( count( $theme )==1 ) {
			// Still not on CUAR 4.0? then we have a problem			
			return '';
		} else if ( count( $theme )==2 ) {
			$base = '';
			switch ( $theme[0] ) {
				case 'plugin':
					$base = untrailingslashit(CUAR_PLUGIN_DIR) . '/themes';
					break;
				case 'user-theme':
					$base = untrailingslashit(get_stylesheet_directory()) . '/customer-area/themes';
					break;
				case 'wp-content':
					$base = untrailingslashit(WP_CONTENT_DIR) . '/customer-area/themes';
					break;
			} 			
			return $base . '/' . $theme_type . '/' . $theme[1];
		}
		
		return '';
	}

	public function get_admin_theme_url() {
		return $this->get_theme_url('admin');
	}
	
	/**
	 * This function offers a way for addons to do their stuff after this plugin is loaded
	 */
	public function get_frontend_theme_url() {
		return $this->get_theme_url('frontend');
	}
	
	/**
	 * This function offers a way for addons to do their stuff after this plugin is loaded
	 */
	public function get_frontend_theme_path() {
		return $this->get_theme_path('frontend');
	}
	
	public function load_theme_functions() {
		$theme_path = trailingslashit($this->get_frontend_theme_path());
		if ( empty( $theme_path ) ) return;
		
		$functions_path = $theme_path . 'cuar-functions.php';
		if ( file_exists( $functions_path ) ) {
			include_once( $functions_path );
		}
	}
	
	/**
	 * Takes a default template file as parameter. It will look in the theme's directory to see if the user has
	 * customized the template. If so, it returns the path to the customized file. Else, it returns the default
	 * passed as parameter.
	 * 
	 * Order of preference is:
	 * 1. user-directory/filename
	 * 2. user-directory/fallback-filename
	 * 3. default-directory/filename
	 * 4. default-directory/fallback-filename
	 * 
	 * @param string $default_path
	 */
	public function get_template_file_path( $default_root, $filename, $sub_directory = '', $fallback_filename = '' ) {		
		$relative_path = ( !empty( $sub_directory ) ) ? trailingslashit( $sub_directory ) . $filename : $filename;
		
		$possible_locations = apply_filters( 'cuar/ui/template-directories', 
				array(
					untrailingslashit(WP_CONTENT_DIR) . '/customer-area',
					untrailingslashit(get_stylesheet_directory()) . '/customer-area',
					untrailingslashit(get_stylesheet_directory()) ) );
		
		// Look for the preferred file first
		foreach ( $possible_locations as $dir ) {
			$path =  trailingslashit( $dir ) . $relative_path;
			if ( file_exists( $path ) ) {
				if ( $this->get_option( CUAR_Settings::$OPTION_DEBUG_TEMPLATES ) ) {
					$this->print_template_path_debug_info( $path );
				}
				
				return $path;
			}
		}
		
		// Then for the fallback alternative if any
		if ( !empty( $fallback_filename ) ) {
			$fallback_relative_path = ( !empty( $sub_directory ) ) 
											? trailingslashit( $sub_directory ) . $fallback_filename 
											: $fallback_filename;
		
			foreach ( $possible_locations as $dir ) {
				$path =  trailingslashit( $dir ) . $fallback_relative_path;
				if ( file_exists( $path ) ) {
					if ( $this->get_option( CUAR_Settings::$OPTION_DEBUG_TEMPLATES ) ) {
						$this->print_template_path_debug_info( $path );
					}
					
					return $path;
				}
			}
		}
		
		// Then from default directories. We allow multiple default root directories.
		if ( !is_array( $default_root ) ) {
			$default_root = array( $default_root );
		}
		
		foreach ( $default_root as $root_path ) {
			$path =  trailingslashit( $root_path ) . $relative_path;
			if ( file_exists( $path ) ) {
				if ( $this->get_option( CUAR_Settings::$OPTION_DEBUG_TEMPLATES ) ) {
					$this->print_template_path_debug_info( $path );
				}
				
				return $path;
			}
	
			if ( !empty( $fallback_filename ) ) {
				$path =  trailingslashit( $root_path ) . $fallback_relative_path;
				if ( file_exists( $path ) ) {
					if ( $this->get_option( CUAR_Settings::$OPTION_DEBUG_TEMPLATES ) ) {
						$this->print_template_path_debug_info( $path, $filename );
					}
					
					return $path;
				}
			}
		}
		
		if ( $this->get_option( CUAR_Settings::$OPTION_DEBUG_TEMPLATES ) ) {
			echo "\n<!-- CUAR TEMPLATE < $filename > REQUESTED BUT NOT FOUND (WILL NOT BE USED) -->\n";
		}
		
		return '';
	}
	
	private function print_template_path_debug_info( $path, $original_filename=null ) {	
		$dirname = dirname( $path );
		$strip_from = strpos( $path, 'customer-area' );
		$dirname = $strip_from>0 ? strstr( $dirname, 'customer-area' ) : $dirname;
		
		$filename = basename( $path );
		
		if ( $original_filename==null ) {
			echo "\n<!-- CUAR TEMPLATE < $filename > IN FOLDER < $dirname > -->\n";
		} else {
			echo "\n<!-- CUAR TEMPLATE < $original_filename > NOT FOUND BUT USING FALLBACK: < $filename > IN FOLDER < $dirname > -->\n";
		}
	}
	
	public function is_customer_area_page() {
		$cp_addon = $this->get_addon( 'customer-pages' );
		return $cp_addon->is_customer_area_page();
	}
	
	public function get_customer_page_id( $slug ) {
		$cp_addon = $this->get_addon( 'customer-pages' );
		return $cp_addon->get_page_id( $slug );
	}
	
	public function login_then_redirect_to_page( $page_slug ) {
		$cp_addon = $this->get_addon('customer-pages');
		$redirect_page_id = $cp_addon->get_page_id( $page_slug );
		if ( $redirect_page_id>0 ) {
			$redirect_url = get_permalink( $redirect_page_id );
		} else {
			$redirect_url = '';
		}
	
		$this->login_then_redirect_to_url( $redirect_url );
	}
	
	public function login_then_redirect_to_url( $redirect_to='' ) {
		$login_url = apply_filters( 'cuar/routing/login-url', null, $redirect_to );		
		if ( $login_url==null ) {
			$login_url = wp_login_url( $redirect_to );
		}	
		
		wp_redirect( $login_url );
		exit;
	}

	/*------- GENERAL MAINTENANCE -----------------------------------------------------------------------------------*/
	
	public function set_attention_needed( $section_id, $message ) {
		$status_sections = get_option( self::$OPTION_STATUS_SECTIONS );
		
		if ( $status_sections==null ) $status_sections = array();
		
		if ( !empty( $message ) ) {
			$status_sections[$section_id] = $message;
		} else if ( isset( $status_sections[$section_id] ) ) {
			unset( $status_sections[$section_id] );
		}		
		
		update_option( self::$OPTION_STATUS_SECTIONS, $status_sections );
	}
	
	public function clear_attention_needed( $section_id ) {
		$status_sections = get_option( self::$OPTION_STATUS_SECTIONS );
		unset( $status_sections[$section_id] );
		update_option( self::$OPTION_STATUS_SECTIONS, $status_sections );
	}
	
	public function is_attention_needed( $section_id ) {
		$status_sections = get_option( self::$OPTION_STATUS_SECTIONS );

		if ( $status_sections!=null 
				&& isset( $status_sections[$section_id] )
				&& !empty( $status_sections[$section_id] ) ) {
			return true;
		}
		
		return false;
	}
	
	public function is_warning_ignored( $warning_id ) {
		$warnings = get_option( self::$OPTION_IGNORE_WARNINGS );
		return isset( $warnings[$warning_id] ) && $warnings[$warning_id]==true;
	}
	
	public function ignore_warning( $warning_id ) {
		$warnings = get_option( self::$OPTION_IGNORE_WARNINGS );
		$warnings[$warning_id] = true;
		update_option( self::$OPTION_IGNORE_WARNINGS, $warnings );
	}
	
	public function get_attention_needed_messages() {
		return get_option( self::$OPTION_STATUS_SECTIONS, array() );
	}
	
	public function show_attention_needed_message() {
		if ( isset( $_GET['page'] ) && $_GET['page']=='cuar-status' ) return;
		
		$status_sections = $this->get_attention_needed_messages();
		if ( $status_sections==null || empty( $status_sections ) ) return;
		
		echo '<div id="message" class="error">';
		
		echo '<p>';
		_e( 'The Customer Area plugin requires your attention. The issues we have detected are the following: ', 'cuar' );
		echo '</p>';
		echo '<ul class="cuar-with-bullets">';		
		foreach ( $status_sections as $id => $message ) {
			echo '<li>' . $message . '</li>';
		}		
		echo '</ul>';		
		echo '<p><strong>';
		printf( __( 'You can view the details of those issues and fix them on the <a href="%1$s">Customer Area status page</a>.', 'cuar' ),
				admin_url('admin.php?page=cuar-status&cuar_section=needs-attention' ) );
		echo '</strong></p>';
		echo '</div>';
	}

	public static $OPTION_STATUS_SECTIONS = 'cuar/core/status/sections';
	public static $OPTION_IGNORE_WARNINGS = 'cuar_ignore_warnings';
	
	/*------- SETTINGS ----------------------------------------------------------------------------------------------*/
	
	/**
	 * Access to the settings (delegated to our settings class instance)
	 * @param unknown $option_id
	 */
	public function get_option( $option_id ) {
		return $this->settings->get_option( $option_id );
	}
	
	public function update_option( $option_id, $new_value, $commit = true ) {
		return $this->settings->update_option( $option_id, $new_value, $commit );
	}
	
	public function save_options() {
		return $this->settings->save_options();
	}
	
	public function reset_defaults() {
		return $this->settings->reset_defaults();
	}
	
	public function get_default_options() {
		return $this->settings->get_default_options();
	}
	
	public function get_version() {
		return $this->get_option( CUAR_Settings::$OPTION_CURRENT_VERSION );
	}
	
	public function get_options() {
		return $this->settings->get_options();
	}
	
	public function set_options( $opt ) {
		return $this->settings->set_options( $opt );
	}
	
	/** @var CUAR_Settings */
	private $settings;

	/*------- ADD-ONS -----------------------------------------------------------------------------------------------*/

	/**
	 * This function offers a way for addons to do their stuff after this plugin is loaded
	 */
	public function load_addons() {		
		do_action( 'cuar/core/addons/before-init', $this );
		do_action( 'cuar/core/addons/init', $this );
		do_action( 'cuar/core/addons/after-init', $this );

		// Check add-on versions
		if ( is_admin() ) {
			$this->check_permalinks_enabled();
			$this->check_addons_required_versions();
			$this->check_templates();
		}
	}
	
	/**
	 * Register an add-on in the plugin
	 * @param CUAR_AddOn $addon
	 */
	public function register_addon( $addon ) {
		$this->registered_addons[$addon->addon_id] = $addon;
	}
	
	/**
	 * Get registered add-ons
	 */
	public function get_registered_addons() {
		return $this->registered_addons;
	}
	
	public function tag_addon_as_commercial( $addon_id ) {
		return $this->commercial_addons[$addon_id] = $this->get_addon( $addon_id );
	}
	
	public function get_commercial_addons() {
		return $this->commercial_addons;
	}
	
	public function has_commercial_addons() {
		return !empty( $this->commercial_addons );
	}
	
	/**
	 * Shows a compatibity warning
	 */
	public function check_permalinks_enabled() {
		if ( !get_option('permalink_structure') ) {
			$this->set_attention_needed( 'permalinks-disabled',
					__( 'Permalinks are disabled, Customer Area will not work properly. Please enable them in the WordPress settings.', 'cuar' ) );
		} else {
			$this->clear_attention_needed( 'permalinks-disabled' );
		}
	}
	
	/**
	 * Shows a compatibity warning
	 */
	public function check_addons_required_versions() {
		$current_version = $this->get_version();
		$needs_attention = false;
		
		foreach ( $this->registered_addons as $id => $addon ) {
			if ( $current_version < $addon->min_cuar_version ) {
				$needs_attention = true;
				break;
			}
		}
		
		if ( $needs_attention ) {
			$this->set_attention_needed( 'outdated-plugin-version',
					__('Some add-ons require a newer version of the main plugin.', 'cuar') );
		} else {
			$this->clear_attention_needed( 'outdated-plugin-version' );
		}
	}
	
	/**
	 * Get add-on
	 */
	public function get_addon($id) {
		return isset( $this->registered_addons[$id] ) ? $this->registered_addons[$id] : null;
	}
	
	/** @var array */
	private $registered_addons = array();
	
	/** @var array */
	private $commercial_addons = array();

	/*------- ADMIN NOTICES -----------------------------------------------------------------------------------------*/
	
	/**
	 * Print the eventual errors that occured during a post save/update
	 */
	public function print_admin_notices() {
		$notices = $this->get_admin_notices();
		
		if ( $notices ) {
			foreach ( $notices as $n ) {
				echo sprintf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $n['type'] ), $n['msg'] );
			}
		}
		$this->clear_admin_notices();
	}
	
	/**
	 * Remove the notices stored in the session for save posts
	 */
	public function clear_admin_notices() {
		if ( isset( $_SESSION['cuar_admin_notices'] ) ) {
			unset( $_SESSION['cuar_admin_notices'] ); 
		}
	}

	/**
	 * Remove the stored notices
	 */
	private function get_admin_notices() {
		return isset( $_SESSION[ 'cuar_admin_notices' ] ) && !empty( $_SESSION[ 'cuar_admin_notices' ] ) ? $_SESSION['cuar_admin_notices'] : false;
	}
	
	/**
	 * Add an admin notice (useful when in a save post function for example)
	 * 
	 * @param string $msg
	 * @param string $type error or updated
	 */
	public function add_admin_notice( $msg, $type = 'error' ) {
		if ( empty( $_SESSION[ 'cuar_admin_notices' ] ) ) {
			$_SESSION[ 'cuar_admin_notices' ] = array();
	 	}
	 	$_SESSION[ 'cuar_admin_notices' ][] = array(
				'type' 	=> $type,
				'msg' 	=> $msg 
	 		);
	}

	/*------- EXTERNAL LIBRARIES ------------------------------------------------------------------------------------*/
	
	/**
	 * Allow the use of an external library provided by Customer Area
	 * 
	 * @param string $id The ID for the external library
	 */
	public function enable_library( $library_id ) {
		do_action( 'cuar/core/libraries/before-enable?id=' . $library_id );
		
		switch ( $library_id ) {
			case 'jquery.select2': {
				wp_enqueue_script( 'jquery.select2', CUAR_PLUGIN_URL . 'libs/select2/select2.min.js', array('jquery'), $this->get_version() );

				$locale = get_locale();
				if ( $locale && !empty( $locale ) ) {
					$locale = str_replace("_", "-", $locale );
					$locale_parts = explode( "-", $locale );
					
					$loc_files = array( 'select2_locale_' . $locale . '.js' );
					
					if ( count( $locale_parts ) > 0 ) {
						$loc_files[] = 'select2_locale_' . $locale_parts[0] . '.js';
					}
					
					foreach ( $loc_files as $lf ) {
						if ( file_exists( CUAR_PLUGIN_DIR . '/libs/select2/' . $lf ) ) {
							wp_enqueue_script( 'jquery.select2.locale', CUAR_PLUGIN_URL . 'libs/select2/' . $lf, array('jquery.select2'), $this->get_version() );
							break;
						}
					}					
				}
				
				wp_enqueue_style( 'jquery.select2', CUAR_PLUGIN_URL . 'libs/select2/select2.css', $this->get_version() );
			}
			break;

			case 'bootstrap.dropdown': {
				wp_enqueue_script( 'bootstrap.transition', CUAR_PLUGIN_URL . 'libs/bootstrap/js/transition.js', array('jquery'), $this->get_version() );
				wp_enqueue_script( 'bootstrap.dropdown', CUAR_PLUGIN_URL . 'libs/bootstrap/js/dropdown.js', array('jquery', 'bootstrap.transition'), $this->get_version() );
			}
			break;

			case 'bootstrap.transition': {
				wp_enqueue_script( 'bootstrap.transition', CUAR_PLUGIN_URL . 'libs/bootstrap/js/transition.js', array('jquery'), $this->get_version() );
			}
			break;

			case 'bootstrap.collapse': {
				wp_enqueue_script( 'bootstrap.collapse', CUAR_PLUGIN_URL . 'libs/bootstrap/js/collapse.js', array('jquery'), $this->get_version() );
			}
			break;

			case 'jquery.knob': {
				wp_enqueue_script( 'jquery.knob', CUAR_PLUGIN_URL . 'libs/knob/jquery.knob.min.js', array('jquery'), $this->get_version() );
			}
			break;
			
			default:
				do_action( 'cuar/core/libraries/enable?id=' . $library_id );
		}
		
		do_action( 'cuar/core/libraries/after-enable?id=' . $library_id );
	}

	/*------- TEMPLATES ---------------------------------------------------------------------------------------------*/
	
	public static function enable_check_templates( $enable=true ) {
		update_option( 'cuar_check_templates_required', $enable );
	}
	
	public function is_check_templates_enabled() {
		return get_option( 'cuar_check_templates_required', false );
	}
	
	public function check_templates() {
		if ( $this->is_check_templates_enabled()!=true ) {
			return;
		}

		include( CUAR_PLUGIN_DIR . '/includes/core-addons/status/template-finder.class.php' );
		$dirs_to_scan = apply_filters( 'cuar/core/status/directories-to-scan', array( CUAR_PLUGIN_DIR => __( 'Customer Area', 'cuar' ) ) );
		
		$outdated_templates = array();		
		foreach ( $dirs_to_scan as $dir => $title ) {
			$template_finder = new CUAR_TemplateFinder( $this );
			$template_finder->scan_directory( $dir );
			
			$tmp = $template_finder->get_outdated_templates();
			if ( !empty( $tmp ) ) {
				$outdated_templates[ $title ] = $tmp;
			}
		}
		
		if ( !empty( $outdated_templates ) ) {
			$this->set_attention_needed( 'outdated-templates', __( 'Some template files you have overriden seem to be outdated.', 'cuar' ) );
		} else {
			$this->clear_attention_needed( 'outdated-templates' );
			self::enable_check_templates( false );
		}
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/
	
	public static function on_activate() {
		self::enable_check_templates();
		flush_rewrite_rules();
	}
	
	/**
	 * Tells which post types are private (shown on the customer area page)
	 * @return array
	 */
	public function get_content_post_types() {
		return apply_filters( 'cuar/core/post-types/content', array() );
	}	
	
	/**
	 * Get the content types descriptors. Each descriptor is an array with:
	 * - 'label-plural'				- plural label
	 * - 'label-singular'			- singular label
	 * - 'content-page-addon'		- content page addon associated to this type 
	 * 
	 * @return array keys are post_type and values are arrays as described above
	 */
	public function get_content_types() {
		return apply_filters( 'cuar/core/types/content', array() );
	}	
	
	/**
	 * Tells which container post types are available
	 * @return array
	 */
	public function get_container_post_types() {
		return apply_filters( 'cuar/core/post-types/container', array() );
	}	
	
	/**
	 * Get the post type descriptors. Each descriptor is an array with:
	 * - 'label-plural'				- plural label
	 * - 'label-singular'			- singular label
	 * - 'container-page-slug'		- main page slug associated to this type 
	 * 
	 * @return array
	 */
	public function get_container_types() {
		return apply_filters( 'cuar/core/types/container', array() );
	}	
		
	public function get_default_wp_editor_settings() {
		return apply_filters( 'cuar/ui/default-wp-editor-settings', array(
				'textarea_rows'	=> 5,
				'editor_class'	=> 'form-control',
				'quicktags'		=> false,
				'media_buttons' => false,
				'teeny' 		=> true,
				'dfw' 			=> true
			) );
	}
}

endif; // if (!class_exists('CUAR_Plugin')) :
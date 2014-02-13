<?php
/*
 * Copyright 2013 MarvinLabs (contact@marvinlabs.com) This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
require_once (CUAR_INCLUDES_DIR . '/helpers/wordpress-helper.class.php');

if (! class_exists ( 'CUAR_Settings' )) :
	
	/**
	 * Creates the UI to change the plugin settings in the admin area.
	 * Also used to access the plugin settings
	 * stored in the DB (@see CUAR_Plugin::get_option)
	 */
	class CUAR_Settings {
		public function __construct($plugin) {
			$this->plugin = $plugin;
			$this->setup ();
			$this->reload_options ();
		}
		
		/**
		 * Get the value of a particular plugin option
		 *
		 * @param string $option_id
		 *        	the ID of the option to get
		 * @return mixed the value
		 */
		public function get_option($option_id) {
			return isset ( $this->options [$option_id] ) ? $this->options [$option_id] : null;
		}
		
		/**
		 * Setup the WordPress hooks we need
		 */
		public function setup() {
			if (is_admin ()) {
				add_action ( 'cuar_admin_submenu_pages', array (
						&$this,
						'add_settings_menu_item' 
				), 100 );
				add_action ( 'admin_init', array (
						&$this,
						'page_init' 
				) );
				
				// Links under the plugin name
				$plugin_file = 'customer-area/customer-area.php';
				add_filter( "plugin_action_links_{$plugin_file}", array( &$this, 'print_plugin_action_links' ), 10, 2 );
				
				// We have some core settings to take care of too
				add_filter( 'cuar_addon_settings_tabs', array( &$this, 'add_core_settings_tab' ), 5, 1 );
				
				add_action( 'cuar_addon_print_settings_cuar_core', array( &$this, 'print_core_settings' ), 10, 2 );
				add_filter( 'cuar_addon_validate_options_cuar_core', array( &$this,	'validate_core_settings' ), 10, 3 );
				
				add_action( 'cuar_addon_print_settings_cuar_frontend', array( &$this, 'print_frontend_settings' ), 10, 2 );
				add_filter( 'cuar_addon_validate_options_cuar_frontend', array( &$this,	'validate_frontend_settings' ), 10, 3 );
			
				add_action( 'wp_ajax_cuar_validate_license', array( 'CUAR_Settings', 'ajax_validate_license' ) );
			}
		}
		
		public function flush_rewrite_rules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
		
		/**
		 * Add the menu item
		 */
		public function add_settings_menu_item($submenus) {
			$separator = '<span class="cuar-menu-divider"></span>';	
			
			$submenu = array (
					'page_title' => __ ( 'Settings', 'cuar' ),
					'title' => $separator . __ ( 'Settings', 'cuar' ),
					'slug' => self::$OPTIONS_PAGE_SLUG,
					'function' => array (
							&$this,
							'print_settings_page' 
					),
					'capability' => 'manage_options' 
			);
			
			$submenus [] = $submenu;
			
			return $submenus;
		}
		public function print_plugin_action_links($links, $file) {
			$link = '<a href="' . get_bloginfo ( 'wpurl' ) . '/wp-admin/admin.php?page=' . self::$OPTIONS_PAGE_SLUG . '">' . __ ( 'Settings', 'cuar' ) . '</a>';
			array_unshift ( $links, $link );
			return $links;
		}
		
		/**
		 * Output the settings page
		 */
		public function print_settings_page() {
			if ( isset( $_GET['run-setup-wizard'] ) ) {
				$success = false;
				
				if ( isset( $_POST['submit'] ) ) {
					$errors = array();
					if ( !isset( $_POST["cuar_page_title"] ) || empty( $_POST["cuar_page_title"] ) ) {
						$errors[] = __( 'The page title cannot be empty', 'cuar');
					}
					
					if ( empty($errors) ) {
						$post_data = array(
								'post_content' 		=> '[customer-area /]',
								'post_title' 		=> $_POST["cuar_page_title"], 
								'post_status' 		=> 'publish',
								'post_type' 		=> 'page', 
								'comment_status' 	=> 'closed', 
								'ping_status' 		=> 'closed'
							);						
						$page_id = wp_insert_post($post_data);
						if ( is_wp_error( $page_id ) ) {
							$errors[] = $page_id->get_error_message();
						} else {
							$this->plugin->get_addon( 'customer-pages' )->set_customer_page_id( $page_id );
						}
						
						if ( empty($errors) ) $success = true;
					}
				}
				
				if ( $_GET['run-setup-wizard']==1664 ) {
					$success = true;
				}
				
				if ( $success ) {
					include (CUAR_INCLUDES_DIR . '/setup-wizard-done.view.php');
				} else {
					include (CUAR_INCLUDES_DIR . '/setup-wizard.view.php');
				}
			} else {
				include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-classes',
					'settings.template.php',
					'templates' ));
			}
		}
		
		/**
		 * Register the settings
		 */
		public function page_init() {
			$this->setup_tabs ();
			
			// Register the main settings and for the current tab too
			register_setting ( self::$OPTIONS_GROUP, self::$OPTIONS_GROUP, array (
					&$this,
					'validate_options' 
				) );
			register_setting ( self::$OPTIONS_GROUP . '_' . $this->current_tab, self::$OPTIONS_GROUP, array (
					&$this,
					'validate_options' 
				) );
			
			// Let the current tab add its own settings to the page
			do_action ( "cuar_addon_print_settings_{$this->current_tab}", $this, self::$OPTIONS_GROUP . '_' . $this->current_tab );
		}
		
		/**
		 * Create the tabs to show
		 */
		public function setup_tabs() {
			$this->tabs = apply_filters ( 'cuar_addon_settings_tabs', array () );
			
			// Get current tab from GET or POST params or default to first in list
			$this->current_tab = isset ( $_GET ['cuar_tab'] ) ? $_GET ['cuar_tab'] : '';
			if (! isset ( $this->tabs [$this->current_tab] )) {
				$this->current_tab = isset ( $_POST ['cuar_tab'] ) ? $_POST ['cuar_tab'] : '';
			}
			if (! isset ( $this->tabs [$this->current_tab] )) {
				reset ( $this->tabs );
				$this->current_tab = key ( $this->tabs );
			}
		}
		
		/**
		 * Save the plugin settings
		 * 
		 * @param array $input
		 *        	The new option values
		 * @return
		 *
		 */
		public function validate_options($input) {
			$validated = array ();
			
			// Allow addons to validate their settings here
			$validated = apply_filters ( 'cuar_addon_validate_options_' . $this->current_tab, $validated, $this, $input );
			
			$this->options = array_merge ( $this->options, $validated );
			
			// Also flush rewrite rules
			global $wp_rewrite;
			$wp_rewrite->flush_rules ();
			
			return $this->options;
		}
		
		/* ------------ CORE SETTINGS ----------------------------------------------------------------------------------- */
		
		/**
		 * Add a tab
		 * 
		 * @param array $tabs        	
		 * @return array
		 */
		public function add_core_settings_tab($tabs) {
			$tabs ['cuar_core'] 	= __ ( 'General', 'cuar' );
			$tabs ['cuar_frontend'] = __ ( 'Frontend', 'cuar' );
			return $tabs;
		}
		
		/**
		 * Add our fields to the settings page
		 *
		 * @param CUAR_Settings $cuar_settings
		 *        	The settings class
		 */
		public function print_core_settings($cuar_settings, $options_group) {
			// General settings
			add_settings_section ( 'cuar_general_settings', 
					__ ( 'General Settings', 'cuar' ), 
					array ( &$cuar_settings, 'print_empty_section_info' ), 
					self::$OPTIONS_PAGE_SLUG );
			
			add_settings_field ( self::$OPTION_ADMIN_THEME, 
					__ ( 'Admin theme', 'cuar' ), 
					array ( &$cuar_settings, 'print_theme_select_field' ), 
					self::$OPTIONS_PAGE_SLUG, 
					'cuar_general_settings', 
					array (
							'option_id' => self::$OPTION_ADMIN_THEME,
							'theme_type' => 'admin' 
						)
				);
			
			add_settings_field ( self::$OPTION_HIDE_SINGLE_OWNER_SELECT, 
					__ ( 'Hide owner selection', 'cuar' ), 
					array ( &$cuar_settings, 'print_input_field' ), 
					self::$OPTIONS_PAGE_SLUG, 
					'cuar_general_settings', 
					array (
							'option_id' => self::$OPTION_HIDE_SINGLE_OWNER_SELECT,
							'type' => 'checkbox',
							'after' => __ ( 'Hide the owner selection field when a single owner is possible.', 'cuar' ) 
									. '<p class="description">' 
									. __ ( 'The owner will not be displayed but included directly if a single owner type is available to the current user and if within that owner type, a single owner is available.', 'cuar' ) 
									. '</p>' 
						) 
				);
		}
		
		/**
		 * Validate core options
		 *
		 * @param CUAR_Settings $cuar_settings        	
		 * @param array $input        	
		 * @param array $validated        	
		 */
		public function validate_core_settings($validated, $cuar_settings, $input) {
			$cuar_settings->validate_not_empty ( $input, $validated, self::$OPTION_ADMIN_THEME );
			$cuar_settings->validate_boolean ( $input, $validated, self::$OPTION_HIDE_SINGLE_OWNER_SELECT );
			
			return $validated;
		}
		
		/**
		 * Add our fields to the settings page
		 *
		 * @param CUAR_Settings $cuar_settings
		 *        	The settings class
		 */
		public function print_frontend_settings($cuar_settings, $options_group) {
			// General settings
			add_settings_section ( 'cuar_general_settings', 
					__ ( 'General Settings', 'cuar' ), 
					array ( &$cuar_settings, 'print_empty_section_info' ), 
					self::$OPTIONS_PAGE_SLUG );
			
			add_settings_field ( self::$OPTION_INCLUDE_CSS, 
					__ ( 'Include CSS', 'cuar' ), 
					array( &$cuar_settings,	'print_input_field'	), 
					self::$OPTIONS_PAGE_SLUG, 
					'cuar_general_settings', 
					array (
							'option_id' => self::$OPTION_INCLUDE_CSS,
							'type' => 'checkbox',
							'after' => __ ( 'Include the default stylesheet.', 'cuar' ) . '<p class="description">' . __ ( 'If not, you should style the plugin yourself in your theme.', 'cuar' ) . '</p>' 
						) 
				);
			
			add_settings_field ( self::$OPTION_FRONTEND_THEME, 
					__ ( 'Frontend theme', 'cuar' ), 
					array ( &$cuar_settings, 'print_theme_select_field' ), 
					self::$OPTIONS_PAGE_SLUG, 
					'cuar_general_settings', 
					array (
							'option_id' => self::$OPTION_FRONTEND_THEME,
							'theme_type' => 'frontend' 
						) 
				);
		}
		
		/**
		 * Validate frontend options
		 *
		 * @param CUAR_Settings $cuar_settings        	
		 * @param array $input        	
		 * @param array $validated        	
		 */
		public function validate_frontend_settings($validated, $cuar_settings, $input) {
			$cuar_settings->validate_boolean ( $input, $validated, self::$OPTION_INCLUDE_CSS );
			$cuar_settings->validate_not_empty ( $input, $validated, self::$OPTION_FRONTEND_THEME );
			
			return $validated;
		}
		
		public function print_empty_section_info() {
		}
				
		/**
		 * Set the default values for the core options
		 *
		 * @param array $defaults        	
		 * @return array
		 */
		public static function set_default_core_options($defaults) {
			$defaults [self::$OPTION_INCLUDE_CSS] = true;
			$defaults [self::$OPTION_ADMIN_THEME] = CUAR_ADMIN_THEME;
			$defaults [self::$OPTION_FRONTEND_THEME] = CUAR_FRONTEND_THEME;
			$defaults [self::$OPTION_HIDE_SINGLE_OWNER_SELECT] = false;
			
			return $defaults;
		}
		
		/* ------------ VALIDATION HELPERS ------------------------------------------------------------------------------ */
		
		/**
		 * Validate a page
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_post_id($input, &$validated, $option_id) {
			if ( $input[$option_id]==-1 || get_post( $input[$option_id] ) ) {
				$validated[$option_id] = $input[$option_id];
			} else {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$option_id] . __ ( ' is not a valid post', 'cuar' ), 'error' );
			}
		}
		
		/**
		 * Validate a boolean value within an array
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_boolean($input, &$validated, $option_id) {
			$validated[$option_id] = isset ( $input[$option_id] ) ? true : false;
		}
		
		/**
		 * Validate a role
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_role($input, &$validated, $option_id) {
			$role = $input[$option_id];			
			if ( isset( $role ) && ( $role=="cuar_any" || null!=get_role( $input[$option_id] ) ) ) {
				$validated[$option_id] = $input[$option_id];
			} else {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$option_id] . __ ( ' is not a valid role', 'cuar' ), 'error' );
			}
		}
		
		/**
		 * Validate a value in any case
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_license_key($input, &$validated, $option_id, $store_url, $product_name) {
			$validated[$option_id] = trim ( $input[$option_id] );
			
// 			// Only validate on license change or every N days
// 			$last_check = $this->get_option ( $option_id . '_lastcheck' );
			
// 			if ($last_check) {
// 				$days_since_lastcheck = (time () - intval ( $last_check )) / (24 * 60 * 60);
// 			} else {
// 				$days_since_lastcheck = 99999;
// 			}
			
// 			if ($days_since_lastcheck > 2 || $input[$option_id] != $this->get_option ( $option_id )) {
// 				// data to send in our API request
// 				$api_params = array (
// 						'edd_action' => 'activate_license',
// 						'license' => $validated[$option_id],
// 						'item_name' => urlencode ( $product_name ) 
// 					);
				
// 				// Call the custom API.
// 				$response = wp_remote_get ( add_query_arg ( $api_params, $store_url ), array (
// 						'timeout' => 15,
// 						'sslverify' => false 
// 					) );
				
// 				// make sure the response came back okay
// 				if (is_wp_error ( $response ))
// 					return false;
					
// 				// decode the license data
// 				// $license_data->license will be either "active" or "inactive"
// 				$response = wp_remote_retrieve_body( $response );
// 				$license_data = json_decode( $response );
				
// 				$validated[$option_id . '_status'] = $license_data->license;
// 				$validated[$option_id . '_lastcheck'] = time ();
// 				$validated[$option_id . '_response'] = $response;
// 			}
		}
		
		/**
		 * Validate a value which should simply be not empty
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_not_empty($input, &$validated, $option_id) {
			if (isset ( $input[$option_id] ) && ! empty ( $input[$option_id] )) {
				$validated[$option_id] = $input[$option_id];
			} else {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$option_id] . __ ( ' cannot be empty', 'cuar' ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
			}
		}
		
		/**
		 * Validate an email address
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_email($input, &$validated, $option_id) {
			if (isset ( $input[$option_id] ) && is_email ( $input[$option_id] )) {
				$validated[$option_id] = $input[$option_id];
			} else {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$option_id] . __ ( ' is not a valid email', 'cuar' ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
			}
		}
		
		/**
		 * Validate an enum value within an array
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 * @param array $enum_values
		 *        	Array of possible values
		 */
		public function validate_enum($input, &$validated, $option_id, $enum_values) {
			if (! in_array ( $input[$option_id], $enum_values )) {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$option_id] . __ ( ' is not a valid value', 'cuar' ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
				return;
			}
			
			$validated[$option_id] = $input[$option_id];
		}
		
		/**
		 * Validate an integer value within an array
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 * @param int $min
		 *        	Min value for the int (set to null to ignore check)
		 * @param int $max
		 *        	Max value for the int (set to null to ignore check)
		 */
		public function validate_int($input, &$validated, $option_id, $min = null, $max = null) {
			// Must be an int
			if (! is_int ( intval ( $input[$option_id] ) )) {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . __ ( 'must be an integer', 'cuar' ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
				return;
			}
			
			// Must be > min
			if ($min !== null && $input[$option_id] < $min) {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . sprintf ( __ ( 'must be greater than %s', 'cuar' ), $min ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
				return;
			}
			
			// Must be < max
			if ($max !== null && $input[$option_id] > $max) {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . sprintf ( __ ( 'must be lower than %s', 'cuar' ), $max ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
				return;
			}
			
			// All good
			$validated[$option_id] = intval ( $input[$option_id] );
		}
		
		/**
		 * Validate a value which should be an owner type
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_owner_type($input, &$validated, $option_id) {
			$po_addon = $this->plugin->get_addon ( "post-owner" );
			
			if (isset ( $input[$option_id] ) && $po_addon->is_valid_owner_type ( $input[$option_id] )) {
				$validated[$option_id] = $input[$option_id];
			} else {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$option_id] . __ ( ' is not a valid owner type', 'cuar' ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
			}
		}
		
		/**
		 * Validate a value which should be an owner
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_owner($input, &$validated, $option_id, $owner_type_option_id) {
			$field_id = $option_id . '_' . $input[$owner_type_option_id] . '_id';
			
			// TODO better checks, for now, just check it is not empty
			// $po_addon = $this->plugin->get_addon("post-owner");
			if (isset ( $input[$field_id] )) {
				$validated[$option_id] = is_array( $input[$field_id] ) ? $input[$field_id] : array( $input[$field_id] );
			} else {
				add_settings_error ( $option_id, 'settings-errors', $option_id . ': ' . $input[$field_id] . __ ( ' is not a valid owner', 'cuar' ), 'error' );
				
				$validated[$option_id] = $this->default_options [$option_id];
			}
		}
		
		/**
		 * Validate a term id (for now, we just check it is not empty and strictly positive)
		 *
		 * @param array $input
		 *        	Input array
		 * @param array $validated
		 *        	Output array
		 * @param string $option_id
		 *        	Key of the value to check in the input array
		 */
		public function validate_term( $input, &$validated, $option_id, $taxonomy ) {			
			// TODO better checks, for now, we just check it is not empty and strictly positive
			if (isset( $input[$option_id] ) && $input[$option_id] > 0 ) {
				$validated[$option_id] = $input[$option_id];
			} else {
				$validated[$option_id] = -1;
			}
		}
	
		/** 
		 * Handles remote requests to validate a license
		 */
		public static function ajax_validate_license() {
			global $cuar_plugin;
			
			$addon_id = $_POST["addon_id"];		
			$license = $_POST["license"];		
			$today = new DateTime();
			
			$addon = $cuar_plugin->get_addon( $addon_id );
			
			$license_key_option_id = $addon->get_license_key_option_name();
			$cuar_plugin->update_option( $license_key_option_id, $license );
			
			// data to send in our API request
			$api_params = array (
					'edd_action' 	=> 'activate_license',
					'license' 		=> $license,
					'item_name' 	=> urlencode( $addon->store_item_name )
				);
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, CUAR_AddOn::$STORE_URL ), array (
					'timeout' => 15,
					'sslverify' => false
				) );
			
			$result = new stdClass();
			$result->result_container = $license_key_option_id . '_check_result';
			
			// make sure the response came back okay
			if (is_wp_error( $response )) {
				$result->success = false;
				$result->message = $response->get_error_message();
	
				echo json_encode( $result );
				exit;
			} 
	
			$response = wp_remote_retrieve_body( $response );		
			$license_data = json_decode( $response );
			
			$result->response = json_encode( $response, JSON_PRETTY_PRINT );
			
			if ( $license_data->license=='valid' ) {
				$expiry_date = new DateTime($license_data->expires);
			
				$result->success = true;
				$result->expires = $license_data->expires;
				$result->message = sprintf( __( 'Your license is valid until: %s', 'cuar' ), $expiry_date->format( get_option('date_format') ) );
			} else {
				$result->success = false;
				
				switch ( $license_data->error ) {
					case 'revoked': 
						$result->message = __( 'This license key has been revoked.', 'cuar' );
						break;

					case 'no_activations_left':
						$result->message = sprintf( __( 'You have reached your maximum number of sites for this license key. You can use it on at most %s site(s).', 'cuar' ), $license_data->max_sites );
						break;

					case 'expired':
						$expiry_date = new DateTime($license_data->expires);
						$result->message = sprintf( __( 'Your license has expired at the date: %s', 'cuar' ), $expiry_date->format( get_option('date_format') ) );
						break;

					case 'key_mismatch':
						$result->message = __( 'This license key does not match.', 'cuar' );
						break;

					case 'product_name_mismatch':
						$result->message = __( 'This license key seems to have been generated for another product.', 'cuar' );
						break;

					default:
						$result->message = __( 'Problem when validating your license key', 'cuar' );
						break;
								
				}				
			}
		
			$license_check_option_id = $addon->get_license_check_option_name();
			$cuar_plugin->update_option( $license_check_option_id, $today->format('Y-m-d') );

			$license_status_option_id = $addon->get_license_status_option_name();
			$cuar_plugin->update_option( $license_status_option_id, $result );
			
			echo json_encode( $result );
			exit;
		}
		
		/* ------------ FIELDS OUTPUT ----------------------------------------------------------------------------------- */
		
		/**
		 * Output a text field for a license key
		 *
		 * @param string $option_id        	
		 */
		public function print_license_key_field($args) {
			extract ( $args );

			$license_key = $this->options[$option_id];
			$last_status = $this->plugin->get_option( $status_option_id, null );
			
			if ( $last_status!=null && !empty( $license_key ) ) {
				$status_class = $last_status->success ? 'cuar-ajax-success' : 'cuar-ajax-failure';
				$status_message = $last_status->message;
			} else if ( empty( $license_key ) ) {
				$status_class = '';
				$status_message = '';				
				$this->plugin->update_option( $status_option_id, null );
				$this->plugin->update_option( $check_option_id, null );
			} else {
				$status_class = '';
				$status_message = '';
			}
			
			if (isset ( $before ))
				echo $before;
			
			echo sprintf ( '<input type="text" id="%s" name="%s[%s]" value="%s" class="regular-text" />', esc_attr( $option_id ), self::$OPTIONS_GROUP, esc_attr( $option_id ), esc_attr( $this->options[$option_id] ) );			
			echo sprintf ( '<span class="cuar-ajax-container"><span id="%s_check_result" class="%s">%s</span></span>', esc_attr( $option_id ), $status_class, $status_message );
			
			if (isset ( $after ))
				echo $after; 

			$last_check = $this->plugin->get_option( $check_option_id, null );
			if ( $last_check!=null ) {
				$next_check = new DateTime( $last_check );
				$next_check->add(new DateInterval('P10D'));
				
				$should_check_license = ( new DateTime('now') > $next_check );
			} else {
				$should_check_license = true;
			}
			
			if ( !empty( $license_key ) && ( $should_check_license || ( $last_status!=null && $last_status->success==false ) ) ) {
?>
				<script type="text/javascript">
				<!--
					jQuery(document).ready(function($) {
						$('#<?php echo $option_id . '_check_result'; ?>').html('<?php esc_attr_e( 'Checking license...', 'cuar' ); ?>').removeClass().addClass('cuar-ajax-running');
						
						var data = {
								action: 'cuar_validate_license',
								addon_id: '<?php echo $addon_id; ?>',
								license: '<?php echo $license_key; ?>',
								url: '<?php echo home_url(); ?>'
							};
						
						$.post(ajaxurl, data, function(response) {
								var panel = $('#<?php echo $option_id . '_check_result'; ?>');
								if ( response.success ) {
									panel.removeClass().addClass('cuar-ajax-success').html(response.message);
								} else {
									panel.removeClass().addClass('cuar-ajax-failure').html(response.message);
								}
							}, "json",
							function() {
								panel.removeClass().addClass('cuar-ajax-failure').html('<?php esc_attr_e( 'Failed to contact server', 'cuar' ); ?>');
							});
					});
				//-->
				</script>
<?php			
			}
		}
		
		/**
		 * Output a text field for a setting
		 *
		 * @param string $option_id        	
		 * @param string $type        	
		 * @param string $caption        	
		 */
		public function print_input_field($args) {
			extract ( $args );
			
			if ($type == 'checkbox') {
				if (isset ( $before ))
					echo $before;
				
				echo sprintf ( '<input type="%s" id="%s" name="%s[%s]" value="open" %s />&nbsp;', esc_attr ( $type ), esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ), ($this->options [$option_id] != 0) ? 'checked="checked" ' : '' );
				
				if (isset ( $after ))
					echo $after;
			} else if ($type == 'textarea') {
				if (isset ( $before ))
					echo $before;
				
				echo sprintf ( '<textarea id="%s" name="%s[%s]" class="large-text">%s</textarea>', esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ), $content );
				
				if (isset ( $after ))
					echo $after;
			} else if ($type == 'editor') {
				if (! isset ( $editor_settings ))
					$editor_settings = $this->plugin->get_default_wp_editor_settings();
				$editor_settings ['textarea_name'] = self::$OPTIONS_GROUP . "[" . $option_id . "]";
				
				wp_editor ( $this->options [$option_id], $option_id, $editor_settings );
			} else {
				$extra_class = isset ( $is_large ) && $is_large == true ? 'large-text' : 'regular-text';
				
				if (isset ( $before ))
					echo $before;
				
				echo sprintf ( '<input type="%s" id="%s" name="%s[%s]" value="%s" class="%s" />', esc_attr ( $type ), esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ), esc_attr ( $this->options [$option_id] ), esc_attr ( $extra_class ) );
				
				if (isset ( $after ))
					echo $after;
			}
		}


		/**
		 * Output a submit button
		 *
		 * @param string $option_id
		 * @param array $options
		 * @param string $caption
		 */
		public function print_submit_button($args) {
			extract ( $args );
				
			if (isset ( $before ))
				echo $before;
				
			echo sprintf ( '<input type="submit" name="%s" id="%s[%s]" value="%s" class="button button-primary">', 
						esc_attr( $option_id ), 
						self::$OPTIONS_GROUP, esc_attr( $option_id ),
						$label
					);

			wp_nonce_field( $nonce_action, $nonce_name );

			if (isset ( $after ))
				echo $after;
		}
		
		/**
		 * Output a select field for a setting
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_select_field($args) {
			extract ( $args );
			
			if (isset ( $before ))
				echo $before;
			
			echo sprintf ( '<select id="%s" name="%s[%s]">', esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ) );
			
			foreach ( $options as $value => $label ) {
				$selected = ($this->options [$option_id] == $value) ? 'selected="selected"' : '';
				
				echo sprintf ( '<option value="%s" %s>%s</option>', esc_attr ( $value ), $selected, $label );
			}
			
			echo '</select>';
			
			if (isset ( $after ))
				echo $after;
		}
		
		/**
		 * Output a select field for a setting
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_post_select_field($args) {
			extract ( $args );

			$query_args = array(
					'post_type' 		=> $post_type,
					'posts_per_page' 	=> -1,
					'orderby' 			=> 'title',
					'order' 			=> 'ASC'
				);
			$pages_query = new WP_Query( $query_args );
			
			if (isset ( $before ))
				echo $before;
			
			echo sprintf ( '<select id="%s" name="%s[%s]" class="cuar-post-select">', esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ) );

			$value = -1;
			$label = __( 'None', 'cuar' );
			$selected = ( isset( $this->options[$option_id] ) && $this->options[$option_id] == $value ) ? 'selected="selected"' : '';
			echo sprintf ( '<option value="%s" %s>%s</option>', esc_attr ( $value ), $selected, $label );
			
			while ( $pages_query->have_posts() ) {
				$pages_query->the_post();				
				$value = get_the_ID();
				$label = get_the_title();
				
				$selected = ( isset( $this->options[$option_id] ) && $this->options[$option_id] == $value ) ? 'selected="selected"' : '';
				
				echo sprintf ( '<option value="%s" %s>%s</option>', esc_attr ( $value ), $selected, $label );
			}
			
			echo '</select>';
			
			if ( isset( $this->options[$option_id] ) && $this->options[$option_id]>0 ) {
				if ( $show_create_button ) {
					printf( '<input type="submit" value="%1$s" id="%2$s" name="%2$s" class="cuar-submit-create-post"/>',
							esc_attr__( 'Delete existing &amp; create new &raquo;', 'cuar' ),
							esc_attr( $this->get_submit_create_post_button_name( $option_id ) )
						);
				}
				
				edit_post_link( __('Edit it &raquo;', 'cuar'), '<span class="cuar-edit-page-link">', '</span>', $this->options[$option_id] );
			} else {
				if ( $show_create_button ) {
					printf( '<input type="submit" value="%1$s" id="%2$s" name="%2$s" class="cuar-submit-create-post"/>', 
							esc_attr__( 'Create it &raquo;', 'cuar' ),
							esc_attr( $this->get_submit_create_post_button_name( $option_id ) )
						); 
				}
			}

			wp_reset_postdata();
			
			if (isset ( $after ))
				echo $after;
		}
		
		public function get_submit_create_post_button_name( $option_id ) {
			return 'submit_' . $option_id . '_create';
		}
		
		/**
		 * Output a select field for a setting
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_owner_type_select_field($args) {
			extract ( $args );
			
			if (isset ( $before ))
				echo $before;
			
			$po_addon = $this->plugin->get_addon ( "post-owner" );
			$po_addon->print_owner_type_select_field ( $option_id, self::$OPTIONS_GROUP, $this->options [$option_id] );
			
			if (isset ( $after ))
				echo $after;
		}
		
		/**
		 * Output a select field for a setting
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_owner_select_field($args) {
			extract ( $args );
			
			if (isset ( $before ))
				echo $before;
			
			$po_addon = $this->plugin->get_addon ( "post-owner" );
			$po_addon->print_owner_select_field ( $owner_type_option_id, $option_id, self::$OPTIONS_GROUP, $this->options [$owner_type_option_id], $this->options [$option_id] );
			$po_addon->print_owner_select_javascript ( $owner_type_option_id, $option_id );
			
			if (isset ( $after ))
				echo $after;
		}
		
		/**
		 * Output a select field for a setting
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_role_select_field($args) {
			extract ( $args );
			
			if (isset ( $before ))
				echo $before;
			
			echo sprintf ( '<select id="%s" name="%s[%s]">', esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ) );
			
			global $wp_roles;
			if ( !isset( $wp_roles ) ) $wp_roles = new WP_Roles();
			$all_roles = $wp_roles->role_objects;
			
			if ($show_any_option) {
				$value = 'cuar_any';
				$label = __ ( 'Any Role', 'cuar' );
				$selected = ($this->options [$option_id] == $value) ? 'selected="selected"' : '';
				
				echo sprintf ( '<option value="%s" %s>%s</option>', esc_attr ( $value ), $selected, $label );
			}
			
			foreach ( $all_roles as $role ) {
				$value = $role->name;
				$label = CUAR_WordPressHelper::getRoleDisplayName ( $role->name );
				$selected = ($this->options [$option_id] == $value) ? 'selected="selected"' : '';
				
				echo sprintf ( '<option value="%s" %s>%s</option>', esc_attr ( $value ), $selected, $label );
			}
			
			echo '</select>';
			
			if (isset ( $after ))
				echo $after;
		}
		
		/**
		 * Output a select field for a term
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_term_select_field($args) {
			extract ( $args );
			
			if (isset ( $before ))
				echo $before;
			
			wp_dropdown_categories( array(
					'taxonomy'			=> $taxonomy,
					'name' 				=> self::$OPTIONS_GROUP . '[' . esc_attr ( $option_id ) . ']',
					'id' 				=> esc_attr ( $option_id ),
					'selected'			=> $this->options [$option_id],
					'hide_empty'    	=> 0,
					'hierarchical'  	=> 1,
					'show_option_none' 	=> __('None', 'cuar'),
					'orderby'       	=> 'NAME',
				) );
			
			if (isset ( $after ))
				echo $after;
		}
		
		/**
		 * Output a select field for a theme
		 *
		 * @param string $option_id        	
		 * @param array $options        	
		 * @param string $caption        	
		 */
		public function print_theme_select_field($args) {
			extract ( $args );
			
			if (isset ( $before ))
				echo $before;
			
			echo sprintf ( '<select id="%s" name="%s[%s]">', esc_attr ( $option_id ), self::$OPTIONS_GROUP, esc_attr ( $option_id ) );
			
			$theme_locations = apply_filters ( 'cuar_theme_locations', array (
					array (
							'base'	=> 'plugin',
							'type'	=> $theme_type,
							'dir' 	=> CUAR_PLUGIN_DIR . '/themes/' . $theme_type,
							'label' => __ ( 'Main plugin folder', 'cuar' ) 
						),
					array (
							'base'	=> 'user-theme',
							'type'	=> $theme_type,
							'dir' 	=> untrailingslashit(get_stylesheet_directory()) . '/customer-area/themes/' . $theme_type,
							'label' => __ ( 'Current theme folder', 'cuar' ) 
						),
					array (
							'base'	=> 'wp-content',
							'type'	=> $theme_type,
							'dir' 	=> untrailingslashit(WP_CONTENT_DIR) . '/customer-area/themes/' . $theme_type,
							'label' => __ ( 'WordPress content folder', 'cuar' ) 
						) 
				) );
			
			foreach ( $theme_locations as $theme_location ) {
				$subfolders = array_filter ( glob ( $theme_location ['dir'] . '/*' ), 'is_dir' );
				
				foreach ( $subfolders as $s ) {
					$theme_name = basename( $s );
					$label = $theme_location['label'] . ' - ' . $theme_name;
					$value = esc_attr ( $theme_location['base'] . '%%' . $theme_name );
					$selected = ($this->options[$option_id] == $value || $this->options[$option_id] == $theme_location['url'] ) ? 'selected="selected"' : '';
					
					echo sprintf ( '<option value="%s" %s>%s</option>', esc_attr ( $value ), $selected, $label );
				}
			}
			
			echo '</select>';
			
			if (isset ( $after ))
				echo $after;
		}
		
		/* ------------ OTHER FUNCTIONS --------------------------------------------------------------------------------- */
		
		/**
		 * Prints a sidebox on the side of the settings screen
		 *
		 * @param string $title        	
		 * @param string $content        	
		 */
		public function print_sidebox($title, $content) {
			echo '<div class="cuar-sidebox">';
			echo '<h2 class="cuar-sidebox-title">' . $title . '</h2>';
			echo '<div class="cuar-sidebox-content">' . $content . '</div>';
			echo '</div>';
		}
		
		/**
		 * Update an option and persist to DB if asked to
		 *
		 * @param string $option_id        	
		 * @param mixed $new_value        	
		 * @param boolean $commit        	
		 */
		public function update_option($option_id, $new_value, $commit = true) {
			$this->options [$option_id] = $new_value;
			if ($commit) $this->save_options ();
		}
		
		/**
		 * Persist the current plugin options to DB
		 */
		public function save_options() {
			update_option( CUAR_Settings::$OPTIONS_GROUP, $this->options );
		}
	
		public function reset_defaults() {
			$this->options = array();
			$this->save_options();
			$this->reload_options();
		}
		
		/**
		 * Persist the current plugin options to DB
		 */
		public function get_options() {
			return $this->options;
		}
		
		/**
		 * Persist the current plugin options to DB
		 */
		public function get_default_options() {
			return $this->default_options;
		}
		
		/**
		 * Load the options (and defaults if the options do not exist yet
		 */
		private function reload_options() {
			$current_options = get_option ( CUAR_Settings::$OPTIONS_GROUP );
			
			$this->default_options = apply_filters ( 'cuar_default_options', array () );
			
			if (! is_array ( $current_options ))
				$current_options = array ();
			$this->options = array_merge ( $this->default_options, $current_options );
			
			do_action ( 'cuar_options_loaded', $this->options );
		}
		public static $OPTIONS_PAGE_SLUG = 'cuar-settings';
		public static $OPTIONS_GROUP = 'cuar_options';
		
		// Core options
		public static $OPTION_CURRENT_VERSION = 'cuar_current_version';
		public static $OPTION_INCLUDE_CSS = 'cuar_include_css';
		public static $OPTION_ADMIN_THEME = 'cuar_admin_theme_url';
		public static $OPTION_FRONTEND_THEME = 'cuar_frontend_theme_url';
		public static $OPTION_HIDE_SINGLE_OWNER_SELECT = 'cuar_hide_single_owner_select';
		
		/**
		 * @var CUAR_Plugin The plugin instance
		 */
		private $plugin;
		
		/**
		 * @var array
		 */
		private $default_options;
		
		/**
		 * @var array
		 */
		private $tabs;
		
		/**
		 * @var string
		 */
		private $current_tab;
	}
	
	// This filter needs to be executed too early to be registered in the constructor
	add_filter ( 'cuar_default_options', array (
			'CUAR_Settings',
			'set_default_core_options' 
	) );


endif; // if (!class_exists('CUAR_Settings')) :

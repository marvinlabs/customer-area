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

if (!class_exists('CUAR_AbstractCreateContentPageAddOn')) :

/**
 * The base class for addons that should render a page to create private content
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AbstractCreateContentPageAddOn extends CUAR_AbstractPageAddOn {

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

	/*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/
	
	public function get_friendly_post_type() {
		return $this->page_description['friendly_post_type'];
	}
	
	public function get_friendly_taxonomy() {
		return $this->page_description['friendly_taxonomy'];
	}	
	
	public function handle_form_submission() {		
		if ( get_queried_object_id()!=$this->get_page_id() ) return false;
        echo 'hiha';	
		if ( !isset( $_POST['cuar_form_id'] ) || $_POST['cuar_form_id']!=$this->get_slug() ) return false;

        if ( !wp_verify_nonce( $_POST["cuar_" . $this->get_slug() . "_nonce"], 'cuar_' . $this->get_slug() ) ) {
        	die('An attempt to bypass security checks was detected! Please go back and try again.');
        }
        
		// If not logged-in, bail
		if ( !is_user_logged_in() ) return false;
        
        return true;	
	}
	
	public function should_print_form() {
		if ( !empty( $this->form_messages ) ) {
			foreach ( $this->form_messages as $msg ) {
				printf( '<p class="alert alert-success">%s</p>', $msg );
			}
		}
		
		return $this->should_print_form;
	}

	public function print_form_header() {
		printf( '<form name="%1$s" method="post" class="cuar-form cuar-%1$s-form" action="%2$s">', $this->get_slug(), $this->get_page_url() );

		printf( '<input type="hidden" name="cuar_form_id" value="%1$s" />', $this->get_slug() );
		
		wp_nonce_field( 'cuar_' . $this->get_slug(), 'cuar_' . $this->get_slug() . '_nonce' );
	
		if ( !empty( $this->form_errors ) ) {
			foreach ( $this->form_errors as $error ) {
				if ( is_wp_error( $error ) ) {
					printf( '<p class="alert alert-warning">%s</p>', $error->get_error_message() );
				} else if ( $error!==false && !empty( $error ) && !is_array( $error ) ) {
					printf( '<p class="alert alert-info">%s</p>', $error );
				}
			}
		}
	}
	
	public function print_form_footer() {
		echo '</form>';
	}
		
	public function print_page_content( $args = array(), $shortcode_content = '' ) {
		$this->print_form_header(); 		
		parent::print_page_content( $args, $shortcode_content );
		$this->print_form_footer();
	}

	protected $should_print_form = true;
	protected $form_errors = array();	
	protected $form_messages = array();
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function is_rich_editor_enabled() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_ENABLE_RICH_EDITOR, true );
	}
	
	public function get_default_owner_type() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_DEFAULT_OWNER_TYPE, 'usr' );
	}
	
	public function get_default_owner() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_DEFAULT_OWNER, array( '1' ) );
	}
	
	/**
	 * Set the default values for the options
	 *
	 * @param array $defaults
	 * @return array
	 */
	public function set_default_options( $defaults ) {
		$defaults = parent::set_default_options($defaults);
		
		$slug = $this->get_slug();
		
		$defaults[ $slug . self::$OPTION_ENABLE_RICH_EDITOR ] 	= true;
		$defaults[ $slug . self::$OPTION_DEFAULT_OWNER_TYPE ] 	= 'usr';
		$defaults[ $slug . self::$OPTION_DEFAULT_OWNER ] 		= array( '1' );
			
		return $defaults;
	}
	
	/*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

	public function enable_settings( $target_tab, $enabled_settings = array( 'rich-editor', 'default-ownership' ) ) {
		$this->enabled_settings = $enabled_settings;
		
		if ( is_admin() && !empty( $this->enabled_settings ) ) {
			// Settings	
			add_action( 'cuar_addon_print_settings_' . $target_tab, array( &$this, 'print_settings' ), 10, 2 );
			add_filter( 'cuar_addon_validate_options_' . $target_tab, array( &$this, 'validate_options' ), 10, 3 );
		}
	}
	
	protected function get_settings_section() {
		return $this->get_slug() . '_content_creation_frontend';
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
				__('Content creation', 'cuar'),
				array( &$this, 'print_empty_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		if ( in_array('rich-editor', $this->enabled_settings ) ) {
			add_settings_field(
					$slug . self::$OPTION_ENABLE_RICH_EDITOR,
					__('Rich Editor', 'cuar'),
					array( &$cuar_settings, 'print_input_field' ),
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array(
							'option_id' 	=> $slug . self::$OPTION_ENABLE_RICH_EDITOR,
							'type' 			=> 'checkbox',
							'default_value' => 1,
							'after'			=> __('Enable the rich editor when creating new content.', 'cuarme')
						)
				);
		}

		if ( in_array('default-ownership', $this->enabled_settings ) ) {	
			add_settings_field(
					$slug . self::$OPTION_DEFAULT_OWNER_TYPE, 
					__('Default owner type', 'cuarme'),
					array( &$cuar_settings, 'print_owner_type_select_field' ), 
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array( 
						'option_id' => $slug . self::$OPTION_DEFAULT_OWNER_TYPE, 
		    			'after'		=> '' )
				);
	
			add_settings_field(
					$slug . self::$OPTION_DEFAULT_OWNER, 
					__('Default owner', 'cuarme'),
					array( &$cuar_settings, 'print_owner_select_field' ), 
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array( 
						'option_id' 			=> $slug . self::$OPTION_DEFAULT_OWNER, 
						'owner_type_option_id' 	=> $slug . self::$OPTION_DEFAULT_OWNER_TYPE, 
		    			'after'					=> '' )
				);			
		}
		
		$this->print_additional_settings( $cuar_settings, $options_group );
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
		
		$cuar_settings->validate_boolean( $input, $validated, $slug . self::$OPTION_ENABLE_RICH_EDITOR );
		$cuar_settings->validate_owner_type( $input, $validated, $slug . self::$OPTION_DEFAULT_OWNER_TYPE );
		$cuar_settings->validate_owner( $input, $validated, $slug . self::$OPTION_DEFAULT_OWNER, $slug . self::$OPTION_DEFAULT_OWNER_TYPE );
		
		$this->validate_additional_settings( $validated, $cuar_settings, $input );
		
		return $validated;
	}
	
	protected function print_additional_settings( $cuar_settings, $options_group ) {		
	}
	
	protected function validate_additional_settings( &$validated, $cuar_settings, $input ) {		
	}
	
	public function print_empty_section_info() {
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );

		if ( !is_admin() ) {
			add_action( 'template_redirect', array( &$this, 'handle_form_submission' ) );
		} else {
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );			
		}
	}

	/**
	 * Enqueues the select script on the user-edit and profile screens.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
	
		if ( isset( $screen->id ) && $screen->id=='customer-area_page_cuar-settings' ) {
			$this->plugin->enable_library('jquery.select2');
		}
	}

	// Settings
	public static $OPTION_DEFAULT_OWNER_TYPE	= '-default_owner_type';
	public static $OPTION_DEFAULT_OWNER			= '-default_owner';
	public static $OPTION_ENABLE_RICH_EDITOR	= '-enable_rich_editor';
	
	protected $enabled_settings = array();
}

endif; // CUAR_AbstractCreateContentPageAddOn
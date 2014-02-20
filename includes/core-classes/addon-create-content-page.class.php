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

	public function __construct( $addon_id = null, $min_cuar_version = null ) {
		parent::__construct( $addon_id, $min_cuar_version );
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
		
		if ( !isset( $_POST['cuar_form_id'] ) || $_POST['cuar_form_id']!=$this->get_slug() ) return false;

        if ( !wp_verify_nonce( $_POST["cuar_" . $this->get_slug() . "_nonce"], 'cuar_' . $this->get_slug() ) ) {
        	die('An attempt to bypass security checks was detected! Please go back and try again.');
        }
        
		// If not logged-in, bail
		if ( !is_user_logged_in() ) return false;

        if ( !$this->is_accessible_to_current_user() ) {
        	die('You are not allowed to view this page.');
        }

        if ( !$this->current_user_can_create_content() ) {
        	die('You are not allowed to create content.');
        }
        
		$result = $this->do_create_content( $_POST );		
		if ( true===$result ) {
			$redirect_url = apply_filters( 'cuar_redirect_url_after_content_creation', $this->get_redirect_slug_after_creation(), $this->get_slug() );
			if ( $redirect_url!=null ) {
				wp_redirect( $redirect_url );
				exit;
			}
		}
		
        return true;	
	}
	
	protected function do_create_content( $form_data ) {		
	}
	
	protected function check_submitted_title( $form_data, $error_message ) {
		if ( isset( $form_data['cuar_title'] ) && !empty( $form_data['cuar_title'] ) ) {
			return $form_data['cuar_title'];
		}

		$this->form_errors[] = new WP_Error( 'missing_title', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_content( $form_data, $error_message ) {
		if ( isset( $form_data['cuar_content'] ) && !empty( $form_data['cuar_content'] ) ) {
			return $form_data['cuar_content'];
		}

		$this->form_errors[] = new WP_Error( 'missing_content', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_category( $form_data, $error_message ) {
		if ( isset( $form_data['cuar_category'] ) && !empty( $form_data['cuar_category'] ) ) {
			return $form_data['cuar_category'];
		}

		$this->form_errors[] = new WP_Error( 'missing_category', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_file( $form_data, $error_message ) {
		if ( isset( $_FILES ) && isset( $_FILES['cuar_file'] ) && !empty( $_FILES['cuar_file']['name'] ) ) {
			return $_FILES['cuar_file'];
		}

		$this->form_errors[] = new WP_Error( 'missing_file', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_owner( $form_data, $error_message ) {
		$po_addon = $this->plugin->get_addon('post-owner');
		$new_owner = $po_addon->get_owner_from_post_data();
			
		if ( $new_owner!=null ) {
			return $new_owner;
		}
		
		$this->form_errors[] = new WP_Error( 'missing_owner', $error_message );
		return FALSE;
	}
	
	protected function get_default_publish_status() {
		$post_status = 'publish';
		if ( $this->is_moderation_enabled() && !$this->current_user_can_bypass_moderation() ) {
			$post_status = 'draft';
		}		
		return $post_status;
	}
	
	protected function get_redirect_slug_after_creation() {		
		$cp_addon = $this->plugin->get_addon('customer-pages');
		$page_id = $cp_addon->get_page_id( $this->get_parent_slug() );
		return get_permalink( $page_id );
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
		printf( '<form name="%1$s" method="post" class="cuar-form cuar-create-content-form cuar-%1$s-form" action="%2$s" enctype="multipart/form-data">', $this->get_slug(), $this->get_page_url() );

		printf( '<input type="hidden" name="cuar_form_id" value="%1$s" />', $this->get_slug() );
		printf( '<input type="hidden" name="cuar_post_type" value="%1$s" />', $this->get_friendly_post_type() );
		
		if ( !isset( $_POST['cuar_post_type'] ) ) {
			$_POST['cuar_post_type'] = $this->get_friendly_post_type();
		} 
		
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

	public function print_submit_button( $label ) {
		echo '<div class="form-group">';
		echo '	<div class="submit-container">';
		echo '		<input type="submit" name="cuar_do_register" value="' . esc_attr( $label ) . '" class="btn btn-default" />';
		echo '	</div>';
		echo '</div>';
	}

	public function print_title_field( $label ) {
		$title = isset( $_POST['cuar_title'] ) ? $_POST['cuar_title'] : '';
		$field_code = sprintf( '<input type="text" id="cuar_title" name="cuar_title" value="%1$s" class="form-control" />', esc_attr( $title ) );		
		$this->print_form_field( 'cuar_title', $label, $field_code );
	}

	public function print_file_field( $label ) {
		$field_code = '<input type="file" id="cuar_file" name="cuar_file" class="form-control" />';		
		$this->print_form_field( 'cuar_file', $label, $field_code );
	}

	public function print_content_field( $label ) {
		$content = isset( $_POST['cuar_content'] ) ? $_POST['cuar_content'] : '';
		
		if ( !$this->is_rich_editor_enabled() ) {
			$field_code = sprintf( '<textarea rows="5" cols="40" name="cuar_content" id="cuar_content" class="form-control">%1$s</textarea>', esc_attr( $content ) );
		} else {
			ob_start();
			wp_editor( $content, 'cuar_content', $this->plugin->get_default_wp_editor_settings() );

			$field_code = ob_get_contents();
			ob_end_clean();
		}	
		
		$this->print_form_field( 'cuar_content', $label, $field_code );
	}

	public function print_owner_field( $label ) {
		if ( $this->current_user_can_select_owner() ) {
			$po_addon = $this->plugin->get_addon('post-owner');
			
			$owner = $po_addon->get_owner_from_post_data();
			$owner_type = $owner==null ? 'usr' : $owner['type'];
			$owner_ids = $owner==null ? array() : $owner['ids'];
		
			ob_start();
			$po_addon->print_owner_type_select_field( 'cuar_owner_type', null, $owner_type );
			$po_addon->print_owner_select_field( 'cuar_owner_type', 'cuar_owner', null, $owner_type, $owner_ids );
			$po_addon->print_owner_select_javascript( 'cuar_owner_type', 'cuar_owner' );
			$field_code = ob_get_contents();
			ob_end_clean();
		
			$this->print_form_field( 'cuar_owner', $label, $field_code );
		} else {
			$owner_type = $this->get_default_owner_type();
			$owner_ids = $this->get_default_owner();
			
			$field_code = sprintf( '<input type="hidden" name="cuar_owner_type" value="%1$s" />', esc_attr( $owner_type ) );
			
			foreach ( $owner_ids as $id ) {
				$field_code .= sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', 
									'cuar_owner_' . $owner_type . '_id[]',
									esc_attr( $id ) );
			}
			
			echo $field_code;
		}
	}

	public function print_category_field( $label ) {		
		$categories = get_terms( $this->get_friendly_taxonomy() );
		if ( empty( $categories ) )	{	
			$field_code = '<input type="hidden" name="cuar_category" value="-1" />';			
			echo $field_code;
		} else if ( $this->current_user_can_select_category() ) {
			$category = isset( $_POST['cuar_category'] ) ? $_POST['cuar_category'] : '';
			
			$field_code = wp_dropdown_categories( array( 
							'taxonomy'		=> $this->get_friendly_taxonomy(),
							'name' 			=> 'cuar_category',
							'hide_empty'    => 0,
							'hierarchical'  => 1,
							'selected'		=> $category,
							'orderby'       => 'NAME',
							'echo'			=> false,
							'class'         => 'form-control',
						) );
		
			$this->print_form_field( 'cuar_category', $label, $field_code );
		} else {
			$category = $this->get_default_category();			
			$field_code = sprintf( '<input type="hidden" name="cuar_category" value="%1$s" />', esc_attr( $category ) );			
			echo $field_code;
		}
	}

	public function print_form_field( $name, $label, $field_code, $help_text='' ) {
		echo '<div class="form-group">';
		echo '	<label for="' . $name . '" class="control-label">' . $label . '</label>';
		echo '	<div class="control-container">';
		echo $field_code;
		
		if ( !empty( $help_text ) ) {
			echo '		<span class="help-block">' . $help_text . '</span>';
		}
		
		echo '	</div>';
		echo '</div>';
	}
	
	protected $should_print_form = true;
	protected $form_errors = array();	
	protected $form_messages = array();
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function is_rich_editor_enabled() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_ENABLE_RICH_EDITOR, true );
	}
	
	public function is_moderation_enabled() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_ENABLE_MODERATION, false );
	}
	
	public function get_default_owner_type() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_DEFAULT_OWNER_TYPE, 'usr' );
	}
	
	public function get_default_owner() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_DEFAULT_OWNER, array( '1' ) );
	}
	
	public function get_default_category() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_DEFAULT_CATEGORY, -1 );
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

		$defaults[ $slug . self::$OPTION_ENABLE_MODERATION ] 	= false;
		$defaults[ $slug . self::$OPTION_ENABLE_RICH_EDITOR ] 	= true;
		$defaults[ $slug . self::$OPTION_DEFAULT_OWNER_TYPE ] 	= 'usr';
		$defaults[ $slug . self::$OPTION_DEFAULT_OWNER ] 		= array( '1' );
		$defaults[ $slug . self::$OPTION_DEFAULT_CATEGORY ]		= -1;
			
		return $defaults;
	}
	
	/*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

	public function get_configurable_capability_groups( $capability_groups ) {
		$post_type = $this->get_friendly_post_type();

		if ( isset( $capability_groups[$post_type] ) ) {
			$capability_groups[$post_type]['groups']['create-content'] = array(
					'group_name' 	=> __( 'Content creation (from front-office)', 'cuar' ),
					'capabilities' 	=> array(
							$post_type . '_create_content'				=> __( 'Create content from front office', 'cuar' ),
							$post_type . '_create_select_owner'			=> __( 'Select an owner (uses default else)', 'cuar' ),
							$post_type . '_create_select_category'		=> __( 'Select a category (uses default else)', 'cuar' ),
							$post_type . '_create_bypass_moderation' 	=> __( 'Bypass moderation (content is automatically published)', 'cuar' )
						)
			);
		}
		
		return $capability_groups;
	}
	
	public function current_user_can_select_category() {
		$post_type = $this->get_friendly_post_type();
		return current_user_can( $post_type . '_create_select_category' );
	}
	
	public function current_user_can_select_owner() {
		$post_type = $this->get_friendly_post_type();
		return current_user_can( $post_type . '_create_select_owner' );
	}
	
	public function current_user_can_create_content() {
		$post_type = $this->get_friendly_post_type();
		return current_user_can( $post_type . '_create_content' );
	}
	
	public function current_user_can_bypass_moderation() {
		$post_type = $this->get_friendly_post_type();
		return current_user_can( $post_type . '_create_bypass_moderation' );
	}
	
	public function is_accessible_to_current_user() {
		return $this->current_user_can_create_content();
	}
	
	/*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

	public function enable_settings( $target_tab, $enabled_settings = array( 'rich-editor', 'default-ownership', 'default-category', 'moderation' ) ) {
		$this->enabled_settings = $enabled_settings;
		
		if ( is_admin() && !empty( $this->enabled_settings ) ) {
			// Settings	
			add_action( 'cuar_addon_print_settings_' . $target_tab, array( &$this, 'print_settings' ), 20, 2 );
			add_filter( 'cuar_addon_validate_options_' . $target_tab, array( &$this, 'validate_options' ), 20, 3 );
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
							'after'			=> __('Enable the rich editor when creating new content.', 'cuar')
						)
				);
		}

		if ( in_array('moderation', $this->enabled_settings ) ) {
			add_settings_field(
					$slug . self::$OPTION_ENABLE_MODERATION,
					__('Moderation', 'cuar'),
					array( &$cuar_settings, 'print_input_field' ),
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array(
							'option_id' 	=> $slug . self::$OPTION_ENABLE_MODERATION,
							'type' 			=> 'checkbox',
							'default_value' => 1,
							'after'			=> __('Enable moderation when new content is submitted by a user.', 'cuar')
					    					. '<p class="description">'
						    				. __( 'An administrator will be required to review the content and publish it '
						    						. 'manually. This can be used to moderate the content created by users by saving it as draft. '
						    						. 'When content is saved as draft, it is not visible to anyone outside of the administration area. '
						    						. 'You can allow some roles to bypass the moderation process by setting the corresponding capability. '
						    						. 'This setting does not affect the backend interface.', 'cuar' )
						    				. '</p>' 
						)
				);
		}

		if ( in_array('default-ownership', $this->enabled_settings ) ) {	
			add_settings_field(
					$slug . self::$OPTION_DEFAULT_OWNER_TYPE, 
					__('Default owner type', 'cuar'),
					array( &$cuar_settings, 'print_owner_type_select_field' ), 
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array( 
						'option_id' => $slug . self::$OPTION_DEFAULT_OWNER_TYPE, 
		    			'after'		=> '' )
				);
	
			add_settings_field(
					$slug . self::$OPTION_DEFAULT_OWNER, 
					__('Default owner', 'cuar'),
					array( &$cuar_settings, 'print_owner_select_field' ), 
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array( 
						'option_id' 			=> $slug . self::$OPTION_DEFAULT_OWNER, 
						'owner_type_option_id' 	=> $slug . self::$OPTION_DEFAULT_OWNER_TYPE, 
		    			'after'					=> '' )
				);			
		}
		
		$tax = $this->get_friendly_taxonomy();
		if ( in_array('default-category', $this->enabled_settings ) && !empty( $tax ) ) {
			add_settings_field(
					$slug . self::$OPTION_DEFAULT_CATEGORY, 
					__('Default category', 'cuar'),
					array( &$cuar_settings, 'print_term_select_field' ), 
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					$this->get_settings_section(),
					array( 
						'option_id' 		=> $slug . self::$OPTION_DEFAULT_CATEGORY, 
						'taxonomy' 			=> $tax, 
		    			'after'				=> '' )
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

		if ( in_array('moderation', $this->enabled_settings ) ) {
			$cuar_settings->validate_boolean( $input, $validated, $slug . self::$OPTION_ENABLE_MODERATION );
		}

		if ( in_array('rich-editor', $this->enabled_settings ) ) {
			$cuar_settings->validate_boolean( $input, $validated, $slug . self::$OPTION_ENABLE_RICH_EDITOR );
		}
		
		if ( in_array('default-ownership', $this->enabled_settings ) ) {
			$cuar_settings->validate_owner_type( $input, $validated, $slug . self::$OPTION_DEFAULT_OWNER_TYPE );
			$cuar_settings->validate_owner( $input, $validated, $slug . self::$OPTION_DEFAULT_OWNER, $slug . self::$OPTION_DEFAULT_OWNER_TYPE );
		}

		$tax = $this->get_friendly_taxonomy();
		if ( in_array('default-category', $this->enabled_settings ) && !empty( $tax ) ) {
			$cuar_settings->validate_term( $input, $validated, $slug . self::$OPTION_DEFAULT_CATEGORY, $tax );
		}
		
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
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );	
		} else {
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );		
			add_filter( 'cuar_configurable_capability_groups', array( &$this, 'get_configurable_capability_groups' ), 1000 );	
		}
	}

	/**
	 * Enqueues the select script on the user-edit and profile screens.
	 */
	public function enqueue_scripts() {	
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( isset( $screen->id ) && $screen->id=='customer-area_page_cuar-settings' ) {
				$this->plugin->enable_library('jquery.select2');
			}
		} else {
			$this->plugin->enable_library('jquery.select2');
		}
	}

	// Settings
	public static $OPTION_ENABLE_MODERATION		= '-enable_moderation';
	public static $OPTION_DEFAULT_OWNER_TYPE	= '-default_owner_type';
	public static $OPTION_DEFAULT_OWNER			= '-default_owner';
	public static $OPTION_ENABLE_RICH_EDITOR	= '-enable_rich_editor';
	public static $OPTION_DEFAULT_CATEGORY		= '-default_category';
	
	protected $enabled_settings = array();
}

endif; // CUAR_AbstractCreateContentPageAddOn
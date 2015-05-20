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

if (!class_exists('CUAR_AbstractEditContentPageAddOn')) :

/**
 * The base class for addons that should render a page to edit (create or update) private content
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AbstractEditContentPageAddOn extends CUAR_AbstractPageAddOn {

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
	
	/**
	 * The action done by this page (e.g. create or update)
	 */
	protected abstract function get_action();
	
	public function get_type() {
		return $this->get_action() . '-content';
	}

	public function run_addon( $plugin ) {
		parent::run_addon( $plugin );
		
		if ( !is_admin() ) {
			add_action( 'template_redirect', array( &$this, 'handle_form_submission' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		} else {
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			add_filter( 'cuar/core/permission-groups', array( &$this, 'get_configurable_capability_groups' ), 1000 );
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

        $action = $this->get_action();
        if ( get_query_var( 'cuar_action', null )!=null ) {
        	$action = get_query_var( 'cuar_action', null );
        } 
        
        if ( !$this->is_action_authorized( $action ) ) {
        	return false;
        } 
		
        do_action( 'cuar/private-content/edit/before_' . $action, $this, $this->form_errors );
        do_action( 'cuar/private-content/edit/before_' . $action . '/page-slug=' . $this->get_slug(), $this, $this->form_errors );
        
		$result = $this->do_edit_content( $action, $_POST );		

		do_action( 'cuar/private-content/edit/after_' . $action, $this, $this->form_errors );
		do_action( 'cuar/private-content/edit/after_' . $action . '/page-slug=' . $this->get_slug(), $this, $this->form_errors );
		
		if ( true===$result && empty( $this->form_errors ) ) {		
			$redirect_url = apply_filters( 'cuar/private-content/edit/after_' . $action . '/redirect_url', $this->get_redirect_url_after_action(), $this->get_slug() );
			if ( $redirect_url!=null ) {
				wp_redirect( $redirect_url );
				exit;
			}
		}
		
        return true;	
	}
	
	protected abstract function is_action_authorized( $action );
	
	/**
	 * The slug to redirect to after the form has been submitted
	 */
	protected abstract function get_redirect_url_after_action();
	
	protected function do_edit_content( $action, $form_data ) {		
		return false;
	}
	
	protected function get_required_fields() {
		if ( $this->required_fields==null ) {
			$this->required_fields = apply_filters(
					'cuar/private-content/edit/required-fields?post_type=' . $this->get_friendly_post_type(), 
					$this->get_default_required_fields() );
		}
		return $this->required_fields;
	}
	
	protected function get_default_required_fields() {
		return array();
	}
	
	protected function is_field_required( $id ) {
		$rf = $this->get_required_fields();
		return in_array( $id, $rf );
	}
	
	protected $required_fields = null;
	
	protected function check_submitted_title( $form_data, $error_message ) {
		if ( isset( $form_data['cuar_title'] ) && !empty( $form_data['cuar_title'] ) ) {
			return $form_data['cuar_title'];
		}

		if ( !$this->is_field_required( 'cuar_title' ) ) {
			return '';
		}
		
		$this->form_errors[] = new WP_Error( 'missing_title', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_content( $form_data, $error_message ) {
		if ( isset( $form_data['cuar_content'] ) && !empty( $form_data['cuar_content'] ) ) {
			return $form_data['cuar_content'];
		}

		if ( !$this->is_field_required( 'cuar_content' ) ) {
			return '';
		}

		$this->form_errors[] = new WP_Error( 'missing_content', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_category( $form_data, $error_message ) {
		if ( isset( $form_data['cuar_category'] ) && !empty( $form_data['cuar_category'] ) ) {
			return $form_data['cuar_category'];
		}

		if ( !$this->is_field_required( 'cuar_category' ) ) {
			return 0;
		}

		$this->form_errors[] = new WP_Error( 'missing_category', $error_message );
		return FALSE;
	}
	
	protected function check_submitted_file( $form_data, $error_message ) {
		if ( isset( $_FILES ) && isset( $_FILES['cuar_file'] ) && !empty( $_FILES['cuar_file']['name'] ) ) {
			return $_FILES['cuar_file'];
		}

		if ( !$this->is_field_required( 'cuar_file' ) ) {
			return null;
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

		if ( !$this->is_field_required( 'cuar_owner' ) ) {
			return null;
		}
		
		$this->form_errors[] = new WP_Error( 'missing_owner', $error_message );
		return FALSE;
	}
	
	protected function get_default_publish_status() {
		return 'publish';
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
		printf( '<form name="%1$s" method="post" class="cuar-form cuar-%3$s-content-form cuar-%1$s-form" action="%2$s" enctype="multipart/form-data">', $this->get_slug(), $this->get_page_url(), $this->get_action() );

		printf( '<input type="hidden" name="cuar_form_id" value="%1$s" />', $this->get_slug() );
		printf( '<input type="hidden" name="cuar_post_type" value="%1$s" />', $this->get_friendly_post_type() );
		
		if ( $this->get_current_post_id()>0 ) {
			printf( '<input type="hidden" name="cuar_post_id" value="%1$s" />', $this->get_current_post_id() );
		}
		
		if ( !isset( $_POST['cuar_post_type'] ) ) {
			$_POST['cuar_post_type'] = $this->get_friendly_post_type();
		} 
		
		wp_nonce_field( 'cuar_' . $this->get_slug(), 'cuar_' . $this->get_slug() . '_nonce' );
		
		do_action( 'cuar/private-content/edit/before_submit_errors', $this );
	
		if ( !empty( $this->form_errors ) ) {
			foreach ( $this->form_errors as $error ) {
				if ( is_wp_error( $error ) ) {
					printf( '<p class="alert alert-warning">%s</p>', $error->get_error_message() );
				} else if ( $error!==false && !empty( $error ) && !is_array( $error ) ) {
					printf( '<p class="alert alert-info">%s</p>', $error );
				}
			}
		}
		
		do_action( 'cuar/private-content/edit/after_submit_errors', $this );
		
		do_action( 'cuar/private-content/edit/before_fields', $this );		
	}
	
	public function print_form_footer() {
		do_action( 'cuar/private-content/edit/after_fields', $this );
		
		echo '</form>';
	}

	public function print_submit_button( $label ) {
        do_action( 'cuar/private-content/edit/before_submit_button', $this );
        
		echo '<div class="form-group">';
		echo '	<div class="submit-container">';
		echo '		<input type="submit" name="cuar_do_register" value="' . esc_attr( $label ) . '" class="btn btn-default" />';
		echo '	</div>';
		echo '</div>';

		do_action( 'cuar/private-content/edit/after_submit_button', $this );
	}

	public function print_title_field( $label, $help_text='' ) {
		$title = '';
		if ( isset( $_POST['cuar_title'] ) ) {
			$title = $_POST['cuar_title'];
		} else if ( $this->get_current_post()!=null ) {
			$title = $this->get_current_post()->post_title;
		}
		
		$field_code = sprintf( '<input type="text" id="cuar_title" name="cuar_title" value="%1$s" class="form-control" />', esc_attr( $title ) );		
		$this->print_form_field( 'cuar_title', $label, $field_code, $help_text );
	}

	public function print_file_field( $label, $help_text='' ) {
		$field_code = '<input type="file" id="cuar_file" name="cuar_file" class="form-control" />';		
		$this->print_form_field( 'cuar_file', $label, $field_code, $help_text );
	}

	public function print_content_field( $label, $help_text='' ) {
		$content = '';
		if ( isset( $_POST['cuar_content'] ) ) {
			$content = $_POST['cuar_content'];
		} else if ( $this->get_current_post()!=null ) {
			$content = $this->get_current_post()->post_content;
		}
		
		if ( !$this->is_rich_editor_enabled() ) {
			$field_code = sprintf( '<textarea rows="5" cols="40" name="cuar_content" id="cuar_content" class="form-control">%1$s</textarea>', esc_attr( $content ) );
		} else {
			ob_start();
			wp_editor( $content, 'cuar_content', $this->plugin->get_default_wp_editor_settings() );

			$field_code = ob_get_contents();
			ob_end_clean();
		}	
		
		$this->print_form_field( 'cuar_content', $label, $field_code, $help_text );
	}

	public function print_owner_field( $label, $help_text='' ) {
		if ( $this->current_user_can_select_owner() ) {
			$po_addon = $this->plugin->get_addon('post-owner');
			
			$owner = $po_addon->get_owner_from_post_data();
			if ( $owner!=null ) {
				$owner_type = $owner['type'];
				$owner_ids = $owner['ids'];
			} else if ( $this->get_current_post()!=null ) {
				$owner = $po_addon->get_post_owner( $this->get_current_post_id() );
				
				$owner_type = $owner['type'];
				$owner_ids = $owner['ids'];
			} else {
				$owner_type = 'usr';
				$owner_ids = array();
			}
		
			ob_start();
			$po_addon->print_owner_type_select_field( 'cuar_owner_type', null, $owner_type );
			$po_addon->print_owner_select_field( 'cuar_owner_type', 'cuar_owner', null, $owner_type, $owner_ids );
			$po_addon->print_owner_select_javascript( 'cuar_owner_type', 'cuar_owner' );
			$field_code = ob_get_contents();
			ob_end_clean();
		
			$this->print_form_field( 'cuar_owner', $label, $field_code, $help_text );
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

	public function print_category_field( $label, $help_text='' ) {		
		$categories = get_terms( $this->get_friendly_taxonomy(), array(
            'hide_empty'        => false,
            'fields'            => 'count'
        ) );
		if ( empty( $categories ) )	{	
			$field_code = '<input type="hidden" name="cuar_category" value="-1" />';			
			echo $field_code;
		} else if ( $this->current_user_can_select_category() ) {
			$category = -1;
			if ( isset( $_POST['cuar_category'] ) ) {
				$category = $_POST['cuar_category'];
			} else if ( $this->get_current_post()!=null ) {
				$cats = wp_get_post_terms( $this->get_current_post_id(), $this->get_friendly_taxonomy(), array( 'fields' => 'ids' ) );
				$category = implode(',', $cats);
			}
			
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
		
			$this->print_form_field( 'cuar_category', $label, $field_code, $help_text );
		} else {
			$category = $this->get_default_category();			
			$field_code = sprintf( '<input type="hidden" name="cuar_category" value="%1$s" />', esc_attr( $category ) );			
			echo $field_code;
		}
	}

	public function print_form_field( $name, $label, $field_code, $help_text='' ) {
		do_action( 'cuar/private-content/edit/before_field?id=' . $name, $this );
			
		echo '<div class="form-group">';
		echo '	<label for="' . $name . '" class="control-label">' . $label . '</label>';
		echo '	<div class="control-container">';
		echo $field_code;
		
		if ( !empty( $help_text ) ) {
			echo '		<span class="help-block">' . $help_text . '</span>';
		}
		
		echo '	</div>';
		echo '</div>';
		
		do_action( 'cuar/private-content/edit/after_field?id=' . $name, $this );			
	}
	
	public function set_current_post_id( $post_id ) {
		$this->current_post_id = $post_id; 
	}
	
	public function get_current_post_id() {		
		if ( $this->current_post_id==null ) {
			if ( isset( $_POST['cuar_post_id'] ) ) {
				$this->current_post_id = $_POST['cuar_post_id'];
			} else if ( get_query_var( 'cuar_post_name', null )!=null ) {
				$args = array( 
						'name' => get_query_var( 'cuar_post_name', null ), 
						'post_type' => array( $this->get_friendly_post_type() )
					);
				
				if ( get_query_var( 'year', null )!=null ) {
					$args['year'] = get_query_var( 'year', null );
				}
				if ( get_query_var( 'monthnum', null )!=null ) {
					$args['monthnum'] = get_query_var( 'monthnum', null );
				}
				if ( get_query_var( 'day', null )!=null ) {
					$args['day'] = get_query_var( 'day', null );
				}
				
				$post = get_posts( $args );
				
				if ( !empty( $post ) ) {
					$this->current_post_id = $post[0]->ID;
					$this->current_post = $post[0];
				} else {
					$this->current_post_id = 0;
				}
			} else {
				$this->current_post_id = 0;
			}
		}
		return $this->current_post_id;
	}
	
	public function get_current_post() {
		if ( $this->current_post==null && $this->get_current_post_id()>0 ) {
			$this->current_post = get_post( $this->get_current_post_id() );
		}
		return $this->current_post;
	}
	
	protected $should_print_form = true;
	protected $form_errors = array();	
	protected $form_messages = array();
	protected $current_post_id = null;
	protected $current_post = null;
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function is_rich_editor_enabled() {
		return $this->plugin->get_option( $this->get_slug() . self::$OPTION_ENABLE_RICH_EDITOR, true );
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
		
		return $defaults;
	}
	
	/*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

	public function get_configurable_capability_groups( $capability_groups ) {
		$post_type = $this->get_friendly_post_type();

		if ( isset( $capability_groups[$post_type] ) ) {
			$capability_groups[$post_type]['groups']['edit-content'] = array(
					'group_name' 	=> __( 'Content edition (from front-office)', 'cuar' ),
					'capabilities' 	=> array(
							$post_type . '_create_select_owner'			=> __( 'Select an owner (uses default else)', 'cuar' ),
							$post_type . '_create_select_category'		=> __( 'Select a category (uses default else)', 'cuar' ),
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
	
	/*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

	public function enable_settings( $target_tab, $enabled_settings = array( 'rich-editor', 'default-ownership', 'default-category', 'moderation' ) ) {
		$this->enabled_settings = $enabled_settings;
		
		if ( is_admin() && !empty( $this->enabled_settings ) ) {
			// Settings	
			add_action( 'cuar/core/settings/print-settings?tab=' . $target_tab, array( &$this, 'print_settings' ), 20, 2 );
			add_filter( 'cuar/core/settings/validate-settings?tab=' . $target_tab, array( &$this, 'validate_options' ), 20, 3 );
		}
	}
	
	protected function get_settings_section() {
		return $this->get_slug() . '_' . $this->get_action() . '_content_frontend';
	}
	
	protected abstract function get_settings_section_title();
	
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
				$this->get_settings_section_title(),
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
							'after'			=> __('Enable the rich editor when editing content.', 'cuar')
						)
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

		if ( in_array('rich-editor', $this->enabled_settings ) ) {
			$cuar_settings->validate_boolean( $input, $validated, $slug . self::$OPTION_ENABLE_RICH_EDITOR );
		}
		
		$validated = $this->validate_additional_settings( $validated, $cuar_settings, $input );
		
		return $validated;
	}
	
	protected function print_additional_settings( $cuar_settings, $options_group ) {		
	}
	
	protected function validate_additional_settings( &$validated, $cuar_settings, $input ) {	
		return $validated;			
	}
	
	public function print_empty_section_info() {
	}
	
	public static $OPTION_ENABLE_RICH_EDITOR	= '-enable_rich_editor';
	
	protected $enabled_settings = array();
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

	/**
	 * Enqueues the select script on the user-edit and profile screens.
	 */
	public function enqueue_scripts() {	
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( isset( $screen->id ) && $screen->id=='customer-area_page_wpca-settings' ) {
				$this->plugin->enable_library('jquery.select2');
			}
		} else {
			$this->plugin->enable_library('jquery.select2');
		}
	}
}

endif; // CUAR_AbstractEditContentPageAddOn
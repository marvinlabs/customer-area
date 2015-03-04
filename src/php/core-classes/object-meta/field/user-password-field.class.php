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

if (!class_exists('CUAR_UserPasswordField')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/field.interface.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/abstract-field.class.php' );

/**
 * A field that renders two input forms to enter a password and its confirmation (and validates accordingly). Persistance is 
 * achieved using the wp_set_password function
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_UserPasswordField extends CUAR_AbstractField implements CUAR_Field {

	public function __construct( $id, $args ) { // $label, $help, $confirm_label, $confirm_help, $validation ) {
		parent::__construct( $id, null, $args );
		
		$this->pwd_renderer = new CUAR_PasswordFieldRenderer( 
				$this->get_arg( 'label' ), 
				false, 
				$this->get_arg( 'inline_help' )
			);
		
		$this->pwd_confirm_renderer = new CUAR_PasswordFieldRenderer( 
				$this->get_arg( 'confirm_label' ), 
				false, 
				$this->get_arg( 'confirm_inline_help' )
			);
		
		$this->pwd_validation_rule = new CUAR_PasswordValidation( 
				$this->get_arg( 'required' ),
				$this->get_arg( 'min_length' ),
				$this->get_arg( 'max_length' )
			);
	}
	
	protected function get_default_args() {
		return array_merge( parent::get_default_args(), array( 
				'inline_help' 			=> '',
				'confirm_label' 		=> '',
				'confirm_inline_help' 	=> '',
				
				'required' 				=> false,	
				'min_length'			=> null,
				'max_length'			=> null,
				'visibility' 			=> array( 'frontend_view_profile', 'frontend_edit_profile', 'admin_edit_profile' ),			
			) );
	}

	// See CUAR_Field
	public function get_type( $is_for_display ) {
		return $is_for_display ? __( 'Password', 'cuar' ) : 'password';
	}

	// See CUAR_Field
	public function persist( $object_id ) {
		$pwd = isset( $_POST[$this->get_id()] ) ? $_POST[$this->get_id()] : null;
		$pwd_confirm = isset( $_POST[$this->get_id() . '_confirm'] ) ? $_POST[$this->get_id() . '_confirm'] : null;

		if ( $pwd==null && $pwd_confirm==NULL ) return TRUE;
		
		if ( $pwd!=$pwd_confirm ) {
			return __('The password and its confirmation do not match', 'cuar');
		}
		
		$validation_result = $this->pwd_validation_rule==null ? TRUE : $this->pwd_validation_rule->validate( $this->get_arg( 'label' ), $pwd );		
		if ( $validation_result===TRUE ) {
			wp_set_password( $pwd, $object_id );
		}
		
		return $validation_result;
	}	

	// See CUAR_Field
	public function render_read_only_field( $object_id ) {
		$this->pwd_renderer->render_read_only_field( $this->get_id(), '' );
	}

	// See CUAR_Field
	public function render_form_field( $object_id ) {
		// Render the field
		$this->pwd_renderer->render_form_field( $this->get_id(), '' );
		$this->pwd_confirm_renderer->render_form_field( $this->get_id() . '_confirm', '' );
	}

	// See CUAR_Field
	public function render_raw_value( $object_id ) {
		$this->pwd_renderer->render_raw_value( '' );
	}

	protected $pwd_renderer = null;
	protected $pwd_confirm_renderer = null;
	protected $pwd_validation_rule = null;
}

endif; // if (!class_exists('CUAR_UserPasswordField')) :
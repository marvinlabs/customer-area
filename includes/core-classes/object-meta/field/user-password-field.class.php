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
include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/password-field-renderer.class.php' );

/**
 * A field that renders two input forms to enter a password and its confirmation (and validates accordingly). Persistance is 
 * achieved using the wp_set_password function
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_UserPasswordField implements CUAR_Field {

	public function __construct( $id, $label, $help, $confirm_label, $confirm_help, $validation ) {
		$this->id = $id;
		$this->pwd_renderer = new CUAR_PasswordFieldRenderer( $label, false, $help );
		$this->pwd_confirm_renderer = new CUAR_PasswordFieldRenderer( $confirm_label, false, $confirm_help );
		$this->pwd_validation_rule = $validation;
	}

	// See CUAR_AbstractField
	public function persist( $object_id ) {
		$pwd = isset( $_POST[$this->id] ) ? $_POST[$this->id] : null;
		$pwd_confirm = isset( $_POST[$this->id . '_confirm'] ) ? $_POST[$this->id . '_confirm'] : null;

		if ( $pwd==null && $pwd_confirm==NULL ) return TRUE;
		
		if ( $pwd!=$pwd_confirm ) {
			return __('The password and its confirmation do not match', 'cuar');
		}
		
		$validation_result = $this->pwd_validation_rule==null ? TRUE : $this->pwd_validation_rule->validate( $this->pwd_renderer->get_label(), $pwd );		
		if ( $validation_result===TRUE ) {
			wp_set_password( $pwd, $object_id );
		}
		
		return $validation_result;
	}	

	// See CUAR_AbstractField
	public function render_read_only_field( $object_id ) {
		$this->pwd_renderer->render_read_only_field( $this->id, '' );
	}

	// See CUAR_AbstractField
	public function render_form_field( $object_id ) {
		// Render the field
		$this->pwd_renderer->render_form_field( $this->id, '' );
		$this->pwd_confirm_renderer->render_form_field( $this->id . '_confirm', '' );
	}

	protected $id = null;
	protected $pwd_renderer = null;
	protected $pwd_confirm_renderer = null;
	protected $pwd_validation_rule = null;
}

endif; // if (!class_exists('CUAR_UserPasswordField')) :
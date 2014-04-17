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

if (!class_exists('CUAR_SimpleField')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/field.interface.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/abstract-field.class.php' );

/**
 * A simple object field (composition of renderer, storage and validation). This object is meant to serve as a base to very basic field types.
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_SimpleField extends CUAR_AbstractField implements CUAR_Field {

	public function __construct( $id, $storage, $args ) {
		parent::__construct( $id, $storage, $args );
	}

	// See CUAR_Field
	public function persist( $object_id ) {
		$value = isset( $_POST[$this->get_id()] ) ? $_POST[$this->get_id()] : null;
		$validation_result = $this->validation_rule==null ? TRUE : $this->validation_rule->validate( $this->renderer->get_label(), $value );
		
		if ( $validation_result===TRUE ) {
			$this->get_storage()->update_field_value( $object_id, $this->get_id(), $value );
		}
		
		return $validation_result;
	}	

	// See CUAR_Field
	public function render_read_only_field( $object_id ) {
		$value = $this->get_storage()->fetch_field_value( $object_id, $this->get_id() );
		$this->renderer->render_read_only_field( $this->get_id(), $value );
	}

	// See CUAR_Field
	public function render_form_field( $object_id ) {
		// Get the field value either from POST data or from the DB storage
		if ( isset( $_POST[$this->get_id()] ) ) {
			$value = $_POST[$this->get_id()];
		} else {
			$value = $this->get_storage()->fetch_field_value( $object_id, $this->get_id() );
		}
		
		// Render the field
		$this->renderer->render_form_field( $this->get_id(), $value );
	}

	// See CUAR_Field
	public function render_raw_value( $object_id ) {
		$value = $this->get_storage()->fetch_field_value( $object_id, $this->get_id() );
		$this->renderer->render_raw_value( $value );
	}

	protected function set_renderer( $renderer ) {
		$this->renderer = $renderer;
	}
	
	protected function set_validation_rule( $validation_rule ) {
		$this->validation_rule = $validation_rule;
	}
	
	protected $renderer = null;
	protected $validation_rule = null;
}

endif; // if (!class_exists('CUAR_SimpleField')) :
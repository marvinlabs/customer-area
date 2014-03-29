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

/**
 * A simple object field (composition of renderer, storage and validation) 
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_SimpleField implements CUAR_Field {

	/**
	 * 
	 * @param string $id
	 * @param CUAR_FieldRenderer $renderer
	 * @param CUAR_Storage $storage
	 * @param CUAR_ValidationRule $validation_rule
	 */
	public function __construct( $id, $renderer, $storage, $validation_rule=null ) {
		$this->id = $id;
		$this->renderer = $renderer;
		$this->storage = $storage;
		$this->validation_rule = $validation_rule;
	}

	// See CUAR_AbstractField
	public function persist( $object_id ) {
		$value = isset( $_POST[$this->id] ) ? $_POST[$this->id] : null;
		$validation_result = $this->validation_rule==null ? TRUE : $this->validation_rule->validate( $this->renderer->get_label(), $value );
		
		if ( $validation_result===TRUE ) {
			$this->storage->update_field_value( $object_id, $this->id, $value );
		}
		
		return $validation_result;
	}	

	// See CUAR_AbstractField
	public function render_read_only_field( $object_id ) {
		$value = $this->storage->fetch_field_value( $object_id, $this->id );
		$this->renderer->render_read_only_field( $this->id, $value );
	}

	// See CUAR_AbstractField
	public function render_form_field( $object_id ) {
		// Get the field value either from POST data or from the DB storage
		if ( isset( $_POST[$this->id] ) ) {
			$value = $_POST[$this->id];
		} else {
			$value = $this->storage->fetch_field_value( $object_id, $this->id );
		}
		
		// Render the field
		$this->renderer->render_form_field( $this->id, $value );
	}

	protected $id = null;
	protected $field = null;
	protected $storage = null;
	protected $validation_rule = null;
}

endif; // if (!class_exists('CUAR_SimpleField')) :
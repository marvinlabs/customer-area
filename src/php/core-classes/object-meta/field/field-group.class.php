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

if (!class_exists('CUAR_FieldGroup')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/field.interface.php' );

/**
 * A group of fields to be shown all together
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_FieldGroup implements CUAR_Field {

	/**
	 * 
	 * @param string $title
	 * @param CUAR_Field[] $fields
	 */
	public function __construct( $title, $fields=array() ) {
		$this->title = $title;
		$this->fields = $fields;
	}
	
	// See CUAR_AbstractField
	public function persist( $object_id ) {
		$errors = array();
		
		foreach ( $this->fields as $id => $field ) {
			$res = $field->persist( $object_id );
			if ( $res!==TRUE ) {
				if ( is_array( $res ) ) {
					$errors = array_merge( $errors, $res );
				} else {
					$errors[] = $res;
				}
			}
		} 
		
		return empty( $errors ) ? TRUE : $errors;
	}	

	// See CUAR_AbstractField
	public function render_read_only_field( $object_id ) {
		echo '<div class="cuar-field-group">' . "\n";
		echo '	<div class="cuar-title widget-title">' . $this->get_title() . '</div>' . "\n";
		echo '	<div class="cuar-fields">' . "\n";
		
		foreach ( $this->fields as $id => $field ) {
			$field->render_read_only_field( $object_id );
		}

		echo '	</div>' . "\n";
		echo '</div>' . "\n";
	}

	// See CUAR_AbstractField
	public function render_form_field( $object_id ) {
		echo '<fieldset class="cuar-field-group">' . "\n";
		echo '	<legend>' . $this->get_title() . '</legend>' . "\n";
		echo '	<div class="cuar-fields">' . "\n";
		
		foreach ( $this->fields as $id => $field ) {
			$field->render_form_field( $object_id );
		}

		echo '	</div>' . "\n";
		echo '</fieldset>' . "\n";
	}
	
	/**
	 * 
	 * @param string $title
	 * @return CUAR_FieldGroup
	 */
	public function set_title( $title ) {
		$this->title = $title;
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}
	
	/**
	 * 
	 * @param string $id
	 * @param CUAR_Field $field
	 * @return CUAR_FieldGroup
	 */
	public function add_field( $id, $field ) {
		$this->fields[$id] = $field;
		return $this;
	}
	
	/**
	 * 
	 * @return CUAR_Field[]
	 */
	public function get_fields() {
		return $this->fields;
	}
	
	/**
	 * 
	 * @param string $id
	 * @return CUAR_Field[]
	 */
	public function get_field( $id ) {
		return $this->fields[id];
	}
	
	protected $title = '';
	protected $fields = array();
	protected $visibility = array();
	
}

endif; // if (!class_exists('CUAR_FieldGroup')) :
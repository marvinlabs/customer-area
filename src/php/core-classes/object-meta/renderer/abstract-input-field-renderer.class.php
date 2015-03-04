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

if (!class_exists('CUAR_AbstractInputFieldRenderer')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/abstract-field-renderer.class.php' );

/**
 * Implementation of a field holding short text
 *
 * @author Vincent Prat @ MarvinLabs
 */
abstract class CUAR_AbstractInputFieldRenderer extends CUAR_AbstractFieldRenderer implements CUAR_FieldRenderer {

	public function __construct( $input_type, $label, $readonly = false, $inline_help='' ) {
		$this->input_type = $input_type;
		$this->readonly = $readonly;
		$this->initialize($label, $inline_help);
	}

	public function get_type() {
		return $this->input_type; 
	}

	protected function get_readonly_field_value( $id, $value ) {
		return $value;
	}

	protected function get_form_field_input( $id, $value ) {
		return sprintf( '<input type="%5$s" id="%1$s" name="%2$s" value="%3$s" class="%4$s" %6$s/>',
				$id,
				$id,
				esc_attr( $value ), 
				'form-control',
				esc_attr( $this->input_type ),
				($this->readonly ? 'readonly="readonly"' : '')
			);
	}
	
	protected $input_type = 'text';
	protected $readonly = false;
}

endif; // if (!class_exists('CUAR_AbstractInputFieldRenderer')) :
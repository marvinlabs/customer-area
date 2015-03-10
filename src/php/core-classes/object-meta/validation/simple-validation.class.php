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

if (!class_exists('CUAR_SimpleValidation')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/validation-rule.interface.php' );

/**
 * The base class for validation rules
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_SimpleValidation implements CUAR_ValidationRule {

	public function __construct( $required=false ) {
		$this->required = $required;
	}
	
	// See CUAR_ValidationRule
	public function validate( $label, $value ) {
		if ( $this->required && (!isset( $value ) || $value==null) ) return sprintf( __( '%1$s is required.', 'cuar' ), $label );
		
		return TRUE;
	}
	
	protected $required = false;
	
}

endif; // if (!class_exists('CUAR_SimpleValidation')) :
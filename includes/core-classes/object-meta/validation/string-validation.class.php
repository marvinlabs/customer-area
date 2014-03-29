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

if (!class_exists('CUAR_StringValidation')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/validation-rule.interface.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/simple-validation.class.php' );

/**
 * An implementation to validate strings
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_StringValidation extends CUAR_SimpleValidation implements CUAR_ValidationRule {

	public function __construct( $required=false, $min_length = null, $max_length = null ) {
		parent::__construct( $required );
		
		$this->min_length = $min_length;
		$this->max_length = $max_length;
	}

	// See CUAR_ValidationRule
	public function validate( $label, $value ) {
		$parent_val = parent::validate( $label, $value );
		if ( $parent_val!==TRUE ) return $parent_val;
		
		$errors = array();		
		if ( $this->min_length!=null && strlen( $value ) < $this->min_length ) $errors[] = sprintf( __('%1$s must contain at least %2$s characters', 'cuar'), $label, $this->min_length );
		if ( $this->max_length!=null && strlen( $value ) > $this->max_length ) $errors[] = sprintf( __('%1$s must contain at most %2$s characters', 'cuar'), $label, $this->max_length );
		
		return empty( $errors ) ? TRUE : $errors;
	}
	
	protected $min_length = null;
	protected $max_length = null;
	
}

endif; // if (!class_exists('CUAR_StringValidation')) :
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

if (!class_exists('CUAR_NumberValidation')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/validation-rule.interface.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/simple-validation.class.php' );

/**
 * An implementation to validate numbers
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_NumberValidation extends CUAR_SimpleValidation implements CUAR_ValidationRule {

	public function __construct( $required=false, $force_int = false, $min = null, $max = null ) {
		parent::__construct( $required );
		
		$this->force_int = $force_int;
		$this->min = $min;
		$this->max = $max;
	}

	// See CUAR_ValidationRule
	public function validate( $label, $value ) {
		$parent_val = parent::validate( $label, $value );
		if ( $parent_val!==TRUE ) return $parent_val;
		
		if ( !is_numeric( $value ) ) return sprintf( __('%1$s is not a valid number', 'cuar'), $label );
		if ( $this->force_int && !is_int( $value ) ) return sprintf( __('%1$s is not a valid integer', 'cuar'), $label );
		
		$errors = array();
		if ( $this->min!=null && $value < $this->min ) $errors[] = sprintf( __('%1$s must be greater than %2$s', 'cuar'), $label, $this->min );
		if ( $this->max!=null && $value > $this->max ) $errors[] = sprintf( __('%1$s must be lower than %2$s', 'cuar'), $label, $this->max );
		
		return empty( $errors ) ? TRUE : $errors;
	}
	
	protected $min = null;
	protected $max = null;
	protected $force_int = false;
	
}

endif; // if (!class_exists('CUAR_NumberValidation')) :
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

if (!interface_exists('CUAR_ValidationRule')) :

/**
 * The base interface for validation rules
*
* @author Vincent Prat @ MarvinLabs
*/
interface CUAR_ValidationRule {
	
	/**
	 * Validate a value according to the rule. 
	 * 
	 * @param unknown $value The value to validate
	 * 
	 * @return boolean|string|array TRUE if the value is valid. Either a string or an array of strings to hold validation error messages if the valid is not valid.
	 */
	public function validate( $label, $value );
	
}

endif; // if (!interface_exists('CUAR_ValidationRule')) :
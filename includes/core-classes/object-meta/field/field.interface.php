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

if (!interface_exists('CUAR_Field')) :

/**
 * The base interface for object fields
*
* @author Vincent Prat @ MarvinLabs
*/
interface CUAR_Field {
	
	/**
	 * Persist the field to database. The value will be taken from POST data. This does not do anything if the field is readonly.
	 * 
	 * @param int $object_id The object associated to the field (post_id, user_id, ...) 
	 * @return boolean|string|array TRUE if the value is valid. Either a string or an array of strings to hold validation error messages if the valid is not valid.
	 */
	public function persist( $object_id );
	
	/**
	 * Render the field for display only
	 * 
	 * @param int $object_id The object associated to the field (post_id, user_id, ...) 
	 */
	public function render_read_only_field( $object_id );

	/**
	 * Render the field for use in a form
	 *
	 * @param int $object_id The object associated to the field (post_id, user_id, ...)
	 */
	public function render_form_field( $object_id );

	/**
	 * Render the field value for direct use 
	 *
	 * @param int $object_id The object associated to the field (post_id, user_id, ...)
	 */
	public function render_raw_value( $object_id );
	
	/**
	 * Indicate if this field should be displayed in the given context.
	 * 
	 * @param string $context
	 */
	public function is_visible( $context );
	
	/**
	 * Returns the label for that field
	 */
	public function get_label();
	
	/**
	 * Returns the type for that field
	 * 
	 * @param boolean $is_for_display if true, return the type as a localized human readable string. Else, return the type ID
	 */
	public function get_type( $is_for_display );
}

endif; // if (!class_exists('CUAR_Field')) :
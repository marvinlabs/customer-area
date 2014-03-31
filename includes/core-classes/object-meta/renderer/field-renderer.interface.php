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

if (!interface_exists('CUAR_FieldRenderer')) :

/**
 * The base interface for field renderers
*
* @author Vincent Prat @ MarvinLabs
*/
interface CUAR_FieldRenderer {

	/**
	 * Returns a unique type identifier for this field (for instance, short-text, single-select, radio, checkbox, ...)
	 */
	public function get_type();
	
	/**
	 * Returns a label for that field that can be used in various cases (for example in validation error messages)
	 */
	public function get_label();
	
	/**
	 * Show the field value as a simple read-only area. For instance within a P or an IMG tag.
	 */
	public function render_read_only_field( $id, $value );
	
	/**
	 * Show the field value as a form input field. For instance, a textarea or an input tag.
	 */
	public function render_form_field( $id, $value );
	
	/**
	 * Show the field value
	 */
	public function render_raw_value( $value );
}

endif; // if (!interface_exists('CUAR_FieldRenderer')) :
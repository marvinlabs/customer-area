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

if (!class_exists('CUAR_EmailFieldRenderer')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/abstract-input-field-renderer.class.php' );

/**
 * Implementation of a field holding short text
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PasswordFieldRenderer extends CUAR_AbstractInputFieldRenderer implements CUAR_FieldRenderer {

	public function __construct( $label, $readonly = false, $inline_help='' ) {
		parent::__construct( 'password', $label, $readonly, $inline_help );
	}

	protected function get_readonly_field_value( $id, $value ) {
		$hidden_char = apply_filters( 'cuar/core/fields/password/hidden-char', '&bull;' );
		return str_repeat( $hidden_char, 8 );
	}
}

endif; // if (!class_exists('CUAR_EmailFieldRenderer')) :
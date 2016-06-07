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

if (!class_exists('CUAR_LongTextFieldRenderer')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/abstract-field-renderer.class.php' );

/**
 * Implementation of a field holding short text
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_LongTextFieldRenderer extends CUAR_AbstractFieldRenderer implements CUAR_FieldRenderer {

	public function __construct( $label, $enable_rich_editor=false, $readonly = false, $inline_help='' ) {
		$this->enable_rich_editor = $enable_rich_editor;
		$this->readonly = $readonly;
		$this->initialize($label, $inline_help);
	}
	
	public function get_type() {
		return 'long-text'; 
	}

	protected function get_readonly_field_value( $id, $value ) {
		return $value;
	}

	protected function get_form_field_input( $id, $value ) {
		if ( $this->enable_rich_editor && !$this->readonly ) {
			if (is_admin()) {
				ob_start();

				$editor_settings = cuar_wp_editor_settings(array(
					'textarea_name' => $id
				));

				wp_editor($value, $id, $editor_settings);

				$out = ob_get_contents();
				ob_end_clean();

				return $out;
			} else {
				return sprintf( '<textarea rows="5" cols="40" id="%1$s" name="%2$s" class="%4$s" %5$s>%3$s</textarea>',
					$id,
					$id,
					$value,
					'form-control cuar-js-richeditor',
					($this->readonly ? 'readonly="readonly"' : '')
				);
			}
		} else {
			return sprintf( '<textarea rows="5" cols="40" id="%1$s" name="%2$s" class="%4$s" %5$s>%3$s</textarea>',
					$id,
					$id,
					$value, 
					'form-control',
					($this->readonly ? 'readonly="readonly"' : '')
				);
		}
	}

	protected $enable_rich_editor = false;
	protected $readonly = false;
}

endif; // if (!class_exists('CUAR_LongTextFieldRenderer')) :
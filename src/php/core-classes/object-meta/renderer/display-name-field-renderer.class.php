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

if (!class_exists('CUAR_DisplayNameFieldRenderer')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/abstract-input-field-renderer.class.php' );

/**
 * Implementation of a select list showing name alternatives
 *
 * @author Vincent Prat @ MarvinLabs
 */
	class CUAR_DisplayNameFieldRenderer extends CUAR_AbstractFieldRenderer implements CUAR_FieldRenderer
	{

		public function __construct($label, $readonly = false, $inline_help = '')
		{
			$this->readonly = $readonly;
			$this->initialize($label, $inline_help);
		}

		public function get_type()
		{
			return 'select-text';
		}

		protected function get_readonly_field_value($id, $value)
		{
			return $value;
		}

		protected function get_form_field_input($id, $value)
		{
			if (!$this->readonly) {
				$current_user = get_userdata(get_current_user_id());

				wp_enqueue_script('user-profile');

				$out = '';
				$out .= '<select name="' . $id . '" id="' . $id . '" class="form-control">' . "\n";
				$public_display = array();
				$public_display['display_nickname'] = $current_user->nickname;
				$public_display['display_username'] = $current_user->user_login;

				if (!empty($current_user->first_name))
					$public_display['display_firstname'] = $current_user->first_name;

				if (!empty($current_user->last_name))
					$public_display['display_lastname'] = $current_user->last_name;

				if (!empty($current_user->first_name) && !empty($current_user->last_name)) {
					$public_display['display_firstlast'] = $current_user->first_name . ' ' . $current_user->last_name;
					$public_display['display_lastfirst'] = $current_user->last_name . ' ' . $current_user->first_name;
				}

				if (!in_array($current_user->display_name, $public_display))// Only add this if it isn't duplicated elsewhere
					$public_display = array('display_displayname' => $current_user->display_name) + $public_display;

				$public_display = array_map('trim', $public_display);
				$public_display = array_unique($public_display);

				foreach ($public_display as $id => $item) {
					$out .= '<option ' . selected($current_user->display_name, $item, false) . '>' . $item . '</option>' . "\n";
				}
				$out .= '</select>' . "\n";
				return $out;
			} else {
				return sprintf('<input type="%5$s" id="%1$s" name="%2$s" value="%3$s" class="%4$s" %6$s/>',
						$id,
						$id,
						esc_attr($value),
						'form-control',
						esc_attr($this->input_type),
						($this->readonly ? 'disabled' : '')
				);
			}
		}

		protected $readonly = false;
	}

endif; // if (!class_exists('CUAR_DisplayNameFieldRenderer')) :
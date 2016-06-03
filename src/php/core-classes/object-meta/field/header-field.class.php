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

if (!class_exists('CUAR_HeaderField')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/field.interface.php' );
include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/abstract-field.class.php' );

/**
 * A pseudo field that renders a heading
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_HeaderField extends CUAR_AbstractField implements CUAR_Field {

	public function __construct( $id, $args ) {
		parent::__construct( $id, null, $args );
	}
	
	protected function get_default_args() {
		return array_merge( parent::get_default_args(), array(
				'wrapper' 			=> 'h3',
				'visibility' 		=> array( 'frontend_view_profile', 'frontend_edit_profile', 'admin_edit_profile' )
		) );
	}

	// See CUAR_Field
	public function get_type( $is_for_display ) {
		return $is_for_display ? __( 'Header', 'cuar' ) : 'header';
	}

	// See CUAR_Field
	public function persist( $object_id ) {
		return TRUE;
	}	

	// See CUAR_Field
	public function render_read_only_field( $object_id ) {
		printf( '<%1$s id="%3$s" class="cuar-field cuar-field-header %4$s">%2$s</%1$s>', $this->get_arg( 'wrapper' ), $this->get_arg( 'label' ), $this->get_id(), $this->get_arg( 'wrapper_class' ));
	}

	// See CUAR_Field
	public function render_form_field( $object_id ) {
		$this->render_read_only_field( $object_id );
	}

	// See CUAR_Field
	public function render_raw_value( $object_id ) {
		echo $this->get_arg( 'label' );
	}
}

endif; // if (!class_exists('CUAR_HeaderField')) :
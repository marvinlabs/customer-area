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

if (!class_exists('CUAR_AbstractField')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/field.interface.php' );

/**
 * A simple object field (composition of renderer, storage and validation) 
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AbstractField implements CUAR_Field {

	/**
	 * 
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $id, $storage, $args ) {
		$this->id = $id;
		$this->storage = $storage;
		$this->args = array_merge( $this->get_default_args(), $args );
	}

	// See CUAR_Field
	public function is_visible( $context ) {
		return in_array( $context, $this->get_arg( 'visibility' ) );
	}

	// See CUAR_Field
	public function get_label() {
		return $this->get_arg( 'label' );
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_arg( $key ) {
		return isset($this->args[$key]) ? $this->args[$key] : "";
	}
	
	protected function get_default_args() {
		return array( 
				'label'				=> $this->id,
				'visibility' 		=> array( 'frontend_view_profile', 'frontend_edit_profile', 'admin_edit_profile', 'admin_list_customers', 'admin_list_customers_sortables' )
			);
	}
	
	protected function get_storage() {
		return $this->storage;
	}

	protected $id = null;
	protected $storage = null;
	protected $args = array();
}

endif; // if (!class_exists('CUAR_AbstractField')) :
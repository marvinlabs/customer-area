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

if (!class_exists('CUAR_UserStorage')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/storage/storage.interface.php' );

/**
 * An implementation to store info as user data in the table
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_UserStorage implements CUAR_Storage {

	public function __construct() {
	}

	public function update_field_value( $object_id, $field_id, $value ) {
		if ( !in_array( $field_id, self::$VALID_FIELDS ) ) return;
		
		wp_update_user( array ( 'ID' => $object_id, $field_id => $value ) ) ;
	}

	public function fetch_field_value( $object_id, $field_id ) {
		if ( !in_array( $field_id, self::$VALID_FIELDS ) ) return null;
		
		$user = get_userdata( $object_id );		
		if ( $user!==FALSE && isset( $user->$field_id ) ) {
			return $user->$field_id;
		}
		
		return null;
	}
	
	protected static $VALID_FIELDS = array( 'user_login', 'user_pass', 'user_nicename', 'user_url', 'user_email', 'display_name', 'nickname', 'first_name', 'last_name', 'description', 'jabber', 'aim', 'yim' );
}

endif; // if (!class_exists('CUAR_UserStorage')) :
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
	
if (!class_exists('CUAR_PrivateFileThemeUtils')) :

/**
 * Gathers static functions to be used in themes 
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivateFileThemeUtils {
	
	/**
	 * Get the URL where the file linked to the specified post can be downloaded directly
	 * 
	 * @param int $post_id Defaults to the current post ID from the loop
	 * @return string
	 */
	public static function get_the_file_link( $post_id = null, $action = 'download' ) {
		if ( !$post_id ) $post_id = get_the_ID();		
		if ( !$post_id ) return '';
		
		global $cuar_pf_addon;
		return $cuar_pf_addon->get_file_permalink( $post_id, $action );
	}

	/**
	 * Prints the URL where the file linked to the specified post can be downloaded directly 
	 * 
	 * @see get_the_download_link
	 * 
	 * @param int $post_id Defaults to the current post ID from the loop
	 */
	public static function the_file_link( $post_id = null, $action = 'download' ) {
		echo self::get_the_file_link( $post_id, $action );
	}
	
	/**
	 * Get the name of the file associated to the given post
	 * 
	 * @param int $post_id
	 * @return string|mixed
	 */	
	public static function get_the_file_name( $post_id = null ) {
		if ( !$post_id ) $post_id = get_the_ID();		
		if ( !$post_id ) return '';
		
		$file = get_post_meta( $post_id, 'cuar_private_file_file', true );

		if ( !$file || empty( $file ) ) return '';
		
		return apply_filters( 'cuar_the_file_name', $file['file'] );
	}
	
	/**
	 * Prints the name of the file associated to the given post
	 * 
	 * @see get_the_file_name
	 * 
	 * @param int $post_id
	 * @return string|mixed
	 */		
	public static function the_file_name( $post_id = null ) {
		echo self::get_the_file_name( $post_id );
	}
	
	/**
	 * Get the type of the file associated to the given post
	 * 
	 * @param int $post_id
	 * @return string|mixed
	 */	
	public static function get_the_file_type( $post_id = null ) {
		if ( !$post_id ) $post_id = get_the_ID();		
		if ( !$post_id ) return '';
		
		$file = get_post_meta( $post_id, 'cuar_private_file_file', true );

		if ( !$file || empty( $file ) ) return '';
		
		return apply_filters( 'cuar_the_file_type', pathinfo( $file['file'], PATHINFO_EXTENSION ) );
	}
	
	/**
	 * Prints the type of the file associated to the given post
	 * 
	 * @see get_the_file_type
	 * 
	 * @param int $post_id
	 * @return string|mixed
	 */		
	public static function the_file_type( $post_id = null ) {
		echo self::get_the_file_type( $post_id );
	}
}

endif; // if (!class_exists('CUAR_PrivateFileThemeUtils')) :
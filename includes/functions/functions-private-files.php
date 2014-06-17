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
	
/**
 * Get the URL where the file linked to the specified post can be downloaded directly
 * 
 * @param int $post_id Defaults to the current post ID from the loop
 * @return string
 */
function cuar_get_the_file_link( $post_id = null, $action = 'download' ) {
	if ( !$post_id ) $post_id = get_the_ID();		
	if ( !$post_id ) return '';
	
	$cuar_plugin = CUAR_Plugin::get_instance();
	$pf_addon = $cuar_plugin->get_addon('private-files');
	return $pf_addon->get_file_permalink( $post_id, $action );
}

/**
 * Prints the URL where the file linked to the specified post can be downloaded directly 
 * 
 * @see get_the_download_link
 * 
 * @param int $post_id Defaults to the current post ID from the loop
 */
function cuar_the_file_link( $post_id = null, $action = 'download' ) {
	echo cuar_get_the_file_link( $post_id, $action );
}

/**
 * Get the name of the file associated to the given post
 * 
 * @param int $post_id
 * @return string|mixed
 */	
function cuar_get_the_file_name( $post_id = null ) {
	if ( !$post_id ) $post_id = get_the_ID();		
	if ( !$post_id ) return '';
	
	$file = get_post_meta( $post_id, 'cuar_private_file_file', true );

	if ( !$file || empty( $file ) ) return '';
	
	return apply_filters( 'cuar/private-content/files/the-name', $file['file'], $post_id );
}

/**
 * Prints the name of the file associated to the given post
 * 
 * @see get_the_file_name
 * 
 * @param int $post_id
 * @return string|mixed
 */		
function cuar_the_file_name( $post_id = null ) {
	echo cuar_get_the_file_name( $post_id );
}

/**
 * Get the type of the file associated to the given post
 * 
 * @param int $post_id
 * @return string|mixed
 */	
function cuar_get_the_file_type( $post_id = null ) {
	if ( !$post_id ) $post_id = get_the_ID();		
	if ( !$post_id ) return '';
	
	$file = get_post_meta( $post_id, 'cuar_private_file_file', true );

	if ( !$file || empty( $file ) ) return '';
	
	return apply_filters( 'cuar/private-content/files/the-type', pathinfo( $file['file'], PATHINFO_EXTENSION ), $post_id );
}

/**
 * Prints the type of the file associated to the given post
 * 
 * @see get_the_file_type
 * 
 * @param int $post_id
 * @return string|mixed
 */		
function cuar_the_file_type( $post_id = null ) {
	echo cuar_get_the_file_type( $post_id );
}

/**
 * Get the type of the file associated to the given post
 * 
 * @param int $post_id
 * @return string|mixed
 */	
function cuar_get_the_file_size( $post_id = null, $human = true ) {
	if ( !$post_id ) $post_id = get_the_ID();		
	if ( !$post_id ) return '';
	
	$cuar_plugin = CUAR_Plugin::get_instance();
	$pf_addon = $cuar_plugin->get_addon('private-files');
		
	$size = $pf_addon->get_file_size( $post_id );
	if ( false===$size ) {
		return '';
	}
	
	if ( $human ) {
		$size = cuar_format_human_file_size( $size );  
	}
	
	return apply_filters( 'cuar/private-content/files/the-size', $size, $post_id );
}

/**
 * Prints the type of the file associated to the given post
 * 
 * @see get_the_file_type
 * 
 * @param int $post_id
 * @return string|mixed
 */		
function cuar_the_file_size( $post_id = null, $human = true ) {
	echo cuar_get_the_file_size( $post_id, $human );
}


/** Helper function to format file size */
function cuar_format_human_file_size( $size ) {
	$factor = 1;
	$unit = __( 'bytes', 'cuar' );
	
	if ( $size>=1024*1024*1024*1024 ) {
		$factor = 1024*1024*1024*1024;
		$unit = __( 'TB', 'cuar' );
	} else if ( $size>=1024*1024*1024 ) {
		$factor = 1024*1024*1024;
		$unit = __( 'GB', 'cuar' );
	} else if ( $size>=1024*1024 ) {
		$factor = 1024*1024;
		$unit = __( 'MB', 'cuar' );
	} else if ( $size>=1024 ) {
		$factor = 1024;
		$unit = __( 'kB', 'cuar' );
	} else {
		$unit = __( 'bytes', 'cuar' );
	}
	
	return sprintf( '%1$s %2$s', number_format( $size/$factor, 2 ), $unit );
}
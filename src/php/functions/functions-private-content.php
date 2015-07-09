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
 * Print the owner of a post
 */
function cuar_the_owner( $post_id = 0 ) {
	echo cuar_get_the_owner( $post_id );
}

/**
 * Get the display name of the owner of a post
 * 
 * @param int $post_id
 * @return mixed
 */
function cuar_get_the_owner( $post_id = 0 ) {
	$cuar_plugin = CUAR_Plugin::get_instance();
	$po_addon = $cuar_plugin->get_addon( 'post-owner' ); 
	
	$post_id = $post_id==0 ? get_the_ID() : $post_id;		
	$owner_name = $po_addon->get_post_owner_displayname( $post_id );
	return apply_filters( 'cuar/private-content/the-owner', $owner_name, $post_id );
}

/**
 * Get the add-on corresponding to the current single post we are viewing
 *
 * @return CUAR_AddOn|null
 */
function cuar_get_single_content_addon() {
    $pt = get_post_type();
    $types = cuar()->get_private_types();

    if (isset($types[$pt])) {
        return cuar_addon($types[$pt]['content-page-addon']);
    }

    return null;
}
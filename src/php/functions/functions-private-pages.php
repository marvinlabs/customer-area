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
 * @param array $post_data The same array you would give to wp_insert_post to create your post. No need to set the post type, this will automatically be set.
 * @param array $owner An array containing the owner description: type ('usr', 'grp', 'prj', 'rol', etc.) and IDs of corresponding objects
 * @return int¦WP_Error the post ID if the function could insert the post, else, a WP_Error object
 *
 * ´cuar_insert_file(
 *      array(
 *          'post_title' => 'Test file',
 *          'post_content' => 'This is the content',
 *          'post_status' => 'publish'
 *      ),
 *      array(
 *          'type' => 'usr',
 *          'ids' => array(1)
 *      )
 * );´
 */
function cuar_create_private_page($post_data, $owner)
{
    // Create the post object
    $post_data['post_type'] = 'cuar_private_page';
    $post_id = wp_insert_post( $post_data );
    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }

    // Assign the owner
    /** @var CUAR_PostOwnerAddOn $po_addon */
    $po_addon = cuar_addon('post-owner');
    $po_addon->save_post_owners( $post_id, $owner['ids'], $owner['type'] );

    return $post_id;
}
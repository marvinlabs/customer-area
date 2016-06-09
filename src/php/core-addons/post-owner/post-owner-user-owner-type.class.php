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
 * Add-on to allow setting user groups or user roles as owner of a private content
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PostOwnerUserOwnerType
{
    public function __construct()
    {
        add_filter('cuar/core/ownership/owner-types', array(&$this, 'declare_new_owner_types'));
        add_filter('cuar/core/ownership/content/meta-query', array(&$this, 'extend_private_posts_meta_query'), 10, 3);
        add_filter('cuar/core/ownership/real-user-ids?owner-type=usr', array(&$this, 'get_post_owner_user_ids_from_usr'), 10, 2);
        add_filter('cuar/core/ownership/validate-post-ownership', array(&$this, 'is_user_owner_of_post'), 10, 5);
        add_action('cuar/core/ownership/printable-owners?owner-type=usr', array(&$this, 'get_printable_owners_for_type_usr'), 10);
        add_action('cuar/core/ownership/saved-displayname', array(&$this, 'saved_post_owner_displayname'), 10, 4);
    }

    /*------- EXTEND THE OWNER TYPES AVAILABLE ----------------------------------------------------------------------*/

    /**
     * Give the display name for our owner types
     *
     * @param string $displayname
     * @param int    $post_id
     * @param string $owner_type
     * @param array  $owner_ids
     *
     * @return string
     *
     */
    public function saved_post_owner_displayname($displayname, $post_id, $owner_type, $owner_ids)
    {
        $names = array();

        if ($owner_type == 'usr')
        {
            $names = array();
            foreach ($owner_ids as $id)
            {
                $u = new WP_User($id);
                $names[] = apply_filters('cuar/core/ownership/owner-display-name?owner-type=usr', $u->display_name, $u);
            }
        }

        asort($names);

        return empty($names) ? $displayname : implode(", ", $names);
    }

    /**
     * Check if a user owns the given post
     *
     * @param boolean $initial_result
     * @param int     $post_id
     * @param int     $user_id
     * @param string  $post_owner_type
     * @param array   $post_owner_ids
     *
     * @return boolean true if the user owns the post
     */
    public function is_user_owner_of_post($initial_result, $post_id, $user_id, $post_owner_type, $post_owner_ids)
    {
        if ($initial_result) return true;

        if ($post_owner_type == 'usr')
        {
            return in_array($user_id, $post_owner_ids);
        }

        return false;
    }

    /**
     * Print a select field with various global rules
     *
     * @param unknown $in
     *
     * @return array
     */
    public function get_printable_owners_for_type_usr($in)
    {
        $all_users = apply_filters('cuar/core/ownership/selectable-owners?owner-type=usr', null);
        if (null === $all_users)
        {
            $all_users = get_users(array('orderby' => 'display_name', 'fields' => 'all_with_meta'));
        }

        $out = $in;
        foreach ($all_users as $u)
        {
            $out[$u->ID] = apply_filters('cuar/core/ownership/owner-display-name?owner-type=usr', $u->display_name, $u);
        }

        return $out;
    }

    /**
     * Extend the meta query to fetch private posts belonging to a user (also fetches the posts for his role and
     * groups)
     *
     * @param array               $base_meta_query
     * @param int                 $user_id The user we want to fetch private posts for
     * @param CUAR_PostOwnerAddOn $po_addon
     *
     * @return array
     */
    public function extend_private_posts_meta_query($base_meta_query, $user_id, $po_addon)
    {
        // For users
        $user_meta_query = array(
            $po_addon->get_owner_meta_query_component('usr', $user_id)
        );

        // Deal with all this
        return array_merge($base_meta_query, $user_meta_query);
    }

    /**
     * Declare the new owner types managed by this add-on
     *
     * @param array $types the existing types
     *
     * @return array The existing types + our types
     */
    public function declare_new_owner_types($types)
    {
        $new_types = array(
            'usr' => __('User', 'cuar'),
        );

        return array_merge($types, $new_types);
    }

    /**
     * Return all user IDs that belong to the given role
     *
     * @param array  $user_ids  The array of user IDs for current owners
     * @param string $owner_ids The owner IDs
     *
     * @return array
     */
    public function get_post_owner_user_ids_from_usr($user_ids, $owner_ids)
    {
        return array_unique($owner_ids, SORT_REGULAR);
    }
}

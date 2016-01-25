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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon.class.php');

if ( !class_exists('CUAR_ContainerOwnerAddOn')) :

    /**
     * Add-on to provide all the stuff required to set an owner on a post type and include that post type in the
     * customer area.
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_ContainerOwnerAddOn extends CUAR_AddOn
    {

        public function __construct()
        {
            parent::__construct('container-owner', '5.0.0');
        }

        public function get_addon_name()
        {
            return __('Container Owner', 'cuar');
        }

        public function run_addon($plugin)
        {
            // Init the admin interface if needed
            if (is_admin())
            {
            }
            else
            {
                add_action('template_redirect', array(&$this, 'protect_single_container_access'));
            }
        }

        /*------- QUERY FUNCTIONS ---------------------------------------------------------------------------------------*/

        /**
         * Builds the meta query to check if a user owns a post
         *
         * @param int $user_id The user ID of the owner
         *
         * @return array See the meta query documentation on WP codex
         */
        public function get_meta_query_containers_owned_by($user_id)
        {
            $user_id = apply_filters('cuar/core/ownership/container/meta-query/override-owner-id', $user_id);

            $base_meta_query = array(
                'relation' => 'OR'
            );

            return apply_filters('cuar/core/ownership/container/meta-query', $base_meta_query, $user_id);
        }

        public function get_owner_meta_query_component($key, $owner_id)
        {
            return array(
                'key'     => $key,
                'value'   => '|' . $owner_id . '|',
                'compare' => 'LIKE'
            );
        }

        /*------- ACCESS TO OWNER INFO ----------------------------------------------------------------------------------*/

        /**
         * Check if a user is an owner of the given container.
         *
         * @param int $post_id
         * @param int $user_id
         *
         * @return boolean
         */
        public function is_user_owner_of_container($post_id, $user_id)
        {
            return apply_filters('cuar/core/ownership/validate-container-ownership', false, $post_id, $user_id);
        }

        /*------- FRONTEND ----------------------------------------------------------------------------------------------*/

        /**
         * Protect access to single posts: only for owners.
         */
        public function protect_single_container_access()
        {
            $private_container_types = $this->plugin->get_container_post_types();

            // If not on a matching post type, we do nothing
            if (empty($private_container_types) || !is_singular($private_container_types)) return;

            // If not logged-in, we ask for details
            if ( !is_user_logged_in())
            {
                $this->plugin->login_then_redirect_to_url(get_permalink());
            }

            // If not authorized to view the page, we bail
            $post = get_queried_object();
            $author_id = $post->post_author;
            $current_user_id = apply_filters('cuar/core/ownership/protect-single-post/override-user-id', get_current_user_id());

            $is_current_user_owner = $this->is_user_owner_of_container($post->ID, $current_user_id);
            if ( !($is_current_user_owner || $author_id == $current_user_id || current_user_can('cuar_view_any_' . get_post_type())))
            {
                wp_die(__("You are not authorized to view this page", "cuar"));
                exit();
            }

            do_action('cuar/core/ownership/protect-single-post/on-access-granted', $post);
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

    }

// Make sure the addon is loaded
    new CUAR_ContainerOwnerAddOn();

endif; // if (!class_exists('CUAR_PrivateFileAddOn')) 

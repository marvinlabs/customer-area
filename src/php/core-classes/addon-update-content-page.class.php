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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-edit-content-page.class.php');

if ( !class_exists('CUAR_AbstractUpdateContentPageAddOn')) :

    /**
     * The base class for addons that should render a page to update private content
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_AbstractUpdateContentPageAddOn extends CUAR_AbstractEditContentPageAddOn
    {

        public function __construct($addon_id = null, $min_cuar_version = null)
        {
            parent::__construct($addon_id, $min_cuar_version);
        }

        protected function set_page_parameters($priority, $description)
        {
            parent::set_page_parameters($priority, $description);

            if ( !isset($this->page_description['hide_in_menu']))
            {
                $this->page_description['hide_in_menu'] = true;
            }
        }

        public function get_action()
        {
            return 'update';
        }

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);

            $this->enable_update_content_permalink();

            if ( !is_admin())
            {
                add_action('cuar/private-content/view/single-post-action-links?post-type=' . $this->get_friendly_post_type(),
                    array(&$this, 'get_single_content_action_links'));

                add_action('cuar/private-container/view/single-post-action-links?post-type=' . $this->get_friendly_post_type(),
                    array(&$this, 'get_single_content_action_links'));
            }
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        protected function get_redirect_url_after_action()
        {
            if (get_query_var('cuar_action', null) == 'delete')
            {
                /** @var CUAR_CustomerPagesAddOn $cp_addon */
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $post_id = $cp_addon->get_page_id($this->get_parent_slug());

                return get_permalink($post_id);
            }

            return get_permalink($this->get_current_post_id());
        }

        protected function get_default_required_fields()
        {
            return array('cuar_title', 'cuar_content', 'cuar_category', 'cuar_owner');
        }

        public function get_default_owners()
        {
            return array();
        }

        public function get_default_category()
        {
            return -1;
        }

        /*------- FORM HANDLING -----------------------------------------------------------------------------------------*/

        protected function is_action_authorized($action)
        {
            switch ($action)
            {
                case 'update':
                    // If not logged-in, bail
                    if ( !is_user_logged_in()) return false;

                    // User can create content
                    if ( !$this->current_user_can_edit_content())
                    {
                        die('You are not allowed to create this type of content.');
                    }

                    return true;

                case 'delete':
                    // If not logged-in, bail
                    if ( !is_user_logged_in()) return false;

                    // User can delete content
                    if ( !$this->current_user_can_delete_content())
                    {
                        die('You are not allowed to delete this content.');
                    }

                    return true;
            }

            return false;
        }

        protected function do_edit_content($action, $form_data)
        {
            if (parent::do_edit_content($action, $form_data) === true)
            {
                return true;
            }

            if ($action == 'delete')
            {
                if ($this->get_current_post_id() <= 0)
                {
                    $this->form_errors[] = new WP_Error(__('You must supply a post ID to delete', 'cuar'), 0);

                    return false;
                }

                if ( !$this->current_user_can_delete_content())
                {
                    $this->form_errors[] = new WP_Error(__('You are not allowed to delete this post', 'cuar'), 0);

                    return false;
                }

                if (false !== wp_delete_post($this->get_current_post_id(), false))
                {
                    $this->set_form_success(
                        __('Done', 'cuar'),
                        __('The content has been deleted.', 'cuar')
                    );
                    
                    return true;
                }
            }

            return false;
        }

        /*------- PERMALINKS --------------------------------------------------------------------------------------------*/

        /**
         * Allow this page to get URLs for content archives
         */
        protected function enable_update_content_permalink()
        {
            add_filter('rewrite_rules_array', array(&$this, 'insert_update_content_rewrite_rules'), 15);
            add_filter('query_vars', array(&$this, 'insert_update_content_query_vars'));
        }

        /**
         * Add rewrite rules for the archive subpages.
         *
         * @param unknown $rules
         *
         * @return array
         */
        public function insert_update_content_rewrite_rules($rules)
        {
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $parent_page_id = $cp_addon->get_page_id($this->get_parent_slug());
            $page_slug = untrailingslashit(str_replace(trailingslashit(home_url()), '', get_permalink($parent_page_id)));

            $page_id = $cp_addon->get_page_id($this->get_slug());

            $new_rules = array();

            // Single post rule with action update
            $rewrite_rule = 'index.php?page_id=' . $page_id . '&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cuar_post_name=$matches[4]&cuar_action='
                . $this->get_action();
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/' . $cp_addon->get_update_content_slug() . '/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            // Single post rule with action delete
            $rewrite_rule = 'index.php?page_id=' . $page_id
                . '&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cuar_post_name=$matches[4]&cuar_action=delete';
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/' . $cp_addon->get_delete_content_slug() . '/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            return $new_rules + $rules;
        }

        /**
         * Add query variables for the archive subpages.
         *
         * @param unknown $vars
         *
         * @return array
         */
        public function insert_update_content_query_vars($vars)
        {
            array_push($vars, 'cuar_post_name');
            array_push($vars, 'cuar_action');

            return $vars;
        }

        /**
         * Get the URL to update private content
         *
         * @param unknown $post_id
         *
         * @return string
         */
        public function get_update_content_url($post_id)
        {
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $url = trailingslashit(get_permalink($post_id));
            $url .= $cp_addon->get_update_content_slug() . '/';

            return $url;
        }

        /**
         * Get the URL for the archive corresponding to a given date.
         *
         * @param unknown $post_id
         *
         * @return string
         */
        public function get_delete_content_url($post_id)
        {
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $nonce = wp_create_nonce('cuar_' . $this->get_slug());

            $url = trailingslashit(get_permalink($post_id));
            $url .= $cp_addon->get_delete_content_slug() . '/';
            $url .= '?nonce=' . $nonce;

            return $url;
        }

        public function get_single_content_action_links($links)
        {
            $post_id = get_queried_object_id();

            if ($this->current_user_can_edit_content($post_id))
            {
                $links[] = array(
                    'title'       => '<span class="fa fa-edit"></span> ' . __('Edit', 'cuar'),
                    'tooltip'     => __('Edit', 'cuar'),
                    'url'         => $this->get_update_content_url($post_id),
                    'extra_class' => ''
                );
            }

            if ($this->current_user_can_delete_content($post_id))
            {
                $links[] = array(
                    'title'           => '<span class="fa fa-trash"></span> ' . __('Delete', 'cuar'),
                    'tooltip'         => __('Delete', 'cuar'),
                    'url'             => $this->get_delete_content_url($post_id),
                    'extra_class'     => '',
                    'confirm_message' => __('Are you sure that you want to delete this content?', 'cuar')
                );
            }

            return $links;
        }

        /*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            $capability_groups = parent::get_configurable_capability_groups($capability_groups);

            $post_type = $this->get_friendly_post_type();

            if (isset($capability_groups[$post_type]))
            {
                $capability_groups[$post_type]['groups']['update-content'] = array(
                    'group_name'   => __('Content update (from front-office)', 'cuar'),
                    'capabilities' => array(
                        $post_type . '_update_any_content'      => __('Update any content from front office', 'cuar'),
                        $post_type . '_update_authored_content' => __('Update authored content', 'cuar'),
                        $post_type . '_update_owned_content'    => __('Update owned content', 'cuar'),
                    )
                );
                $capability_groups[$post_type]['groups']['delete-content'] = array(
                    'group_name'   => __('Content removal (from front-office)', 'cuar'),
                    'capabilities' => array(
                        $post_type . '_delete_any_content'      => __('Delete any content from front office', 'cuar'),
                        $post_type . '_delete_authored_content' => __('Delete authored content', 'cuar'),
                        $post_type . '_delete_owned_content'    => __('Delete owned content', 'cuar'),
                    )
                );
            }

            return $capability_groups;
        }

        public function current_user_can_delete_content($post_id = null)
        {
            if ($post_id == null)
            {
                $post_id = $this->get_current_post_id();
                if ($post_id == null || $post_id <= 0)
                {
                    die(__('You must specify a post to delete', 'cuar'));
                }
            }

            $post_type = $this->get_friendly_post_type();

            if (current_user_can($post_type . '_delete_any_content')) return true;

            $user_id = get_current_user_id();

            $post = get_post($post_id);

            if ($post->post_author == $user_id
                && current_user_can($post_type . '_delete_authored_content')
            )
            {
                return true;
            }

            $po_addon = $this->plugin->get_addon('post-owner');
            if ($po_addon->is_user_owner_of_post($post_id, $user_id)
                && current_user_can($post_type . '_delete_owned_content')
            )
            {
                return true;
            }

            return false;
        }

        public function current_user_can_edit_content($post_id = null)
        {
            if ($post_id == null)
            {
                $post_id = $this->get_current_post_id();
                if ($post_id == null || $post_id <= 0)
                {
                    die(__('You must specify a post to edit', 'cuar'));
                }
            }

            $post_type = $this->get_friendly_post_type();

            if (current_user_can($post_type . '_update_any_content')) return true;

            $user_id = get_current_user_id();

            $post = get_post($post_id);

            if ($post->post_author == $user_id
                && current_user_can($post_type . '_update_authored_content')
            )
            {
                return true;
            }

            $po_addon = $this->plugin->get_addon('post-owner');
            if ($po_addon->is_user_owner_of_post($post_id, $user_id)
                && current_user_can($post_type . '_update_owned_content')
            )
            {
                return true;
            }

            return false;
        }

        public function is_accessible_to_current_user()
        {
            if (get_queried_object_id() != $this->get_page_id()) return false;

            return parent::is_accessible_to_current_user();
        }

        /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

        protected function get_settings_section_title()
        {
            return __('Content edition', 'cuar');
        }


    }

endif; // CUAR_AbstractUpdateContentPageAddOn
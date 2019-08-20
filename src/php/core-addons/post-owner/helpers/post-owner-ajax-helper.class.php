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
 * Helpers for Ajax features of the post owner addon
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PostOwnerAjaxHelper
{
    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PostOwnerAddOn */
    private $po_addon;

    /**
     * Constructor.
     *
     * @param CUAR_Plugin         $plugin
     * @param CUAR_PostOwnerAddOn $po_addon
     */
    public function __construct($plugin, $po_addon)
    {
        $this->plugin = $plugin;
        $this->po_addon = $po_addon;

        if (is_admin())
        {
            add_action('wp_ajax_cuar_search_author', [&$this, 'ajax_find_author']);
            add_action('wp_ajax_cuar_search_visible_by', [&$this, 'ajax_find_visible_by']);
        }

        add_action('wp_ajax_cuar_search_selectable_owner', [&$this, 'ajax_find_selectable_owner']);
    }

    public function print_field_script($field_id, $action, $nonce, $extra_data)
    {
        /** @noinspection PhpIncludeInspection */
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/post-owner',
            'post-owner-ajax-script.template.php',
            'templates'));
    }

    public function get_user_display_value($user_id, $context = '')
    {
        if (is_a($user_id, WP_User::class))
        {
            $user = $user_id;
        }
        else if (is_a($user_id, stdClass::class))
        {
            $user = new WP_User($user_id->ID);
        }
        else if (0 !== (int)($user_id))
        {
            $user = new WP_User($user_id);
        }
        else
        {
            return 'INVALID ARGUMENT passed to get_user_display_value';
        }

        return apply_filters('cuar/core/ajax/user-search/display-result',
            $user->display_name,
            $user,
            $context);
    }

    /*------- AJAX CALLBACKS -----------------------------------------------------------------------------------------*/

    public function ajax_find_selectable_owner()
    {
        // Replace filter:  cuar/core/ownership/printable-owners?owner-type=
        // by:              cuar/core/ajax/search/post-owners?owner-type=

        $type_id = $this->get_query_param('owner_type', null, true);
        $search = $this->get_query_param('search', '');
        $page = $this->get_query_param('page', 1);

        $this->check_nonce_query_param('cuar_search_selectable_owner_' . $type_id);

        wp_send_json_success(apply_filters('cuar/core/ajax/search/post-owners?owner-type=' . $type_id,
            [
                'results' => [],
                'more'    => false,
            ],
            $search,
            $page));
    }

    public function ajax_find_author()
    {
        $this->check_nonce_query_param('cuar_search_author');
        $this->check_capability('cuar_access_admin_panel');

        $post_type = $this->get_query_param('post_type', null, true);
        $this->check_post_type_capability($post_type, 'read_private_posts');

        $search = $this->get_query_param('search', '');
        $page = $this->get_query_param('page', 1);
        wp_send_json_success($this->find_users($search, 'author', $page));
    }

    public function ajax_find_visible_by()
    {
        $this->check_nonce_query_param('cuar_search_visible_by');
        $this->check_capability('cuar_access_admin_panel');

        $post_type = $this->get_query_param('post_type', null, true);
        $this->check_post_type_capability($post_type, 'read_private_posts');

        $search = $this->get_query_param('search', '');
        $page = $this->get_query_param('page', 1);
        wp_send_json_success($this->find_users($search, 'visible_by', $page));
    }

    /*------- UTILITIES ----------------------------------------------------------------------------------------------*/

    public function find_users($search, $context, $page = 1, $extra_query_args = [])
    {
        $args = [
            'search'         => empty($search) ? '*' : '*' . $search . '*',
            'search_columns' => ['display_name'],
            'orderby'        => 'display_name',
            'fields'         => ['ID', 'display_name'],
            'number'         => 20,
            'paged'          => $page,
        ];
        $args = array_merge($args, $extra_query_args);
        $args = apply_filters('cuar/core/ajax/search/query-args?type=users', $args, $context);

        $user_query = new WP_User_Query($args);

        $result = [];
        foreach ($user_query->get_results() as $user)
        {
            $result[] = [
                'id'   => $user->ID,
                'text' => $this->get_user_display_value($user, $context),
            ];
        }

        return apply_filters('cuar/core/ajax/search/response?type=users',
            [
                'results' => $result,
                'more'    => $user_query->get_total() > count($result),
            ],
            $context);
    }

    public function find_posts($search, $context, $page = 1, $extra_query_args = [])
    {
        $args = [
            'orderby'        => 'post_title',
            'order'          => 'ASC',
            'posts_per_page' => 20,
            'paged'          => $page,
        ];

        if (!empty($search))
        {
            $args['s'] = $search;
        }

        $args = array_merge($args, $extra_query_args);
        $args = apply_filters('cuar/core/ajax/search/query-args?type=posts', $args, $context);

        $post_query = new WP_Query($args);

        $result = [];
        foreach ($post_query->posts as $post)
        {
            $result[] = [
                'id'   => $post->ID,
                'text' => $post->post_title,
            ];
        }

        return apply_filters('cuar/core/ajax/search/response?type=posts',
            [
                'results' => $result,
                'more'    => $post_query->post_count < $post_query->found_posts,
            ],
            $context);
    }

    public function check_post_type_capability($post_type, $pt_cap = 'read_private_posts')
    {
        $post_type_object = get_post_type_object($post_type);
        if ($post_type_object === null || !current_user_can($post_type_object->cap->$pt_cap))
        {
            wp_send_json_error(__('You are not allowed to search users', 'cuar'));
        }
    }

    public function check_capability($cap)
    {
        if (!current_user_can($cap))
        {
            wp_send_json_error(__('You are not allowed to search users', 'cuar'));
        }
    }

    public function check_nonce_query_param($action, $name = 'nonce')
    {
        $nonce = $this->get_query_param($name, '', true);

        if (!wp_verify_nonce($nonce, $action))
        {
            wp_send_json_error(__('Trying to cheat?', 'cuar'));
        }
    }

    public function get_query_param($name, $default = null, $required = false)
    {
        $value = isset($_GET[$name]) ? $_GET[$name] : null;
        if ($required && empty($value))
        {
            wp_send_json_error(__('Missing parameter', 'cuar'));
        }

        return empty($value) ? $default : $value;
    }

    public function format_items_for_select2($items, $search = null, $page = null, $per_page = 20)
    {
        $has_more = false;
        $results = [];

        foreach ($items as $id => $text)
        {
            // Filter if needed
            if (!empty($search) && false === strstr(strtolower($text), strtolower($search)))
            {
                continue;
            }

            $results[] = ['id' => $id, 'text' => $text];
        }

        // Paginate if needed
        if ($page !== null)
        {
            $total = count($results);
            $total_pages = ceil($total / $per_page);
            $page = min(max($page, 1), $total_pages);
            $offset = ($page - 1) * $per_page;
            if ($offset < 0)
            {
                $offset = 0;
            }

            $has_more = $page < $total_pages;
            $results = array_slice($results, $offset, $per_page);
        }

        return [$results, $has_more];
    }
}

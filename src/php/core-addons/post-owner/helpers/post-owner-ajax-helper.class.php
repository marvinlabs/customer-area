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
            add_action('wp_ajax_cuar_search_author', array(&$this, 'ajax_find_author'));
            add_action('wp_ajax_cuar_search_visible_by', array(&$this, 'ajax_find_visible_by'));
        }
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
        if (0 !== (int)($user_id))
        {
            $user = new WP_User($user_id);
        }
        else if (is_a($user_id, WP_User::class))
        {
            $user = $user_id;
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

    public function ajax_find_author()
    {
        $post_type = $this->get_query_param('post_type', null);
        $nonce = $this->get_query_param('nonce', null);
        $search = $this->get_query_param('search', '');
        $page = $this->get_query_param('page', 1);

        if (empty($post_type) || empty($nonce))
        {
            wp_send_json_error(__('Missing parameter', 'cuar'));
        }

        if (!wp_verify_nonce($nonce, 'cuar_search_author'))
        {
            wp_send_json_error(__('Trying to cheat?', 'cuar'));
        }

        if (!current_user_can('cuar_access_admin_panel'))
        {
            wp_send_json_error(__('You are not allowed to search users', 'cuar'));
        }

        $post_type_object = get_post_type_object($post_type);
        if ($post_type_object === null || !current_user_can($post_type_object->cap->read_private_posts))
        {
            wp_send_json_error(__('You are not allowed to search users', 'cuar'));
        }

        wp_send_json_success($this->find_users($search, $page, 'author'));
    }

    public function ajax_find_visible_by()
    {
        $post_type = $this->get_query_param('post_type', null);
        $nonce = $this->get_query_param('nonce', null);
        $search = $this->get_query_param('search', '');
        $page = $this->get_query_param('page', 1);

        if (empty($post_type) || empty($nonce))
        {
            wp_send_json_error(__('Missing parameter', 'cuar'));
        }

        if (!wp_verify_nonce($nonce, 'cuar_search_visible_by'))
        {
            wp_send_json_error(__('Trying to cheat?', 'cuar'));
        }

        if (!current_user_can('cuar_access_admin_panel'))
        {
            wp_send_json_error(__('You are not allowed to search users', 'cuar'));
        }

        $post_type_object = get_post_type_object($post_type);
        if ($post_type_object === null || !current_user_can($post_type_object->cap->read_private_posts))
        {
            wp_send_json_error(__('You are not allowed to search users', 'cuar'));
        }

        wp_send_json_success($this->find_users($search, $page, 'visible_by'));
    }

    /*------- UTILITIES ----------------------------------------------------------------------------------------------*/

    private function find_users($search, $page = 1, $context)
    {
        $user_query = new WP_User_Query(
            apply_filters('cuar/core/ajax/user-search/query-args', array(
                'search'         => empty($search) ? '*' : '*' . $search . '*',
                'search_columns' => array('display_name'),
                'orderby'        => 'display_name',
                'fields'         => array('ID', 'display_name'),
                'number'         => 20,
                'paged'          => $page,
            ), $context));

        $result = array();
        foreach ($user_query->get_results() as $user)
        {
            $result[] = array(
                'id'   => $user->ID,
                'text' => $this->get_user_display_value($user, $context),
            );
        }

        return apply_filters('cuar/core/ajax/user-search/response', array(
            'results' => $result,
            'more'    => $user_query->get_total() > count($result),
        ), $context);
    }

    private function get_query_param($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }
}

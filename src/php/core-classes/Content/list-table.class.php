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

if ( !class_exists('WP_List_Table'))
{
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class CUAR_PrivateContentTable
 *
 * List private content on the admin side
 *
 * @link http://plugins.svn.wordpress.org/custom-list-table-example/tags/1.3/list-table-example.php
 */
abstract class CUAR_ListTable extends WP_List_Table
{
    /** @var CUAR_Plugin The plugin instance */
    public $plugin = null;

    /** @var int Total number of posts */
    public $total_count = 0;

    /** @var array Number of posts for each view. */
    public $view_counts = array();

    /** @var array Page parameters to pass to the query. */
    public $parameters = array();

    /** @var string Class to wrap the WP_Post objects (leave empty to use only WP_Post) */
    public $item_wrapper_class = '';

    /** @var string The base URL for this table page */
    public $base_url = '';

    /** @var bool Are we currently viewing the trashed posts */
    public $is_trash = false;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     *
     * @param CUAR_Plugin $plugin
     * @param array       $args
     * @param             $base_url
     * @param string      $item_wrapper_class Class to wrap the WP_Post objects (leave empty to use only WP_Post)
     */
    public function __construct($plugin, $args, $base_url, $item_wrapper_class = '')
    {
        parent::__construct($args);
        $this->plugin = $plugin;
        $this->base_url = $base_url;
        $this->item_wrapper_class = $item_wrapper_class;
    }

    /**
     * Setup the table
     */
    public function initialize()
    {
        $this->parse_form_data();
        $this->process_bulk_action();
        $this->count_posts();
        $this->prepare_items();
    }

    protected function parse_form_data()
    {
        $form_data = $_GET;

        $this->parse_parameters($form_data);
    }

    /**
     * Read the parameters from the query and store them for later use
     *
     * @param array $form_data The form data
     */
    protected function parse_parameters($form_data)
    {
        $this->parameters['status'] = isset($form_data['status']) ? $form_data['status'] : 'any';
        $this->parameters['posts'] = isset($form_data['posts']) ? $form_data['posts'] : array();

        if ($this->parameters['status'] == 'trash')
        {
            $this->is_trash = true;
        }
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function get_parameter($key)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : '';
    }

    /**
     * Count the posts to be displayed in the table
     */
    protected function count_posts()
    {
        $query_args = $this->get_query_args();

        $statuses = $this->get_view_statuses();

        foreach ($statuses as $id => $label)
        {
            $status = $id;
            if ($id == 'any')
            {
                $status = array_diff(get_available_post_statuses(), array('trash'));
            }

            $args = array_merge($query_args, array(
                'fields'         => 'ids',
                'paged'          => 1,
                'posts_per_page' => -1,
                'post_status'    => $status
            ));

            $q = new WP_Query($args);
            $this->view_counts[$id] = $q->post_count;
        }

        $this->total_count = $this->view_counts[$this->parameters['status']];
    }

    /**
     * Get the query parameters
     * @return array
     */
    protected abstract function get_query_args();

    /*------- VIEWS --------------------------------------------------------------------------------------------------*/

    protected function get_view_statuses()
    {
        return array(
            'any' => __('All', 'cuar')
        );
    }

    /**
     * Retrieve the view types
     * @return array $views All the views available
     */
    public function get_views()
    {
        $current = $this->parameters['status'];
        $views = array();
        foreach ($this->get_view_statuses() as $id => $label)
        {
            $views[$id] = $this->format_view_item($label,
                $this->view_counts[$id],
                add_query_arg(array('status' => $id, 'paged' => 1), $this->base_url),
                $current === $id);
        }

        return $views;
    }

    /**
     * Get the properly formatted view item (items shown above the table with post count)
     *
     * @param $label
     * @param $count
     * @param $link
     * @param $is_current
     *
     * @return string
     */
    public function format_view_item($label, $count, $link, $is_current)
    {
        if ($count === null)
        {
            return sprintf('<a href="%1$s"%2$s>%3$s</a>',
                $link, $is_current ? ' class="current"' : '', $label);
        }
        else
        {
            return sprintf('<a href="%1$s"%2$s>%3$s</a>&nbsp;<span class="count">(%4$s)</span>',
                $link, $is_current ? ' class="current"' : '', $label, $count);
        }
    }

    /*------- COLUMNS ------------------------------------------------------------------------------------------------*/

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
        );

        return $columns;
    }

    /**
     *
     * @return array An associative array containing all the columns that should be sortable:
     *               'slugs'=>array('data_values',bool)
     */
    public function get_sortable_columns()
    {
        return array();
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', 'posts', $item->ID);
    }

    public function column_default($item, $column_name)
    {
        return apply_filters('cuar/core/list-table/column-content', 'Not implemented yet', $item,
            $column_name, $this);
    }

    public function column_taxonomy($item, $taxonomy)
    {
        $terms = wp_get_post_terms($item->ID, $taxonomy);
        $out = array();
        foreach ($terms as $t)
        {
            $out[] = sprintf(__('<a href="%1$s" title="Show content classified under %2$s" class="cuar-taxonomy-term">%3$s</a>', 'cuar'),
                $this->base_url . '&' . $taxonomy . '=' . $t->slug,
                esc_attr($t->name),
                $t->name);
        }

        $out = empty($out) ? '-' : implode(', ', $out);

        return apply_filters('cuar/core/admin/content-list-table/column-content?post_type=' . $this->post_type,
            $out, $item, $taxonomy, $this);
    }

    public function column_date($item)
    {
        if ('0000-00-00 00:00:00' == $item->post_date)
        {
            $t_time = $h_time = __('Unpublished', 'cuar');
            $time_diff = 0;
        }
        else
        {
            $t_time = get_the_time(__('Y/m/d g:i:s A', 'cuar'));
            $m_time = $item->post_date;
            $time = get_post_time('G', true, $item);

            $time_diff = time() - $time;

            if ($time_diff > 0 && $time_diff < DAY_IN_SECONDS)
            {
                $h_time = sprintf(__('%s ago', 'cuar'), human_time_diff($time));
            }
            else
            {
                $h_time = mysql2date(__('Y/m/d', 'cuar'), $m_time);
            }
        }

        $out = '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $item, 'date', 'list')
            . '</abbr>';
        $out .= '<br />';
        if ('publish' == $item->post_status)
        {
            $out .= __('Published', 'cuar');
        }
        elseif ('future' == $item->post_status)
        {
            if ($time_diff > 0)
            {
                $out .= '<strong class="attention">' . __('Missed schedule', 'cuar') . '</strong>';
            }
            else
            {
                $out .= __('Scheduled', 'cuar');
            }
        }
        else
        {
            $out .= __('Last Modified', 'cuar');
        }

        return apply_filters('cuar/core/admin/content-list-table/column-content?post_type=' . $this->post_type,
            $out, $item, 'date', $this);
    }

    public function column_title($item)
    {
        $row_actions = array();

        if (current_user_can($this->post_type_object->cap->edit_post))
        {
            $row_actions['edit'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                admin_url('post.php?post=' . $item->ID . '&action=edit&post_type=' . $this->post_type),
                __('Edit', 'cuar'));
        }

        if (current_user_can($this->post_type_object->cap->delete_post))
        {
            if ($this->is_trash)
            {
                $row_actions['untrash'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                    wp_nonce_url(add_query_arg(array('action' => 'cuar-untrash', 'posts' => $item->ID),
                        $this->base_url),
                        'cuar_content_row_nonce'),
                    __('Restore', 'cuar'));

                $row_actions['delete'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                    wp_nonce_url(add_query_arg(array('action' => 'cuar-delete', 'posts' => $item->ID), $this->base_url),
                        'cuar_content_row_nonce'),
                    __('Delete permanently', 'cuar'));
            }
            else
            {
                $row_actions['trash'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                    wp_nonce_url(add_query_arg(array('action' => 'cuar-trash', 'posts' => $item->ID), $this->base_url),
                        'cuar_content_row_nonce'),
                    __('Trash', 'cuar'));
            }
        }

        $row_actions['view'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
            get_permalink($item->ID),
            $item->post_status != 'draft' ? __('View', 'cuar') : __('Preview', 'cuar'));

        $row_actions = apply_filters('cuar/core/admin/content-list-table/row-actions', $row_actions, $item);

        if (current_user_can($this->post_type_object->cap->edit_post))
        {
            $title = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                admin_url('post.php?post=' . $item->ID . '&action=edit&post_type=' . $this->post_type),
                get_the_title($item->ID));
        }
        else
        {
            $title = get_the_title($item->ID);
        }

        if ($item->post_status == 'draft')
        {
            $title .= ' - <span class="post-state">' . __('Draft', 'cuar') . '</span>';
        }

        $value = $title . $this->row_actions($row_actions);

        return apply_filters('cuar/core/admin/content-list-table/column-content?post_type=' . $this->post_type,
            $value, $item, 'title', $this);
    }

    public function column_author($item)
    {
        $user = get_userdata($item->post_author);

        $value = sprintf('<a href="%1$s" title="Show content authored by %2$s" class="cuar-author">%3$s</a>',
            $this->base_url . '&author=' . $user->ID,
            esc_attr($user->display_name),
            $user->user_login);

        return apply_filters('cuar/core/admin/content-list-table/column-content?post_type=' . $this->post_type,
            $value, $item, 'author', $this);
    }

    /*------- BULK ACTIONS -------------------------------------------------------------------------------------------*/

    /**
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Label'
     */
    public function get_bulk_actions()
    {
        $actions = array();

        if ($this->current_user_can_delete())
        {
            if ($this->is_trash)
            {
                $actions['cuar-untrash'] = __('Restore', 'cuar');
                $actions['cuar-delete'] = __('Delete permanently', 'cuar');
            }
            else
            {
                $actions['cuar-trash'] = __('Move to trash', 'cuar');
            }
        }

        return $actions;
    }

    /**
     * Execute the bulk action on all selected posts
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();
        $posts = $this->parameters['posts'];

        if (isset($_REQUEST['delete_all']) && !empty($_REQUEST['delete_all']))
        {
            $action = 'cuar-delete';
            $posts = $this->get_trashed_post_ids();
        }
        
        if (empty($posts))
        {
            return;
        }

        if ( !is_array($posts))
        {
            $posts = array($posts);
        }

        foreach ($posts as $post_id)
        {
            if (get_post_type($post_id)===false)
            {
                continue;
            }

            $this->execute_action($action, $post_id);
        }
    }

    /**
     * Execute a bulk action on a single post
     *
     * @param string $action
     * @param int    $post_id
     */
    protected function execute_action($action, $post_id)
    {
        switch ($action)
        {
            case 'cuar-untrash':
                if ( !$this->current_user_can_delete())
                {
                    wp_die(__('You are not allowed to restore this item.', 'cuar'));
                }
                wp_untrash_post($post_id);
                break;

            case 'cuar-trash':
                if ( !$this->current_user_can_delete())
                {
                    wp_die(__('You are not allowed to move this item to trash.', 'cuar'));
                }
                wp_trash_post($post_id, false);
                break;

            case 'cuar-delete':
                if ( !$this->current_user_can_delete())
                {
                    wp_die(__('You are not allowed to delete this item.', 'cuar'));
                }
                wp_delete_post($post_id, true);
                break;
        }
    }

    /**
     * @return bool true if the current user is allowed to delete items
     */
    protected function current_user_can_delete()
    {
        return false;
    }

    /**
     * @return array Get all posts in trash
     */
    protected function get_trashed_post_ids()
    {
        return array();
    }

    /*------- OTHER FUNCTIONS ----------------------------------------------------------------------------------------*/

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    public function prepare_items()
    {
        // Prepare our columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Register the pagination
        $total_items = $this->total_count;
        $items_per_page = $this->get_items_per_page(get_class($this) . '_per_page');
        $current_page = $this->get_pagenum();
        $page_count = ceil($total_items / $items_per_page);

        $this->set_pagination_args(array(
            "total_items" => $total_items,
            "total_pages" => $page_count,
            "per_page"    => $items_per_page,
        ));

        // Fetch the items
        $args = $this->get_query_args();
        $args = array_merge($args, array(
            'paged'          => $current_page,
            'posts_per_page' => $items_per_page
        ));

        $this->items = get_posts($args);

        if ( !empty($this->item_wrapper_class))
        {
            $item_class = $this->item_wrapper_class;
            foreach ($this->items as $i => $p)
            {
                $this->items[$i] = new $item_class($p);
            }
        }
    }

    /**
     * @global int   $cat
     *
     * @param string $which
     */
    public function extra_tablenav($which)
    {
        global $cat;
        ?>
        <div class="alignleft actions">
            <?php
            if ($this->is_trash && $this->current_user_can_delete())
            {
                submit_button(__('Empty Trash', 'cuar'), 'apply', 'delete_all', false);
            }
            ?>
        </div>
    <?php
    }
}
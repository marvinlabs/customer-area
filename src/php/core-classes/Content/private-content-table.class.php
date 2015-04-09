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
class CUAR_PrivateContentTable extends WP_List_Table
{
    /** @var CUAR_Plugin The plugin instance */
    public $plugin = null;

    /** @var CUAR_PostOwnerAddOn */
    public $po_addon = null;

    /** @var string The post type to be displayed by this table */
    public $post_type = null;

    /** @var object The post type object */
    public $post_type_object = null;

    /** @var int Total number of posts */
    public $total_count = 0;

    /** @var array Number of posts for each view. */
    public $view_counts = array();

    /** @var array Page parameters to pass to the query. */
    public $parameters = array();

    /** @var string The base URL for this table page */
    public $base_url = '';

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     *
     * @param CUAR_Plugin $plugin
     * @param array       $args
     * @param string      $post_type
     */
    public function __construct($plugin, $args, $post_type)
    {
        parent::__construct($args);
        $this->plugin = $plugin;
        $this->po_addon = $plugin->get_addon('post-owner');
        $this->post_type = $post_type;
        $this->post_type_object = get_post_type_object($post_type);
        $this->base_url = admin_url('admin.php?page=' . $post_type);

        $this->parse_parameters();
        $this->count_posts();
    }

    /**
     * Read the parameters from the query and store them for later use
     */
    protected function parse_parameters()
    {
        // TODO Read what has to be read
        $this->parameters['status'] = isset($_GET['status']) ? $_GET['status'] : 'any';
        $this->parameters['search-field'] = isset($_GET['search-field']) ? $_GET['search-field'] : 'title';
        $this->parameters['search-query'] = isset($_GET['search-query']) ? $_GET['search-query'] : '';
        $this->parameters['visible-by'] = isset($_GET['visible-by']) ? $_GET['visible-by'] : 0;
        $this->parameters['start-date'] = isset($_GET['start-date']) ? sanitize_text_field($_GET['start-date']) : null;
        $this->parameters['end-date'] = isset($_GET['end-date']) ? sanitize_text_field($_GET['end-date']) : null;

        // These criterias are not compatible
        if ( !empty($this->parameters['search-query']) && $this->parameters['search-field'] == 'owner')
        {
            $this->parameters['visible-by'] = 0;
        }

        // If current user cannot list all posts, only show what belongs to him
        if (!current_user_can($this->post_type_object->cap->read_private_posts))
        {
            $this->parameters['visible-by'] = get_current_user_id();
        }

        $this->parameters = apply_filters('cuar/core/admin/content-list-table/search-parameters', $this->parameters,
            $this);
    }

    protected function is_search_active()
    {
        $is_active = !empty($this->parameters['search-query'])
            || 0 != $this->parameters['visible-by']
            || !empty($this->parameters['start-date'])
            || !empty($this->parameters['end-date']);

        return apply_filters('cuar/core/admin/content-list-table/is-search-active', $is_active, $this);
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

        $statuses = array('any', 'publish', 'draft');

        foreach ($statuses as $status)
        {
            $args = array_merge($query_args, array(
                'fields'         => 'ids',
                'paged'          => 1,
                'posts_per_page' => -1,
                'post_status'    => $status
            ));

            $q = new WP_Query($args);
            $this->view_counts[$status] = $q->post_count;
        }

        $this->view_counts = apply_filters('cuar/core/admin/content-list-table/view-counts', $this->view_counts, $this);
        $this->total_count = $this->view_counts[$this->parameters['status']];
    }

    /**
     * Get the query parameters
     * @return array
     */
    protected function get_query_args()
    {
        $args = array(
            'post_type'   => $this->post_type,
            'post_status' => $this->parameters['status']
        );

        $args['meta_query'] = array('relation' => 'OR');
        if ( !empty($this->parameters['visible-by']))
        {
            $args['meta_query'] = $this->po_addon->get_meta_query_post_owned_by($this->parameters['visible-by']);
        }

        if ( !empty($this->parameters['search-query']))
        {
            switch ($this->parameters['search-field'])
            {
                case 'title':
                    $args['s'] = $this->parameters['search-query'];
                    break;

                case 'owner':
                    $args['meta_query'][] = array(
                        'key'     => CUAR_PostOwnerAddOn::$META_OWNER_SORTABLE_DISPLAYNAME,
                        'value'   => $this->parameters['search-query'],
                        'compare' => 'LIKE'
                    );
                    break;
            }
        }

        $args['date_query'] = array();

        $start_date = $this->parameters['start-date'];
        if ($start_date != null)
        {
            $start_tokens = explode('/', $start_date);
            if (count($start_tokens) != 3)
            {
                $start_date = null;
            }
            else
            {
                $args['date_query'][] =
                    array(
                        'after'     => array(
                            'year'  => $start_tokens[2],
                            'month' => $start_tokens[1],
                            'day'   => $start_tokens[0],
                        ),
                        'inclusive' => true
                    );
            }
        }

        $end_date = $this->parameters['end-date'];
        if ($end_date != null)
        {
            $end_tokens = explode('/', $end_date);
            if (count($end_tokens) != 3)
            {
                $end_date = null;
            }
            else
            {
                $args['date_query'][] =
                    array(
                        'before'    => array(
                            'year'  => $end_tokens[2],
                            'month' => $end_tokens[1],
                            'day'   => $end_tokens[0],
                        ),
                        'inclusive' => true,
                    );
            }
        }

        return $args;
    }

    /**
     * Retrieve the view types
     * @return array $views All the views available
     */
    public function get_views()
    {
        $current = $this->parameters['status'];

        $views = array();

        $views['all'] = $this->format_view_item(__('All', 'cuar'),
            $this->view_counts['any'],
            remove_query_arg(array('status', 'paged')),
            $current === 'all' || $current == 'any');

        $views['publish'] = $this->format_view_item(__('Published', 'cuar'),
            $this->view_counts['publish'],
            add_query_arg(array('status' => 'publish', 'paged' => false)),
            $current === 'publish');

        $views['draft'] = $this->format_view_item(__('Draft', 'cuar'),
            $this->view_counts['draft'],
            add_query_arg(array('status' => 'draft', 'paged' => false)),
            $current === 'draft');

        return apply_filters('cuar/core/admin/content-list-table/views', $views, $this);
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
            'cb'     => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'  => __('Title', 'cuar'),
            'author' => __('Author', 'cuar'),
            'owner'  => __('Owner', 'cuar'),
            'date'   => __('Date', 'cuar'),
        );

        return apply_filters('cuar/core/admin/content-list-table/columns', $columns, $this);
    }

    /**
     *
     * @return array An associative array containing all the columns that should be sortable:
     *               'slugs'=>array('data_values',bool)
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(// 'title'    => array('title', false),     //true means it's already sorted
        );

        return apply_filters('cuar/core/admin/content-list-table/sortable-columns', $sortable_columns, $this);
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', 'posts', $item->ID);
    }

    public function column_default($item, $column_name)
    {
        return apply_filters('cuar/core/admin/content-list-table/column-content', 'Not implemented yet', $item,
            $column_name, $this);
    }

    public function column_id($item)
    {
        return $item->ID;
    }

    public function column_date($item)
    {
        return get_the_date(get_option('date'), $item->ID) . ' &dash; ' . get_the_time(get_option('time'),
            $item->ID);
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
            $row_actions['delete'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                wp_nonce_url(add_query_arg(array('cuar-action' => 'delete', 'post_id' => $item->ID), $this->base_url),
                    'cuar_content_row_nonce'),
                __('Delete', 'cuar'));
        }


        if ($item->post_status == 'draft')
        {
            $row_actions['view'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                get_permalink($item->ID),
                __('View', 'cuar'));
        }
        else
        {
            $row_actions['view'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
                get_permalink($item->ID),
                __('Preview', 'cuar'));
        }

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

        return $value;
    }

    public function column_author($item)
    {
        $user = get_userdata($item->post_author);

        return sprintf('<a href="%1$s" title="Show content authored by %2$s" class="cuar-author">%3$s</a>',
            admin_url('admin.php?page=' . $this->post_type . '&author=' . $user->ID),
            esc_attr($user->display_name),
            $user->user_login);
    }

    public function column_owner($item)
    {
        return $this->po_addon->get_post_owner_displayname($item->ID, true);
    }

    /*------- BULK ACTIONS -------------------------------------------------------------------------------------------*/

    /**
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Label'
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'cuar')
        );

        return apply_filters('cuar/core/admin/content-list-table/bulk-actions', $actions, $this);
    }

    /**
     * Execute the bulk action on all selected posts
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();
        $posts = isset($_POST['posts']) ? $_POST['posts'] : array();
        if (empty($posts))
        {
            return;
        }

        foreach ($posts as $post_id)
        {
            switch ($action)
            {
                case 'delete':
                    if ( !current_user_can('delete_post', $post_id))
                    {
                        wp_die(__('You are not allowed to delete this item.', 'cuar'));
                    }
                    wp_delete_post($post_id, true);
                    break;

                default:
                    do_action('cuar/core/admin/content-list-table/do-bulk-action', $post_id, $action, $this);
            }
        }
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
        $items_per_page = $this->get_items_per_page('private-content-list-page');
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
    }
}
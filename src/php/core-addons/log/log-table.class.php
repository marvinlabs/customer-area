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
 * Class CUAR_LogTable
 *
 * List logs on the admin side
 *
 * @link http://plugins.svn.wordpress.org/custom-list-table-example/tags/1.3/list-table-example.php
 */
class CUAR_LogTable extends WP_List_Table
{

    public $plugin = null;
    public $content_types = array();

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     *
     * @param CUAR_Plugin $plugin
     */
    public function __construct($plugin)
    {
        parent::__construct(array(
            'singular' => __('Log', 'cuar'),
            'plural'   => __('Logs', 'cuar'),
            'ajax'     => false
        ));

        $this->plugin = $plugin;
        $this->content_types = array_merge($plugin->get_content_types(), $plugin->get_container_types());
        $this->displayable_meta = apply_filters('cuar/core/log/table-displayable-meta', array());
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $columns = array(
            'cb'            => '<input type="checkbox" />', //Render a checkbox instead of text
            'log_id'        => __('ID', 'cuar'),
            'log_timestamp' => __('Date', 'cuar'),
            'log_event'     => __('Event', 'cuar'),
            'log_object'    => __('Object', 'cuar'),
            'log_user'      => __('User', 'cuar'),
            'log_extra'     => __('Extra info', 'cuar'),
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
        $sortable_columns = array(// 'title'    => array('title', false),     //true means it's already sorted
        );

        return $sortable_columns;
    }

    /**
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'cuar')
        );

        return $actions;
    }

    /**
     *
     */
    public function process_bulk_action()
    {
        if ('delete' === $this->current_action())
        {
            $logs = isset($_POST['logs']) ? $_POST['logs'] : array();
            if (empty($logs))
            {
                return;
            }

            foreach ($logs as $log_id)
            {
                if ( !current_user_can('delete_post', $log_id))
                {
                    wp_die(__('You are not allowed to delete this item.'));
                }

                wp_delete_post($log_id, true);
            }
        }
    }

    /**
     * @param CUAR_LogEvent $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', 'logs', $item->id);
    }

    /**
     * This method is called when the parent class can't find a method
     * specifically build for a given column.
     *
     * @param CUAR_LogEvent $item        A singular item (one full row's worth of data)
     * @param array         $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
     */
    public function column_default($item, $column_name)
    {
        return apply_filters('cuar/core/log/table-cell-content', '?' . $column_name . '?', $column_name, $item);
    }

    public function column_log_id($item)
    {
        return $item->id;
    }

    public function column_log_timestamp($item)
    {
        return get_the_date(get_option('date'), $item->id) . ' &dash; ' . get_the_time(get_option('time'),
            $item->id);
    }

    public function column_log_object($item)
    {
        $rel_object_id = $item->get_post()->post_parent;
        $rel_object_type = $item->related_object_type;
        $obj_link_text = isset($this->content_types[$rel_object_type])
            ? $this->content_types[$rel_object_type]['label-singular']
            : $rel_object_type;
        $obj_link_text .= ' ' . $rel_object_id;

        return sprintf('<a href="%1$s" title="Title: %2$s">%3$s</a>',
            admin_url('edit.php?post_type=' . $rel_object_type . '&post_id=' . $rel_object_id),
            esc_attr(get_the_title($rel_object_id)),
            $obj_link_text);
    }

    public function column_log_user($item)
    {
        $user_id = isset($item->user_id) ? $item->user_id : 0;
        if ($user_id == 0)
        {
            return '';
        }

        $user = get_userdata($user_id);

        return sprintf('<span title="%4$s" class="cuar-btn-xs ip">IP</span> <a href="%1$s" title="Profile of %2$s" class="cuar-btn-xs user">%3$s</a>',
            admin_url('user-edit.php?user_id=' . $user_id),
            esc_attr($user->display_name),
            $user->user_login,
            $item->ip);
    }

    public function column_log_event($item)
    {
        $type = $item->get_type();
        $logger = $this->plugin->get_logger();
        $types = $logger->get_valid_event_types();

        return isset($types[$type]) ? $types[$type] : 'Unknown';
    }

    public function column_log_extra($item)
    {
        $fields = array();
        $exclude = array('user_id', 'ip');

        foreach ($this->displayable_meta as $key)
        {
            if (in_array($key, $exclude))
            {
                continue;
            }
            if (isset($item->$key))
            {
                $meta = apply_filters('cuar/core/log/table-meta-pill-descriptor', array(
                    'title' => $item->$key,
                    'value' => $key,
                    'link'  => ''
                ), $key, $item);

                if (empty($meta['link']))
                {
                    $fields[] = sprintf('<span title="%1$s" class="cuar-btn-xs %3$s">%2$s</span>', $meta['title'],
                        esc_attr($meta['value']), $key);
                }
                else
                {
                    $fields[] = sprintf('<a href="%4$s" title="%1$s" class="cuar-btn-xs %3$s">%2$s</a>', $meta['title'],
                        esc_attr($meta['value']), $key, esc_attr($meta['link']));
                }
            }
        }

        return implode(' ', $fields);
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    public function prepare_items()
    {
        $logger = $this->plugin->get_logger();

        // Prepare our columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Retrieve filter values
        $related_object_id = -1;
        $event_type = isset($_POST['filter-by-type']) ? $_POST['filter-by-type'] : null;
        $meta = null;
        $start_date = isset($_POST['start-date']) ? sanitize_text_field($_POST['start-date']) : null;
        $end_date = isset($_POST['end-date']) ? sanitize_text_field($_POST['end-date'])
            : ($start_date == null ? null : $start_date);

        // Count events
        $item_query = $logger->build_query_args($related_object_id, $event_type, $meta, -1, 1);
        $item_query['fields'] = 'ids';
        $item_query['paged'] = 1;
        $item_query['posts_per_page'] = -1;
        if ($start_date != null || $end_date != null)
        {
            $start_tokens = explode('/', $start_date);
            $end_tokens = explode('/', $end_date);
            if (count($start_tokens) != 3)
            {
                $start_date = null;
            }

            if (count($end_tokens) != 3)
            {
                $end_date = null;
            }

            if ($start_date != null && $end_date != null)
            {
                $item_query['date_query'] = array(
                    array(
                        'after'     => array(
                            'year'  => $start_tokens[2],
                            'month' => $start_tokens[1],
                            'day'   => $start_tokens[0],
                        ),
                        'before'    => array(
                            'year'  => $end_tokens[2],
                            'month' => $end_tokens[1],
                            'day'   => $end_tokens[0],
                        ),
                        'inclusive' => true,
                    ),
                );
            }
        }
        $total_items = count(get_posts($item_query));

        // Number of events per screen
        $items_per_page = 25;

        // Current page Number
        $current_page = $this->get_pagenum();

        // Number of pages in total
        $page_count = ceil($total_items / $items_per_page);

        // Register the pagination
        $this->set_pagination_args(array(
            "total_items" => $total_items,
            "total_pages" => $page_count,
            "per_page"    => $items_per_page,
        ));

        // Fetch the items
        $item_query['fields'] = 'all';
        $item_query['paged'] = $current_page;
        $item_query['posts_per_page'] = $items_per_page;
        $this->items = $logger->query_events($item_query);
    }
}
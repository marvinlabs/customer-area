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

    public $post_type = null;
    public $plugin = null;

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
        $this->post_type = $post_type;
    }

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
            $posts = isset($_POST['posts']) ? $_POST['posts'] : array();
            if (empty($posts))
            {
                return;
            }

            foreach ($posts as $post_id)
            {
                if ( !current_user_can('delete_post', $post_id))
                {
                    wp_die(__('You are not allowed to delete this item.', 'cuar'));
                }
                wp_delete_post($post_id, true);
            }
        }
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', 'posts', $item->ID);
    }

    public function column_default($item, $column_name)
    {
        return 'Column not implemented yet';
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
        $title = get_the_title($item->ID);

        return sprintf('<a href="%1$s" title="Edit %2$s">%3$s</a>',
            admin_url('edit.php?post_type=' . $this->post_type . '&post_id=' . $item->ID),
            esc_attr($title),
            $title);
    }

    public function column_author($item)
    {
        $user = get_userdata($item->post_author);

        return sprintf('<a href="%1$s" title="Show content authored by %2$s" class="cuar-author">%3$s</a>',
            admin_url('admin.php?page=' . $this->post_type . '&author=' . $user->ID),
            esc_attr($user->display_name),
            $user->user_login);
    }

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

        // Retrieve filter values

        // Count events
        $args = array(
            'post_type' => $this->post_type
        );
        $args['fields'] = 'ids';
        $args['paged'] = 1;
        $args['posts_per_page'] = -1;

        $item_query = new WP_Query($args);
        $total_items = $item_query->post_count;

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
        $args['fields'] = 'all';
        $args['paged'] = $current_page;
        $args['posts_per_page'] = $items_per_page;

        $this->items = get_posts($args);
    }
}
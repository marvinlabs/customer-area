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

require_once(CUAR_INCLUDES_DIR . '/core-classes/Content/list-table.class.php');

/**
 * Class CUAR_PrivateContentTable
 *
 * List private content on the admin side
 *
 * @link http://plugins.svn.wordpress.org/custom-list-table-example/tags/1.3/list-table-example.php
 */
class CUAR_PrivateContentTable extends CUAR_ListTable
{
    /** @var CUAR_PostOwnerAddOn */
    public $po_addon = null;

    /** @var string The post type to be displayed by this table */
    public $post_type = null;

    /** @var object The post type object */
    public $post_type_object = null;

    /** @var array The taxonomies linked to this post type */
    public $associated_taxonomies = null;

    /** @var string The base URL for this table page */
    public $base_url = '';

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     *
     * @param CUAR_Plugin $plugin
     * @param array       $args
     * @param object      $post_type_object
     * @param array       $associated_taxonomies
     */
    public function __construct($plugin, $args, $post_type_object, $associated_taxonomies)
    {
        parent::__construct($plugin, $args, admin_url('admin.php?page=wpca-list,content,' . $post_type_object->name));
        $this->po_addon = $plugin->get_addon('post-owner');
        $this->post_type = $post_type_object->name;
        $this->post_type_object = $post_type_object;
        $this->associated_taxonomies = $associated_taxonomies;
    }

    /**
     * Read the parameters from the query and store them for later use
     *
     * @param array $form_data The form data
     */
    protected function parse_parameters($form_data)
    {
        parent::parse_parameters($form_data);

        $this->parameters['author'] = isset($form_data['author']) ? $form_data['author'] : 0;
        $this->parameters['search-field'] = isset($form_data['search-field']) ? $form_data['search-field'] : 'title';
        $this->parameters['search-query'] = isset($form_data['search-query']) ? $form_data['search-query'] : '';
        $this->parameters['visible-by'] = isset($form_data['visible-by']) ? $form_data['visible-by'] : 0;
        $this->parameters['start-date'] = isset($form_data['start-date'])
            ? sanitize_text_field($form_data['start-date']) : null;
        $this->parameters['end-date'] = isset($form_data['end-date']) ? sanitize_text_field($form_data['end-date'])
            : null;

        // These criterias are not compatible
        if ( !empty($this->parameters['search-query']) && $this->parameters['search-field'] == 'owner')
        {
            $this->parameters['visible-by'] = 0;
        }

        // If current user cannot list all posts, only show what belongs to him
        if ( !current_user_can($this->post_type_object->cap->read_private_posts))
        {
            $this->parameters['visible-by'] = get_current_user_id();
        }

        // Taxonomies
        foreach ($this->associated_taxonomies as $slug => $tax)
        {
            $this->parameters[$slug] = isset($form_data[$slug]) ? $form_data[$slug] : '';
        }

        $this->parameters = apply_filters('cuar/core/admin/content-list-table/search-parameters?post_type='
            . $this->post_type, $this->parameters, $this);
    }

    public function is_search_active()
    {
        $is_active = $this->parameters['author'] != 0
            || $this->parameters['visible-by'] != 0
            || !empty($this->parameters['search-query'])
            || !empty($this->parameters['start-date'])
            || !empty($this->parameters['end-date']);

        // Taxonomies
        foreach ($this->associated_taxonomies as $slug => $tax)
        {
            if ( !empty($this->parameters[$slug]))
            {
                $is_active = true;
                break;
            }
        }

        return apply_filters('cuar/core/admin/content-list-table/is-search-active?post_type=' . $this->post_type,
            $is_active, $this);
    }

    /**
     * Get the query parameters
     * @return array
     */
    protected function get_query_args()
    {
        $args = array(
            'post_type' => $this->post_type
        );

        if (empty($this->parameters['status']) || $this->parameters['status'] == 'any')
        {
            $statuses = array_diff(get_available_post_statuses(), array('trash'));
            $args['post_status'] = $statuses;
        }
        else
        {
            $args['post_status'] = $this->parameters['status'];
        }

        if ( !empty($this->parameters['author']))
        {
            $args['author'] = $this->parameters['author'];
        }

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

        // Taxonomies
        $args['tax_query'] = array();
        foreach ($this->associated_taxonomies as $slug => $tax)
        {
            if ( !empty($this->parameters[$slug]))
            {
                $args['tax_query'][] = array(
                    'taxonomy' => $slug,
                    'field'    => 'slug',
                    'terms'    => $this->parameters[$slug],
                );
            }
        }

        return apply_filters('cuar/core/admin/content-list-table/query-args?post_type=' . $this->post_type,
            $args, $this);
    }

    /*------- VIEWS --------------------------------------------------------------------------------------------------*/

    protected function get_view_statuses()
    {
        return apply_filters('cuar/core/admin/content-list-table/view_statuses?post_type=' . $this->post_type,
            array_merge(parent::get_view_statuses(), array(
                'publish' => __('Published', 'cuar'),
                'draft'   => __('Draft', 'cuar'),
                'trash'   => __('Trash', 'cuar')
            )), $this);
    }

    /**
     * Retrieve the view types
     * @return array $views All the views available
     */
    public function get_views()
    {
        return apply_filters('cuar/core/admin/content-list-table/views?post_type=' . $this->post_type,
            parent::get_views(), $this);
    }

    /*------- COLUMNS ------------------------------------------------------------------------------------------------*/

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $columns = array_merge(parent::get_columns(), array(
            'title'  => __('Title', 'cuar'),
            'author' => __('Author', 'cuar'),
        ));

        if ( !empty($this->associated_taxonomies))
        {
            foreach ($this->associated_taxonomies as $id => $tax)
            {
                $columns[$id] = $tax->labels->name;
            }
        }

        $columns['owner'] = __('Owner', 'cuar');
        $columns['date'] = __('Date', 'cuar');

        return apply_filters('cuar/core/admin/content-list-table/columns?post_type=' . $this->post_type,
            $columns, $this);
    }

    public function column_default($item, $column_name)
    {
        if ( !empty($this->associated_taxonomies) && isset($this->associated_taxonomies[$column_name]))
        {
            return $this->column_taxonomy($item, $column_name);
        }

        return apply_filters('cuar/core/admin/content-list-table/column-content?post_type=' . $this->post_type,
            'Not implemented yet', $item, $column_name, $this);
    }

    public function column_owner($item)
    {
        $owner_names = $this->po_addon->get_post_displayable_owners($item->ID, true);
        $out = $owner_names; // implode(', ', $owner_names);

        return apply_filters('cuar/core/admin/content-list-table/column-content?post_type=' . $this->post_type,
            $out, $item, 'owner', $this);
    }

    /*------- BULK ACTIONS -------------------------------------------------------------------------------------------*/

    /**
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Label'
     */
    public function get_bulk_actions()
    {
        $actions = parent::get_bulk_actions();

        return apply_filters('cuar/core/admin/content-list-table/bulk-actions?post_type=' . $this->post_type,
            $actions, $this);
    }

    /**
     * Execute a bulk action on a single post
     *
     * @param string $action
     * @param int    $post_id
     */
    protected function execute_action($action, $post_id)
    {
        parent::execute_action($action, $post_id);

        do_action('cuar/core/admin/content-list-table/do-bulk-action?post_type=' . $this->post_type,
            $post_id, $action, $this);
    }

    /**
     * @return bool true if the current user is allowed to delete items
     */
    protected function current_user_can_delete()
    {
        return current_user_can($this->post_type_object->cap->delete_post);
    }

    /**
     * @return array Get all posts in trash
     */
    protected function get_trashed_post_ids()
    {
        return get_posts(array(
            'fields'         => 'ids',
            'post_status'    => 'trash',
            'post_type'      => $this->post_type,
            'posts_per_page' => -1
        ));
    }
}
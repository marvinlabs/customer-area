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
 * Class CUAR_PaymentTable
 *
 * List payments on the admin side
 *
 * @link http://plugins.svn.wordpress.org/custom-list-table-example/tags/1.3/list-table-example.php
 */
class CUAR_PaymentTable extends CUAR_ListTable
{

    /** @var string The post type to be displayed by this table */
    public $post_type = null;

    /** @var object The post type object */
    public $post_type_object = null;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     *
     * @param CUAR_Plugin $plugin
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin,
            array(
                'singular' => __('Payment', 'cuar'),
                'plural'   => __('Payments', 'cuar'),
                'ajax'     => false
            ),
            admin_url('admin.php?page=wpca-payments'),
            'CUAR_Payment');

        $this->post_type = CUAR_Payment::$POST_TYPE;
        $this->post_type_object = get_post_type_object(CUAR_Payment::$POST_TYPE);

        add_filter('cuar/core/admin/content-list-table/row-actions', array(&$this, 'custom_row_actions'), 20, 2);
    }

    /**
     * Read the parameters from the query and store them for later use
     *
     * @param array $form_data The form data
     */
    protected function parse_parameters($form_data)
    {
        parent::parse_parameters($form_data);

        $this->parameters['status'] = isset($form_data['status']) ? $form_data['status'] : 'any';
        $this->parameters['related-object'] = isset($form_data['related-object']) ? $form_data['related-object'] : -1;
        $this->parameters['event-type'] = isset($form_data['event-type']) ? $form_data['event-type'] : 0;
        $this->parameters['start-date'] = isset($form_data['start-date']) ? sanitize_text_field($form_data['start-date']) : null;
        $this->parameters['end-date'] = isset($form_data['end-date']) ? sanitize_text_field($form_data['end-date']) : null;
    }

    /**
     * Returns true if any of our search parameters does not have a default value
     *
     * @return bool
     */
    public function is_search_active()
    {
        $is_active = !empty($this->parameters['start-date'])
            || !empty($this->parameters['end-date']);

        return $is_active;
    }

    /**
     * Get the query parameters
     * @return array
     */
    protected function get_query_args()
    {
        $args = array(
            'post_type'      => CUAR_Payment::$POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => $this->parameters['status'],
            'paged'          => 1,
            'date_query'     => array(),
        );

        if ($args['post_status'] === 'any') {
            $args['post_status'] = CUAR_PaymentStatus::get_payment_status_keys();
        }

        $start_date = $this->parameters['start-date'];
        if ($start_date != null) {
            $start_tokens = explode('/', $start_date);
            if (count($start_tokens) != 3) {
                $start_date = null;
            } else {
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
        if ($end_date != null) {
            $end_tokens = explode('/', $end_date);
            if (count($end_tokens) != 3) {
                $end_date = null;
            } else {
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

    /*------- COLUMNS ------------------------------------------------------------------------------------------------*/

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $columns = array_merge(parent::get_columns(), array(
            'title'          => __('Object', 'cuar'),
            'payment_user'   => __('User', 'cuar'),
            'payment_date'   => __('Date', 'cuar'),
            'payment_amount' => __('Amount', 'cuar'),
            'payment_status' => __('Status', 'cuar'),
        ));

        return $columns;
    }

    /**
     * @param CUAR_Payment $item
     *
     * @return string
     */
    public function column_payment_id($item)
    {
        return $item->ID;
    }

    /**
     * @param CUAR_Payment $item
     *
     * @return string
     */
    public function column_payment_title($item)
    {
        return get_the_title($item->get_post());
    }

    /**
     * @param CUAR_Payment $item
     *
     * @return string
     */
    public function column_payment_date($item)
    {
        return get_the_date('', $item->get_post());
    }

    /**
     * @param CUAR_Payment $item
     *
     * @return string
     */
    public function column_payment_status($item)
    {
        $s = $item->get_post()->post_status;
        $statuses = CUAR_PaymentStatus::get_payment_statuses();

        return isset($statuses[$s]) ? $statuses[$s] : $s;
    }

    /**
     * @param CUAR_Payment $item
     *
     * @return string
     */
    public function column_payment_user($item)
    {
        $user_id = $item->get_user_id();
        if ($user_id == 0) {
            return '';
        }

        $user = get_userdata($user_id);

        return sprintf('<a href="%1$s" title="Profile of %2$s" class="cuar-btn-xs user">%3$s</a><br>%4$s',
            admin_url('user-edit.php?user_id=' . $user_id),
            esc_attr($user->user_login),
            $user->display_name,
            $user->user_email);
    }

    /**
     * @param CUAR_Payment $item
     *
     * @return string
     */
    public function column_payment_amount($item)
    {
        return CUAR_CurrencyHelper::formatAmount($item->get_amount(), $item->get_currency());
    }

    /*------- VIEWS --------------------------------------------------------------------------------------------------*/

    protected function get_view_statuses()
    {
        return array_merge(parent::get_view_statuses(), CUAR_PaymentStatus::get_payment_statuses());
    }

    /*------- BULK ACTIONS -------------------------------------------------------------------------------------------*/

    public function custom_row_actions($row_actions, $item)
    {
        unset($row_actions['view']);
        unset($row_actions['trash']);
        unset($row_actions['untrash']);

        $row_actions['delete'] = sprintf('<a href="%1$s" title="%2$s this post">%2$s</a>',
            wp_nonce_url(add_query_arg(array('action' => 'delete', 'posts' => $item->ID), $this->base_url),
                'cuar_content_row_nonce'),
            __('Delete', 'cuar'));

        return $row_actions;
    }

    /**
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete permanently', 'cuar')
        );

        $statuses = CUAR_PaymentStatus::get_payment_statuses();
        foreach ($statuses as $s => $label) {
            $actions['set_status_' . $s] = sprintf(__('Mark payment as %s', 'cuar'), $label);
        }

        return $actions;
    }

    /**
     * Execute a bulk action on a single post
     *
     * @param string $action
     * @param int    $post_id
     */
    protected function execute_action($action, $post_id)
    {
        switch ($action) {
            case 'delete':
                if ( !current_user_can('delete_post', $post_id)) {
                    wp_die(__('You are not allowed to delete this item.', 'cuar'));
                }

                wp_delete_post($post_id, true);
                break;
        }

        // Status change
        $statuses = CUAR_PaymentStatus::get_payment_statuses();
        foreach ($statuses as $s => $label) {
            if ($action == 'set_status_' . $s) {
                $user = get_userdata(get_current_user_id());
                $payment = new CUAR_Payment($post_id);
                $payment->update_status($s, $user->user_login);
                break;
            }
        }
    }

    /**
     * @return bool true if the current user is allowed to delete items
     */
    protected function current_user_can_delete()
    {
        return current_user_can('cuar_pay_delete');
    }

}
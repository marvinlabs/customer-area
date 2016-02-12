<?php
/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

require_once(CUAR_INCLUDES_DIR . '/core-classes/Content/custom-post.class.php');

class CUAR_Payment extends CUAR_CustomPost
{
    public static $POST_TYPE = 'cuar_payment';

    public static $META_STARTED_BY = 'cuar_started_by';

    /**
     * Constructor
     *
     * @param WP_Post|int $custom_post
     * @param boolean     $load_post If we supply an int as the first argument, shall we load the post object?
     */
    public function __construct($custom_post, $load_post = true)
    {
        parent::__construct($custom_post, $load_post);
    }

    /**
     * Set the user ID who started the payment
     *
     * @param int $user_id
     *
     * @return int
     */
    public function set_started_by($user_id)
    {
        update_post_meta($this->ID, self::$META_STARTED_BY, $user_id);
    }

    /**
     * Get the user ID who started the payment
     *
     * @return int
     */
    public function get_started_by()
    {
        $user_id = get_post_meta($this->ID, self::$META_STARTED_BY, true);
        if ( !isset($user_id))
        {
            $user_id = $this->post->post_author;
            $this->set_started_by($user_id);
        }

        return $user_id;
    }


    /**
     * Register the custom post type
     */
    public static function register_post_type()
    {
        $labels = array(
            'name'               => _x('Payments', 'cuar_payment', 'cuar'),
            'singular_name'      => _x('Payment', 'cuar_payment', 'cuar'),
            'add_new'            => _x('Add New', 'cuar_payment', 'cuar'),
            'add_new_item'       => _x('Add New Payment', 'cuar_payment', 'cuar'),
            'edit_item'          => _x('Edit Payment', 'cuar_payment', 'cuar'),
            'new_item'           => _x('New Payment', 'cuar_payment', 'cuar'),
            'view_item'          => _x('View Payment', 'cuar_payment', 'cuar'),
            'search_items'       => _x('Search Payments', 'cuar_payment', 'cuar'),
            'not_found'          => _x('No payment found', 'cuar_payment', 'cuar'),
            'not_found_in_trash' => _x('No payment found in Trash', 'cuar_payment', 'cuar'),
            'parent_item_colon'  => _x('Parent Payment:', 'cuar_payment', 'cuar'),
            'menu_name'          => _x('Payments', 'cuar_payment', 'cuar'),
        );

        $args = array(
            'labels'              => $labels,
            'hierarchical'        => false,
            'supports'            => array('title', 'editor', 'author'),
            'taxonomies'          => array(),
            'public'              => true,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'publicly_queryable'  => true,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'query_var'           => self::$POST_TYPE,
            'can_export'          => false,
            'rewrite'             => false,
            'capabilities'        => array(
                'edit_post'          => 'cuar_pay_edit',
                'edit_posts'         => 'cuar_pay_edit',
                'edit_others_posts'  => 'cuar_pay_edit',
                'publish_posts'      => 'cuar_pay_edit',
                'read_post'          => 'cuar_pay_read',
                'read_private_posts' => 'cuar_pay_list_all',
                'delete_post'        => 'cuar_pay_delete',
                'delete_posts'       => 'cuar_pay_delete'
            )
        );

        register_post_type(self::$POST_TYPE, apply_filters('cuar/private-content/payments/register-post-type-args', $args));
    }

}
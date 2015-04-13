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

class CUAR_LogEvent extends CUAR_CustomPost
{

    /** @var string The post type corresponding to a log event */
    public static $POST_TYPE = 'cuar_log_event';

    private $type = 0xdead;

    /**
     * Constructor
     *
     * @param int|WP_Post $custom_post
     * @param bool        $load_post
     */
    public function __construct($custom_post, $load_post = true)
    {
        parent::__construct($custom_post, $load_post);
    }

    /**
     * @return WP_Term[]
     */
    public function get_type()
    {
        if ($this->type === 0xdead)
        {
            $terms = wp_get_post_terms($this->ID, CUAR_LogEventType::$TAXONOMY);
            if (empty($terms) || is_wp_error($terms))
            {
                $this->type = null;
            }
            else
            {
                $this->type = $terms[0]->slug;
            }
        }

        return $this->type;
    }

    /**
     * Register post types and custom taxonomies
     */
    public static function register_custom_types()
    {
        $log_args = array(
            'labels'              => array('name' => __('Log events', 'cuar')),
            'public'              => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => false,
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'supports'            => array('title', 'editor'),
            'can_export'          => true
        );

        register_post_type(self::$POST_TYPE, $log_args);
    }

    /**
     * Create a new log event
     *
     * This is just a simple and fast way to log something. Use $this->insert_log()
     * if you need to store custom meta data
     *
     * @param string $type              Event type
     * @param string $title             Event title
     * @param string $message           Event message
     * @param int    $related_object_id The related object ID (post, term, user, ...)
     * @param array  $log_meta          Optional array of meta data (key, value)
     *
     * @return int|WP_Error Log ID or error
     */
    public static function create($type, $title = '', $message = '', $related_object_id = 0,
        $related_object_type = null, $log_meta = array())
    {
        $event_data = array(
            'post_status'  => 'publish',
            'post_type'    => self::$POST_TYPE,
            'post_title'   => $title,
            'post_content' => $message,
            'post_parent'  => $related_object_id
        );

        // Store the log entry
        $log_id = wp_insert_post($event_data);
        if (is_wp_error($log_id))
        {
            return $log_id;
        }

        // Set the log type, if any
        wp_set_object_terms($log_id, $type, CUAR_LogEventType::$TAXONOMY, false);

        if ($related_object_type != null)
        {
            $log_meta['related_object_type'] = $related_object_type;
        }

        // Set log meta, if any
        if ($log_id && is_array($log_meta) && !empty($log_meta))
        {
            foreach ($log_meta as $key => $meta)
            {
                update_post_meta($log_id, 'cuar_' . sanitize_key($key), $meta);
            }
        }

        return $log_id;
    }

}
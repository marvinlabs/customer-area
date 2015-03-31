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

class CUAR_Logger
{

    /** @var array The valid event types */
    private $valid_types = null;

    /**
     * Constructor
     */
    function __construct()
    {
    }

    /**
     * Hook into WP
     */
    public function register_hooks()
    {
        add_action('init', array('CUAR_LogEvent', 'register_custom_types'), 5);
        add_action('init', array('CUAR_LogEventType', 'register_custom_types'), 6);
    }

    /**
     * Get the types of log event available
     * @return  array $types
     */
    public function get_valid_event_types()
    {
        if ($this->valid_types)
        {
            $default_types = array();
            $this->valid_types = apply_filters('cuar/core/log/event-types', $default_types);
        }

        return $this->valid_types;
    }

    /**
     * Tests if a log event type exists
     *
     * @param string $type
     *
     * @return bool
     */
    public function is_event_type_valid($type)
    {
        return in_array($type, $this->get_valid_event_types());
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
     * @return int|WP_Error Log ID or WP_Error if an error occurred
     */
    public function log_event($type, $title = '', $message = '', $related_object_id = 0, $log_meta = array())
    {
        if ( !$this->is_event_type_valid($type))
        {
            return new WP_Error(0, "Log type is not valid");
        }

        $log_id = CUAR_LogEvent::create($type, $title, $message, $related_object_id, $log_meta);

        if ( !is_wp_error($log_id))
        {
            do_action('cuar/core/log/on-log-event', $type, $title, $message, $related_object_id, $log_meta);
        }

        return $log_id;
    }

    /**
     * Delete events related to a type, an object, ...
     *
     * @param int    $related_object_id (default: 0)
     * @param string $type              Log type (default: null)
     * @param array  $meta_query        Log meta query (default: null)
     *
     * @return void
     */
    public function delete_events($related_object_id = 0, $type = null, $meta_query = null)
    {
        $query_args = array(
            'post_parent'    => $related_object_id,
            'post_type'      => CUAR_LogEvent::$POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids'
        );

        if ( !empty($type) && $this->is_event_type_valid($type))
        {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => CUAR_LogEventType::$TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $type,
                )
            );
        }

        if ( !empty($meta_query))
        {
            $query_args['meta_query'] = $meta_query;
        }

        $logs = get_posts($query_args);

        if ($logs)
        {
            foreach ($logs as $log)
            {
                wp_delete_post($log, true);
            }
        }
    }

    /**
     * Get events related to a type, an object, ...
     *
     * @param int    $related_object_id
     * @param string $type       Log type
     * @param array  $meta_query Log meta query
     * @param int    $max_events Max number of events to return
     * @param int    $paged      Page number
     *
     * @return WP_Post[]
     */
    public function get_events($related_object_id = 0, $type = null, $meta_query = null, $max_events = -1,
        $paged = null)
    {
        $query_args = array(
            'post_parent'    => $related_object_id,
            'post_type'      => CUAR_LogEvent::$POST_TYPE,
            'posts_per_page' => $max_events,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'paged'          => $paged
        );

        if ( !empty($type) && $this->is_event_type_valid($type))
        {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => CUAR_LogEventType::$TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $type,
                )
            );
        }

        if ( !empty($meta_query))
        {
            $query_args['meta_query'] = $meta_query;
        }

        return get_posts($query_args);
    }
}
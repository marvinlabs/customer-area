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
        $this->register_hooks();
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
     *
     * @param bool $force_refresh Set to true if we must rebuild the event types array (useful to get
     *                            translated strings)
     *
     * @return array $types
     */
    public function get_valid_event_types($force_refresh = false)
    {
        if ($this->valid_types == null || $force_refresh)
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
        return array_key_exists($type, $this->get_valid_event_types());
    }

    /**
     * Create a new log event
     *
     * This is just a simple and fast way to log something. Use $this->insert_log()
     * if you need to store custom meta data
     *
     * @param string $type                Event type
     * @param int    $related_object_id   The related object ID (post, term, user, ...)
     * @param string $related_object_type The type of related object (post, term, user, ...)
     * @param array  $log_meta            Optional array of meta data (key, value)
     * @param string $title               Event title
     * @param string $message             Event message
     *
     * @return int|WP_Error Log ID or WP_Error if an error occurred
     */
    public function log_event($type, $related_object_id = -1, $related_object_type = null, $log_meta = array(),
        $title = '', $message = '')
    {
        if ( !$this->is_event_type_valid($type))
        {
            return new WP_Error(0, "Log type is not valid");
        }

        $log_id = CUAR_LogEvent::create($type, $title, $message, $related_object_id, $related_object_type, $log_meta);

        if ( !is_wp_error($log_id))
        {
            do_action('cuar/core/log/on-log-event', $type, $title, $message, $related_object_id, $related_object_type,
                $log_meta);
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
    public function get_events($related_object_id = -1, $type = null, $meta_query = null, $max_events = -1,
        $paged = null)
    {
        $query_args = $this->build_query_args($related_object_id, $type, $meta_query, $max_events, $paged);
        $posts = get_posts($query_args);
        $logs = array();
        foreach ($posts as $p)
        {
            $logs[] = new CUAR_LogEvent($p);
        }

        return $logs;
    }

    public function query_events($query_args)
    {
        $posts = get_posts($query_args);
        $logs = array();
        foreach ($posts as $p)
        {
            $logs[] = new CUAR_LogEvent($p);
        }

        return $logs;
    }

    public function count_events($related_object_id = -1, $type = null, $meta_query = null)
    {
        $query_args = $this->build_query_args($related_object_id, $type, $meta_query, -1, 1);
        $query_args['fields'] = 'ids';
        $query_args['paged'] = 1;
        $query_args['posts_per_page'] = -1;

        $ids = get_posts($query_args);

        return count($ids);
    }

    /**
     * @param $related_object_id
     * @param $type
     * @param $meta_query
     * @param $max_events
     * @param $paged
     *
     * @return array
     */
    public function build_query_args($related_object_id, $type, $meta_query, $max_events, $paged)
    {
        $query_args = array(
            'post_type'      => CUAR_LogEvent::$POST_TYPE,
            'posts_per_page' => $max_events,
            'post_status'    => 'publish',
            'paged'          => $paged
        );

        if ($related_object_id >= 0)
        {
            $query_args['post_parent'] = $related_object_id;
        }

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

            foreach ($meta_query as $i => $arg)
            {
                if (is_array($arg))
                {
                    $new_arg = array_merge_recursive($arg);
                    foreach ($arg as $k => $v)
                    {
                        if ($k == 'key' && false == strstr($v, 'cuar_'))
                        {
                            $new_arg[$k] = 'cuar_' . $v;
                        }
                    }
                    $query_args['meta_query'][$i] = $new_arg;
                } else
                {
                    $query_args['meta_query'][$i] = $arg;
                }
            }

            return $query_args;
        }

        return $query_args;
    }
}
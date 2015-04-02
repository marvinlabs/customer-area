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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon.class.php');

if ( !class_exists('CUAR_LogAddOn')) :

    /**
     * Add-on to provide all the stuff required to set an owner on a post type and include that post type in the
     * customer area.
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_LogAddOn extends CUAR_AddOn
    {

        /** @var CUAR_Logger The logger object */
        private $logger = null;

        public function __construct()
        {
            parent::__construct('log', '6.0.0');
        }

        public function get_addon_name()
        {
            return __('Log', 'cuar');
        }

        public function run_addon($plugin)
        {
            $this->logger = $plugin->get_logger();

            // Init the admin interface if needed
            if (is_admin())
            {
                add_action('cuar/core/admin/main-menu-pages', array(&$this, 'add_menu_items'), 99);
                add_action('cuar/core/admin/adminbar-menu-items', array(&$this, 'add_adminbar_menu_items'), 100);

                add_filter('cuar/core/log/table-cell-content', array(&$this, 'get_log_cell_content'), 10, 3);
            }
            else
            {
            }

            add_filter('cuar/core/log/event-types', array(&$this, 'add_default_event_types'));

            // Content viewed
            add_action('cuar/core/ownership/protect-single-post/on-access-granted',
                array(&$this, 'log_content_viewed'));

            // File downloaded
            add_action('cuar/private-content/files/on-download', array(&$this, 'log_file_downloaded'), 10, 3);
            add_action('cuar/private-content/files/on-view', array(&$this, 'log_file_downloaded'), 10, 3);
        }

        /*------- ADMIN PAGE -----------------------------------------------------------------------------------------*/

        private static $LOG_PAGE_SLUG = "cuar_logs";

        /**
         * Add the menu item
         */
        public function add_menu_items($submenus)
        {
            $separator = '<span class="cuar-menu-divider"></span>';

            $submenus[] = array(
                'page_title' => __('WP Customer Area - Logs', 'cuar'),
                'title'      => $separator . __('Logs', 'cuar'),
                'slug'       => self::$LOG_PAGE_SLUG,
                'function'   => array(&$this, 'print_logs_page'),
                'capability' => 'manage_options'
            );

            return $submenus;
        }

        /**
         * Add the menu item
         */
        public function add_adminbar_menu_items($submenus)
        {
            if (current_user_can('manage_options'))
            {
                $submenus[] = array(
                    'parent' => 'customer-area',
                    'id'     => 'customer-area-logs',
                    'title'  => __('Logs', 'cuar'),
                    'href'   => admin_url('admin.php?page=' . self::$LOG_PAGE_SLUG)
                );
            }

            return $submenus;
        }

        /**
         * Display the main logs page
         */
        public function print_logs_page()
        {
            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/log',
                'logs-page.template.php',
                'templates'));
        }

        /*------- LOGGING HANDLERS -----------------------------------------------------------------------------------*/

        /**
         * Add the event types we are currently supporting to the main array
         *
         * @param array $default_types the currently available types
         *
         * @return array
         */
        public function add_default_event_types($default_types)
        {
            return array_merge($default_types, array(
                'cuar-content-viewed' => __('Private content viewed', 'cuar'),
                'cuar-file-download'  => __('Private file downloaded', 'cuar')
            ));
        }

        /**
         * Log a view for private content
         *
         * @param $post
         */
        public function log_content_viewed($post)
        {
            $this->logger->log_event('cuar-content-viewed',
                $post->ID,
                $post->post_type,
                array(
                    'user_id' => get_current_user_id(),
                    'ip'      => $_SERVER['REMOTE_ADDR']
                ));
        }

        /**
         * Log a download for private file
         *
         * @param $post_id
         * @param $current_user_id
         * @param $pf_addon
         */
        public function log_file_downloaded($post_id, $current_user_id, $pf_addon)
        {
            $this->logger->log_event('cuar-file-download',
                $post_id,
                get_post_type($post_id),
                array(
                    'user_id' => get_current_user_id(),
                    'ip'      => $_SERVER['REMOTE_ADDR']
                ));
        }

        /*------- LOG VIEWER -----------------------------------------------------------------------------------------*/

        /**
         * @param string        $content
         * @param string        $column_name
         * @param CUAR_LogEvent $item
         *
         * @return string
         */
        public function get_log_cell_content($content, $column_name, $item)
        {
            switch ($column_name)
            {
                case 'log_extra':
                    return $this->get_log_extra_cell($item);

                case 'log_description':
                    return $this->get_log_description_cell($item);
            }

            return $content;
        }

        /**
         * @param CUAR_LogEvent $item
         *
         * @return string
         */
        private function get_log_description_cell($item)
        {
            $type = $item->get_type();
            $rel_object_id = $item->get_post()->post_parent;
            $rel_object_type = $item->related_object_type;

            if ($type == 'cuar-content-viewed' || $type == 'cuar-file-download')
            {
                switch ($type)
                {
                    case 'cuar-content-viewed':
                        $format_str = __('<a href="%1$s" title="Title: %2$s">%3$s</a> has been viewed by <a href="%4$s" title="Profile of %5$s">%6$s</a>',
                            'cuar');
                        break;
                    case   'cuar-file-download':
                        $format_str = __('<a href="%1$s" title="Title: %2$s">%3$s</a> has been downloaded by <a href="%4$s" title="Profile of %5$s">%6$s</a>',
                            'cuar');
                        break;
                    default:
                        $format_str = __('<a href="%1$s" title="Title: %2$s">%3$s</a> ???? by <a href="%4$s" title="Profile of %5$s">%6$s</a>',
                            'cuar');
                }

                $content_types = array_merge($this->plugin->get_content_types(),
                    $this->plugin->get_container_types());

                $obj_link_text = isset($content_types[$rel_object_type])
                    ? $content_types[$rel_object_type]['label-singular']
                    : $rel_object_type;
                $obj_link_text .= ' ' . $rel_object_id;

                $user_id = $item->user_id;
                $user = get_userdata($user_id);

                return sprintf($format_str,
                    admin_url('edit.php?post_type=' . $rel_object_type . '&post_id=' . $rel_object_id),
                    esc_attr(get_the_title($rel_object_id)),
                    $obj_link_text,
                    admin_url('user-edit.php?user_id=' . $user_id),
                    esc_attr($user->display_name),
                    $user->user_login
                );
            }
        }

        /**
         * @param CUAR_LogEvent $item
         *
         * @return string
         */
        private function get_log_extra_cell($item)
        {
            $type = $item->get_type();

            $fields = array();
            if ($type == 'cuar-content-viewed' || $type == 'cuar-file-download')
            {
                $fields[] = sprintf('<span title="%1$s" class="cuar-btn-xs %3$s">%2$s</span>', __('IP address', 'cuar'), esc_attr($item->ip), 'ip', '#');
            }

            return implode(' ', $fields);
        }
    }

    // Make sure the addon is loaded
    new CUAR_LogAddOn();

endif; // if (!class_exists('CUAR_LogAddOn'))

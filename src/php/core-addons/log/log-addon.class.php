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
        public static $TYPE_CONTENT_VIEWED = 'cuar-content-viewed';
        public static $TYPE_FILE_DOWNLOADED = 'cuar-file-download';
        public static $TYPE_OWNER_CHANGED = 'cuar-owner-changed';
        public static $TYPE_FILE_ATTACHMENT_ADDED = 'cuar_file_attachment_added';
        public static $TYPE_FILE_ATTACHMENT_REMOVED = 'cuar_file_attachment_removed';
        public static $TYPE_FILE_ATTACHMENT_UPDATED = 'cuar_file_attachment_updated';
        public static $TYPE_LOGIN = 'cuar_user_login';

        public static $META_USER_ID = 'user_id';
        public static $META_IP = 'ip';
        public static $META_PREVIOUS_OWNER = 'previous_owner';
        public static $META_CURRENT_OWNER = 'current_owner';
        public static $META_FILE_ATTACHMENT = 'attachment';
        public static $META_FILE_ID = 'attachment_id';

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
                // Menu
                add_action('cuar/core/admin/print-admin-page?page=logs', array(&$this, 'print_logs_page'), 99);

                // Log table handling
                add_filter('cuar/core/log/table-displayable-meta', array(&$this, 'get_table_displayable_meta'), 10, 1);
                add_filter('cuar/core/log/table-meta-pill-descriptor', array(&$this, 'get_table_meta_pill'), 10, 3);

                // Settings
                add_action('cuar/core/settings/print-settings?tab=cuar_core', array(&$this, 'print_core_settings'), 20, 2);
                add_filter('cuar/core/settings/validate-settings?tab=cuar_core', array(&$this, 'validate_core_options'), 20, 3);
            }

            add_action('cuar/core/admin/submenu-items?group=tools', array(&$this, 'add_menu_items'), 99);

            // User login
            add_action('wp_login', array(&$this, 'log_user_login'), 10, 2);

            // Add some event types by default
            add_filter('cuar/core/log/event-types', array(&$this, 'add_default_event_types'));

            // Owner changed
            add_action('cuar/core/ownership/after-save-owner', array(&$this, 'log_owner_updated'), 10, 4);

            // Content viewed
            add_action('cuar/core/ownership/protect-single-post/on-access-granted', array(&$this, 'log_content_viewed'));

            // File downloaded
            add_action('cuar/private-content/files/on-download', array(&$this, 'log_file_downloaded'), 10, 4);
            add_action('cuar/private-content/files/on-view', array(&$this, 'log_file_downloaded'), 10, 4);

            // File attachment operations
            add_action('cuar/private-content/files/on-add-attachment', array(&$this, 'log_add_file_attachment'), 10, 2);
            add_action('cuar/private-content/files/on-remove-attachment', array(&$this, 'log_remove_file_attachment'), 10, 2);
            add_action('cuar/private-content/files/on-update-attachment', array(&$this, 'log_update_file_attachment'), 10, 2);

            add_action("load-post-new.php", array(&$this, 'block_default_admin_pages'));
            add_action("load-edit.php", array(&$this, 'block_default_admin_pages'));
        }

        /*------- ADMIN PAGE -----------------------------------------------------------------------------------------*/

        private static $LOG_PAGE_SLUG = "wpca-logs";

        /**
         * Protect the default edition and listing pages
         */
        public function block_default_admin_pages()
        {
            if (isset($_GET["post_type"]) && $_GET["post_type"] == "cuar_log_event")
            {
                wp_redirect(admin_url("admin.php?page=" . self::$LOG_PAGE_SLUG));
            }
        }

        /**
         * Add the menu item
         */
        public function add_menu_items($submenus)
        {
            $submenus[] = array(
                'page_title' => __('WP Customer Area - Logs', 'cuar'),
                'title'      => __('Logs', 'cuar'),
                'slug'       => self::$LOG_PAGE_SLUG,
                'href'       => 'admin.php?page=' . self::$LOG_PAGE_SLUG,
                'capability' => 'manage_options'
            );

            return $submenus;
        }

        /**
         * Display the main logs page
         */
        public function print_logs_page()
        {
            require_once(CUAR_INCLUDES_DIR . '/core-addons/log/log-table.class.php');
            $logs_table = new CUAR_LogTable($this->plugin);
            $logs_table->initialize();

            /** @noinspection PhpIncludeInspection */
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
                self::$TYPE_LOGIN                   => __('Login', 'cuar'),
                self::$TYPE_CONTENT_VIEWED          => __('Content viewed', 'cuar'),
                self::$TYPE_FILE_DOWNLOADED         => __('File downloaded', 'cuar'),
                self::$TYPE_OWNER_CHANGED           => __('Owner changed', 'cuar'),
                self::$TYPE_FILE_ATTACHMENT_ADDED   => __('Attachment added', 'cuar'),
                self::$TYPE_FILE_ATTACHMENT_REMOVED => __('Attachment removed', 'cuar'),
                self::$TYPE_FILE_ATTACHMENT_UPDATED => __('Attachment updated', 'cuar')
            ));
        }

        /**
         * Log a successful login
         *
         * @param string  $username
         * @param WP_User $user
         */
        public function log_user_login($username, $user)
        {
            $should_log_event = true;
            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_LOGIN, $should_log_event, $user);
            if ($should_log_event)
            {
                $this->logger->log_event(self::$TYPE_LOGIN,
                    $user->ID,
                    'WP_User',
                    $this->get_default_event_meta());
            }
        }

        /**
         * Log a view for private content
         *
         * @param $post
         */
        public function log_content_viewed($post)
        {
            $should_log_event = true;
            $log_only_once = $this->only_log_first_view();
            if ($log_only_once)
            {
                $count = $this->logger->count_events($post->ID, self::$TYPE_CONTENT_VIEWED,
                    array(
                        array(
                            'key'     => self::$META_USER_ID,
                            'value'   => get_current_user_id(),
                            'compare' => '='
                        )
                    ));
                if ($count >= 1)
                {
                    $should_log_event = false;
                }
            }

            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_CONTENT_VIEWED,
                $should_log_event, $post);
            if ($should_log_event)
            {
                $this->logger->log_event(self::$TYPE_CONTENT_VIEWED,
                    $post->ID,
                    $post->post_type,
                    $this->get_default_event_meta());
            }
        }

        /**
         * Log a download for private file
         *
         * @param $post_id
         * @param $current_user_id
         * @param $pf_addon
         * @param $file_id
         */
        public function log_file_downloaded($post_id, $current_user_id, $pf_addon, $file_id)
        {
            $should_log_event = true;
            $log_only_once = $this->only_log_first_download();
            if ($log_only_once)
            {
                $count = $this->logger->count_events($post_id, self::$TYPE_FILE_DOWNLOADED,
                    array(
                        array(
                            'key'     => self::$META_USER_ID,
                            'value'   => get_current_user_id(),
                            'compare' => '='
                        ),
                        array(
                            'key'     => self::$META_FILE_ID,
                            'value'   => $file_id,
                            'compare' => '='
                        )
                    ));
                if ($count >= 1)
                {
                    $should_log_event = false;
                }
            }

            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_FILE_DOWNLOADED,
                $should_log_event, $post_id, $current_user_id, $pf_addon);
            if ($should_log_event)
            {
                $this->logger->log_event(self::$TYPE_FILE_DOWNLOADED,
                    $post_id,
                    get_post_type($post_id),
                    array_merge($this->get_default_event_meta(), array(
                        self::$META_FILE_ID => $file_id,
                    )));
            }
        }

        /**
         * Log an event when the post owner changes
         *
         * @param $post_id
         * @param $new_owners
         * @param $previous_owners
         */
        public function log_owner_updated($post_id, $post, $previous_owners, $new_owners)
        {
            // Compare previous and new, log only if actually changed
            $is_same = array_diff_key($previous_owners, $new_owners);
            $is_same = empty($is_same);
            if ($is_same)
            {
                foreach ($previous_owners as $prev_type => $prev_ids)
                {
                    $same_ids = array_diff($prev_ids, $new_owners[$prev_type]);
                    if (!empty($same_ids)) {
                        $is_same = false;
                        break;
                    }
                }
            }
            if ($is_same) return;

            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_OWNER_CHANGED,
                true, $post_id, $post, $previous_owners, $new_owners);
            if ($should_log_event)
            {
                // unhook this function so it doesn't loop infinitely
                remove_action('cuar/core/ownership/after-save-owner', array(&$this, 'log_owner_updated'), 10, 4);

                $this->logger->log_event(self::$TYPE_OWNER_CHANGED,
                    $post_id,
                    get_post_type($post_id),
                    array_merge($this->get_default_event_meta(), array(
                        self::$META_PREVIOUS_OWNER => $previous_owners,
                        self::$META_CURRENT_OWNER  => $new_owners
                    )));

                // re-hook this function
                add_action('cuar/core/ownership/after-save-owner', array(&$this, 'log_owner_updated'), 10, 4);
            }
        }

        public function log_add_file_attachment($post_id, $file)
        {
            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_FILE_ATTACHMENT_ADDED, true, $post_id, $file);
            if ($should_log_event)
            {
                $this->logger->log_event(self::$TYPE_FILE_ATTACHMENT_ADDED,
                    $post_id,
                    get_post_type($post_id),
                    array_merge($this->get_default_event_meta(), array(
                        self::$META_FILE_ATTACHMENT => $file
                    )));
            }
        }

        public function log_remove_file_attachment($post_id, $file)
        {
            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_FILE_ATTACHMENT_REMOVED, true, $post_id, $file);
            if ($should_log_event)
            {
                $this->logger->log_event(self::$TYPE_FILE_ATTACHMENT_REMOVED,
                    $post_id,
                    get_post_type($post_id),
                    array_merge($this->get_default_event_meta(), array(
                        self::$META_FILE_ATTACHMENT => $file
                    )));
            }
        }

        public function log_update_file_attachment($post_id, $file)
        {
            $should_log_event = apply_filters('cuar/core/log/should-log-event?event=' . self::$TYPE_FILE_ATTACHMENT_UPDATED, true, $post_id, $file);
            if ($should_log_event)
            {
                $this->logger->log_event(self::$TYPE_FILE_ATTACHMENT_UPDATED,
                    $post_id,
                    get_post_type($post_id),
                    array_merge($this->get_default_event_meta(), array(
                        self::$META_FILE_ATTACHMENT => $file
                    )));
            }
        }

        /**
         * @return array
         */
        public function get_default_event_meta()
        {
            return array(
                self::$META_USER_ID => get_current_user_id(),
                self::$META_IP      => $_SERVER['REMOTE_ADDR']
            );
        }

        /*------- LOG VIEWER -----------------------------------------------------------------------------------------*/

        public function get_table_displayable_meta($meta)
        {
            return array_merge($meta, array(
                self::$META_USER_ID,
                self::$META_IP,
                self::$META_PREVIOUS_OWNER,
                self::$META_CURRENT_OWNER,
                self::$META_FILE_ATTACHMENT,
                self::$META_FILE_ID
            ));
        }

        public function get_table_meta_pill($pill, $meta, $item)
        {
            switch ($meta)
            {
                case self::$META_IP:
                    $pill['title'] = __('IP address', 'cuar');
                    $pill['value'] = $item->$meta;
                    break;

                case self::$META_PREVIOUS_OWNER:
                    /** @var CUAR_PostOwnerAddOn $po_addon */
                    $po_addon = $this->plugin->get_addon('post-owner');

                    $owners = $item->$meta;
                    $dn = empty($owners) ? __('Nobody', 'cuar') : $po_addon->get_displayable_owners_for_log(0, $owners);

                    $pill['title'] = __('Previous owner: ', 'cuar') . $dn;
                    $pill['value'] = __('From: ', 'cuar') . substr($dn, 0, 35);
                    break;

                case self::$META_CURRENT_OWNER:
                    /** @var CUAR_PostOwnerAddOn $po_addon */
                    $po_addon = $this->plugin->get_addon('post-owner');

                    $owners = $item->$meta;
                    $dn = empty($owners) ? __('Nobody', 'cuar') : $po_addon->get_displayable_owners_for_log(0, $owners);

                    $pill['title'] = __('Current owner: ', 'cuar') . $dn;
                    $pill['value'] = __('To: ', 'cuar') . substr($dn, 0, 35);
                    break;

                case self::$META_FILE_ATTACHMENT:
                    $o = $item->$meta;
                    $pill['title'] = __('Caption: ', 'cuar') . $o['caption'];
                    $pill['value'] = __('Attachment: ', 'cuar') . $o['file'];
                    break;

                case self::$META_FILE_ID:
                    $o = $item->$meta;
                    /** @var CUAR_PrivateFileAddOn $pf_addon */
                    $pf_addon = $this->plugin->get_addon('private-files');
                    $f = $pf_addon->get_attached_file($item->post->post_parent, $o);
                    $pill['title'] = __('Caption: ', 'cuar') . $f['caption'];
                    $pill['value'] = __('Attachment: ', 'cuar') . $f['file'];
                    break;
            }

            return $pill;
        }

        /*------- SETTINGS -------------------------------------------------------------------------------------------*/


        public function only_log_first_view()
        {
            return $this->plugin->get_option(self::$OPTION_LOG_ONLY_FIRST_VIEW) == true;
        }

        public function only_log_first_download()
        {
            return $this->plugin->get_option(self::$OPTION_LOG_ONLY_FIRST_DOWNLOAD) == true;
        }

        /**
         * Set the default values for the options
         *
         * @param array $defaults
         *
         * @return array
         */
        public function set_default_options($defaults)
        {
            $defaults = parent::set_default_options($defaults);
            $defaults[self::$OPTION_LOG_ONLY_FIRST_VIEW] = true;
            $defaults[self::$OPTION_LOG_ONLY_FIRST_DOWNLOAD] = true;

            return $defaults;
        }

        /**
         * Add our fields to the settings page
         *
         * @param CUAR_Settings $cuar_settings The settings class
         */
        public function print_core_settings($cuar_settings, $options_group)
        {
            add_settings_section(
                'cuar_logs',
                __('Logs', 'cuar'),
                array(&$cuar_settings, 'print_empty_section_info'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG
            );

            add_settings_field(
                self::$OPTION_LOG_ONLY_FIRST_VIEW,
                __('Content viewed', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_logs',
                array(
                    'option_id' => self::$OPTION_LOG_ONLY_FIRST_VIEW,
                    'type'      => 'checkbox',
                    'after'     => __('Only log the first time a private content is viewed by a user.', 'cuar')
                        . '<p class="description">'
                        . __('If you check this box, WP Customer Area will only generate a log event the first time a user is viewing private content. There will be a single event per user and per content. Else, each time a user is viewing a private content, an event will be generated.',
                            'cuar')
                        . '</p>'
                )
            );

            add_settings_field(
                self::$OPTION_LOG_ONLY_FIRST_DOWNLOAD,
                __('File downloaded', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_logs',
                array(
                    'option_id' => self::$OPTION_LOG_ONLY_FIRST_DOWNLOAD,
                    'type'      => 'checkbox',
                    'after'     => __('Only log the first time a private file is downloaded by a user.', 'cuar')
                        . '<p class="description">'
                        . __('If you check this box, WP Customer Area will only generate a log event the first time a user is downloading a private file. There will be a single event per user and per file. Else, each time a user is downloading a private file, an event will be generated.',
                            'cuar')
                        . '</p>'
                )
            );
        }

        /**
         * Validate our options
         *
         * @param array         $validated
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         *
         * @return array
         */
        public function validate_core_options($validated, $cuar_settings, $input)
        {
            $cuar_settings->validate_boolean($input, $validated, self::$OPTION_LOG_ONLY_FIRST_VIEW);
            $cuar_settings->validate_boolean($input, $validated, self::$OPTION_LOG_ONLY_FIRST_DOWNLOAD);

            return $validated;
        }

        private static $OPTION_LOG_ONLY_FIRST_VIEW = 'cuar_log_view_first_only';
        private static $OPTION_LOG_ONLY_FIRST_DOWNLOAD = 'cuar_log_download_first_only';
    }

    // Make sure the addon is loaded
    new CUAR_LogAddOn();

endif; // if (!class_exists('CUAR_LogAddOn'))

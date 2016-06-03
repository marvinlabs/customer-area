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

require_once(dirname(__FILE__) . '/private-file-admin-interface.class.php');
require_once(dirname(__FILE__) . '/private-file-default-handlers.class.php');

if ( !class_exists('CUAR_PrivateFileAddOn')) :

    /**
     * Add-on to put private files in the customer area
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_PrivateFileAddOn extends CUAR_AddOn
    {

        private $default_handlers;

        public function __construct()
        {
            parent::__construct('private-files', '4.0.0');
        }

        public function get_addon_name()
        {
            return __('Private Files', 'cuar');
        }

        public function run_addon($plugin)
        {
            if ($this->is_enabled())
            {
                add_action('init', array(&$this, 'register_custom_types'));
                add_filter('cuar/core/post-types/content', array(&$this, 'register_private_post_types'));
                add_filter('cuar/core/types/content', array(&$this, 'register_content_type'));

                add_filter('cuar/core/ownership/base-private-storage-directory', array(&$this, 'filter_storage_path'));

                add_action('template_redirect', array(&$this, 'handle_file_actions'));
                add_action('before_delete_post', array(&$this, 'before_post_deleted'));

                add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'));

                add_action('wp_ajax_cuar_remove_attached_file', array(&$this, 'ajax_remove_attached_file'));
                add_action('wp_ajax_nopriv_cuar_remove_attached_file', array(&$this, 'ajax_remove_attached_file'));

                add_action('wp_ajax_cuar_attach_file', array(&$this, 'ajax_attach_file'));
                add_action('wp_ajax_nopriv_cuar_attach_file', array(&$this, 'ajax_attach_file'));

                add_action('wp_ajax_cuar_update_attached_file', array(&$this, 'ajax_update_attached_file_meta'));
                add_action('wp_ajax_nopriv_cuar_update_attached_file', array(&$this, 'ajax_update_attached_file_meta'));

                add_filter('cuar/core/js-messages?zone=admin', array(&$this, 'add_js_messages'));
                add_filter('cuar/core/js-messages?zone=frontend', array(&$this, 'add_js_messages'));

                $this->default_handlers = new CUAR_PrivateFilesDefaultHandlers($plugin);
            }

            // Init the admin interface if needed
            if (is_admin())
            {
                $this->admin_interface = new CUAR_PrivateFileAdminInterface($plugin, $this);
            }
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

            $defaults[self::$OPTION_ENABLE_ADDON] = true;
            $defaults[self::$OPTION_FTP_PATH] = WP_CONTENT_DIR . '/customer-area/ftp-uploads';
            $defaults[self::$OPTION_STORAGE_PATH] = '';

            return $defaults;
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function is_enabled()
        {
            return $this->plugin->get_option(self::$OPTION_ENABLE_ADDON);
        }

        public function get_ftp_path($create_dirs = false)
        {
            $path = $this->plugin->get_option(self::$OPTION_FTP_PATH);

            if ($create_dirs)
            {
                @mkdir($path, 0750, true);
            }

            return $path;
        }

        public function get_custom_storage_path()
        {
            return $this->plugin->get_option(self::$OPTION_STORAGE_PATH);
        }

        public function filter_storage_path($path)
        {
            $custom_path = $this->plugin->get_option(self::$OPTION_STORAGE_PATH);

            return empty($custom_path) ? $path : $custom_path;
        }

        /*------- GENERAL MAINTAINANCE FUNCTIONS ------------------------------------------------------------------------*/

        /**
         * Delete the files when a post is deleted
         *
         * @param int $post_id
         */
        public function before_post_deleted($post_id)
        {
            if (get_post_type($post_id) != 'cuar_private_file') return;

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');

            $files = $this->get_attached_files($post_id);
            foreach ($files as $file_id => $file)
            {
                $filename = $file['file'];
                $source = $file['source'];

                // New file structure
                if ( !empty($source))
                {
                    apply_filters('cuar/private-content/files/on-remove-attached-file?source=' . $source,
                        array(),
                        $post_id,
                        $file);
                }

                // Legacy files
                $legacy_filepath = $po_addon->get_legacy_private_file_path($filename, $post_id);
                if (file_exists($legacy_filepath))
                {
                    unlink($legacy_filepath);
                }
            }
        }

        /*------- FUNCTIONS TO ACCESS THE POST META ---------------------------------------------------------------------*/

        /**
         * Compute an ID from the file name
         *
         * @param string $filename The file name
         *
         * @return string The ID
         */
        public function compute_file_id($file)
        {
            $id = md5($file['file']);
            return apply_filters('cuar/private-content/files/file-id', $id, $file);
        }

        /**
         * Get the name of the file associated to the given post
         *
         * @param int   $post_id
         * @param array $file
         *
         * @return mixed|string
         */
        public function get_file_path($post_id, $file)
        {
            if ( !$file || empty($file)) return '';

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $file_path = $po_addon->get_private_file_path($file['file'], $post_id, false);

            // If the file does not exist, try with the old path format (without post ID)
            if ( !file_exists($file_path))
            {
                $file_path = $po_addon->get_legacy_private_file_path($file['file'], $post_id, false);
            }

            // Does not exist at all? -> return false
            if ( !file_exists($file_path))
            {
                $file_path = false;
            }

            return apply_filters('cuar/private-content/files/file-path', $file_path, $post_id, $file);
        }

        /**
         * Get the source of the file associated to the given post
         *
         * @param int   $post_id
         * @param array $file
         *
         * @return mixed|string
         */
        public function get_file_source($post_id, $file)
        {
            if ( !$file || empty($file)) return '';

            $source = empty($file['source']) ? 'local' : $file['source'];

            return apply_filters('cuar/private-content/files/file-source', $source, $post_id, $file);
        }

        /**
         * Get the caption of the file associated to the given post
         *
         * @param int   $post_id
         * @param array $file
         *
         * @return mixed|string
         */
        public function get_file_caption($post_id, $file)
        {
            if ( !$file || empty($file)) return '';

            $caption = empty($file['caption']) ? $file['file'] : $file['caption'];

            return apply_filters('cuar/private-content/files/file-caption', $caption, $post_id, $file);
        }

        /**
         * Get the name of the file associated to the given post
         *
         * @param int   $post_id
         * @param array $file
         *
         * @return mixed|string
         */
        public function get_file_name($post_id, $file)
        {
            if ( !$file || empty($file)) return '';

            return apply_filters('cuar/private-content/files/file-name', $file['file'], $post_id, $file);
        }

        /**
         * Get the type of the file associated to the given post
         *
         * @param int   $post_id
         * @param array $file
         *
         * @return string|mixed
         */
        public function get_file_type($post_id, $file)
        {
            if ( !$file || empty($file)) return '';

            return apply_filters('cuar/private-content/files/file-type', pathinfo($file['file'], PATHINFO_EXTENSION), $post_id, $file);
        }

        /**
         * Get the size of the file associated to the given post
         *
         * @param int   $post_id
         * @param array $file
         *
         * @return boolean|int false if the file does not exist
         */
        public function get_file_size($post_id, $file)
        {
            $file_path = $this->get_file_path($post_id, $file);
            $size = @filesize($file_path);

            return apply_filters('cuar/private-content/files/file-size', $size, $post_id, $file);
        }

        /**
         * Get the number of times the file has been downloaded
         *
         * @param int    $post_id
         * @param string $file_id
         *
         * @return int
         */
        public function get_file_download_count($post_id, $file_id = null)
        {
            if ($file_id == null)
            {
                $count = get_post_meta($post_id, 'cuar/private-content/files/on-download_count', true);
            }
            else
            {
                $count = get_post_meta($post_id, 'cuar/private-content/files/on-download_count?name=' . $file_id, true);
            }

            if ( !$count || empty($count)) return 0;

            return intval($count);
        }

        /**
         * Get the number of times the file has been downloaded
         *
         * @param int    $post_id
         * @param string $file_id
         *
         * @return int
         */
        public function increment_file_download_count($post_id, $file_id = null)
        {
            $current_count = $this->get_file_download_count($post_id, $file_id);
            if ($file_id == null)
            {
                update_post_meta($post_id,
                    'cuar/private-content/files/on-download_count',
                    $current_count + 1);
            }
            else
            {
                update_post_meta($post_id,
                    'cuar/private-content/files/on-download_count?id=' . $file_id,
                    $current_count + 1);
            }
        }

        /**
         * Get the permalink to a file for the specified action
         *
         * @param int    $post_id
         * @param string $action
         * @param string $file_id
         *
         * @return string
         */
        public function get_file_permalink($post_id, $file_id, $action = 'download', $file = null)
        {
            $action = apply_filters('cuar/private-content/files/default-link-action', $action, $post_id, $file_id, $file);

            /** @var CUAR_CustomerPrivateFilesAddOn $cpf_addon */
            $cpf_addon = $this->plugin->get_addon('customer-private-files');
            $url = $cpf_addon->get_single_private_content_action_url($post_id, $action, $file_id);

            return $url;
        }

        /**
         * Attach a file to the post
         *
         * @param int    $post_id
         * @param string $filename
         * @param string $caption
         * @param string $source
         * @param mixed  $extra
         *
         * @return array The file description
         */
        public function add_attached_file($post_id, $filename, $caption, $source, $extra)
        {
            $meta = array(
                'id'      => '',
                'source'  => $source,
                'post_id' => $post_id,
                'file'    => $filename,
                'caption' => $caption,
                'extra'   => $extra
            );

            $meta['id'] = $this->compute_file_id($meta);

            // Update an existing file if any
            $files = $this->get_attached_files($post_id);
            $found = false;
            foreach ($files as $fid => $file)
            {
                if ($fid == $meta['id'] || $file['file'] == $filename)
                {
                    unset($files[$fid]);
                    $files[$meta['id']] = $meta;
                    $found = true;
                    break;
                }
            }

            // File not updated, just add it
            if ( !$found)
            {
                $files[$meta['id']] = $meta;
            }

            $this->save_attached_files($post_id, $files);

            return $meta;
        }

        /**
         * Get the descriptors of all files attached to this content
         *
         * @param int   $post_id The content ID
         * @param array $files   The files to save
         *
         * @return bool|mixed
         */
        public function save_attached_files($post_id, $files)
        {
            update_post_meta($post_id, 'cuar_private_file_file', $files);
        }

        /**
         * Get the descriptors of all files attached to this content
         *
         * @param int $post_id
         *
         * @return bool|mixed
         */
        public function get_attached_files($post_id)
        {
            $file_meta = get_post_meta($post_id, 'cuar_private_file_file', true);

            // No files at all
            if ( !$file_meta)
            {
                return array();
            }

            // We have a single file attached to the content type, this is the legacy meta format
            if (isset($file_meta['file']))
            {
                // Use this opportunity to update that meta field to the new format
                $file_meta = $this->update_legacy_file_meta($file_meta);
                $file_meta = array($file_meta['id'] => $file_meta);
                update_post_meta($post_id, 'cuar_private_file_file', $file_meta);
            }

            return $file_meta;
        }

        /**
         * Get the descriptor of a file attached to this content from the file name
         *
         * @param int    $post_id  The post ID
         * @param string $filename The name of the file we are looking for
         * @param array  $files    The files attached to the post (to save some CPU/queries if you already have it)
         *
         * @return array|bool false if not found
         */
        public function get_attached_file_by_name($post_id, $filename, $files = null)
        {
            if ($files == null)
            {
                $files = $this->get_attached_files($post_id);
            }

            foreach ($files as $file)
            {
                if ($file['file']==$filename) return $file;
            }

            return false;
        }

        /**
         * Get the descriptor of a file attached to this content
         *
         * @param int    $post_id The post ID
         * @param string $file_id The ID of the file we are looking for
         * @param array  $files   The files attached to the post (to save some CPU/queries if you already have it)
         *
         * @return array|bool false if not found
         */
        public function get_attached_file($post_id, $file_id, $files = null)
        {
            if ($files == null)
            {
                $files = $this->get_attached_files($post_id);
            }

            // Check if present
            if (isset($files[$file_id]))
            {
                return $files[$file_id];
            }

            return false;
        }

        /**
         * Get the the number of files attached to this content
         *
         * @param int $post_id
         *
         * @return int
         *
         * @since 6.2
         */
        public function get_attached_file_count($post_id)
        {
            $files = $this->get_attached_files($post_id);

            return count($files);
        }

        /**
         * Update the file meta data from the old single file meta format to the new meta format.
         *
         * @since 6.2
         *
         * @param array $file The file
         *
         * @return array
         */
        private function update_legacy_file_meta($file)
        {
            if ( !isset($file['source']))
            {
                $file['source'] = 'legacy';
                $file['caption'] = $file['file'];
                $file['extra'] = '';
                $file['id'] = $this->compute_file_id($file);
            }

            return $file;
        }

        /**
         * @return mixed|void
         */
        public function get_max_attachment_count()
        {
            return apply_filters('cuar/private-content/files/max-attachment-count', 1);
        }

        /*------- ATTACHMENTS MANAGER -----------------------------------------------------------------------------------*/

        /**
         * Print the scripts to manage attachments
         */
        public function print_attachment_manager_scripts()
        {
            $template_suffix = is_admin() ? '-admin' : '-frontend';

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/private-file',
                array(
                    'private-attachments-add-methods-browser-scripts' . $template_suffix . '.template.php',
                    'private-attachments-add-methods-browser-scripts.template.php'
                ),
                'templates'));
        }

        /**
         * Print the add attachment ajax methods
         *
         * @param int $post_id The post ID
         */
        public function print_add_attachment_method_browser($post_id)
        {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $select_methods = apply_filters('cuar/private-content/files/select-methods', array());

            $template_suffix = is_admin() ? '-admin' : '-frontend';

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/private-file',
                array(
                    'private-attachments-add-methods-browser' . $template_suffix . '.template.php',
                    'private-attachments-add-methods-browser.template.php'
                ),
                'templates'));
        }

        /**
         * Print the current attachments manager
         *
         * @param int $post_id The post ID
         */
        public function print_current_attachments_manager($post_id)
        {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $attached_files = $this->get_attached_files($post_id);

            $template_suffix = is_admin() ? '-admin' : '-frontend';

            $post = get_post($post_id);
            if (($post!=null && $post->post_author==get_current_user_id())
                || current_user_can('cuar_pf_manage_attachments'))
            {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $attachment_item_template = $this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/private-file',
                    array(
                        'private-attachments-list-item' . $template_suffix . '.template.php',
                        'private-attachments-list-item.template.php',
                    ),
                    'templates');
            }
            else
            {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $attachment_item_template = $this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/private-file',
                    array(
                        'private-attachments-list-item-readonly' . $template_suffix . '.template.php',
                        'private-attachments-list-item-readonly.template.php',
                    ),
                    'templates');
            }

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/private-file',
                array(
                    'private-attachments-list' . $template_suffix . '.template.php',
                    'private-attachments-list.template.php',
                ),
                'templates'));
        }

        /*------- UTILITY FUNCTIONS FOR MAINTENANCE ---------------------------------------------------------------------*/

        public function is_folder_accessible_from_web($path)
        {
            // Create temp file in that folder
            $tmp_filename = md5("test") . ".txt";
            $tmp_filepath = untrailingslashit($path) . '/' . $tmp_filename;
            @unlink($tmp_filepath);

            $tmp_file = @fopen($tmp_filepath, "w");
            if ($tmp_file === false) return false;

            if (false === @fwrite($tmp_file, '0123456789'))
            {
                @unlink($tmp_filepath);
                @fclose($tmp_file);

                return false;
            }

            // Tokenize folder and start from the last token
            $url_path = '';
            $home_url = trailingslashit(home_url());
            $path_tokens = explode('/', $path);
            for ($i = count($path_tokens) - 1; $i >= 0; $i--)
            {
                $url_path = $path_tokens[$i] . '/' . $url_path;
                $full_url = $home_url . $url_path . $tmp_filename;

                $response = wp_remote_get($full_url, array('method' => 'GET'));
                $response_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                if ($response_code < 400 && !empty($body) && $body == '0123456789')
                {
                    return sprintf(__('<a href="%s">Public URL to a test file in that folder</a>. Response code: %s. File content: %s', 'cuar'),
                        $full_url,
                        $response_code,
                        substr($body, 0, 10) . '...');
                }
            }

            // Delete temp file in that folder
            @unlink($tmp_filepath);

            return false;
        }

        /**
         * Delete physical files which are not registered in meta
         *
         * @param int $post_id The post ID
         */
        public function remove_orphan_files($post_id)
        {
            do_action('cuar/private-content/files/remove-orphan-files?source=local', $post_id);
        }

        /**
         * Move all legacy files to the new storage folder
         *
         * @param int   $post_id The post ID
         * @param array $owners   The current owner of the post (or previous one if calling this when saving post
         */
        public function move_legacy_files($post_id, $owners)
        {
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');

            $files = $this->get_attached_files($post_id);
            foreach ($files as $file_id => $file)
            {
                if ($file['source'] == 'legacy')
                {
                    foreach ($owners as $owner_type => $owner_ids)
                    {
                        $old_path = $po_addon->get_legacy_owner_file_path($post_id, $file['file'], $owner_ids, $owner_type, false);
                        $new_path = $po_addon->get_private_file_path($file['file'], $post_id, true);

                        if (file_exists($old_path))
                        {
                            @copy($old_path, $new_path);
                            @unlink($old_path);

                            // Maybe delete empty folder
                            if ($this->is_dir_empty(basename($old_path)))
                            {
                                @rmdir(basename($old_path));
                            }
                        }

                        $files[$file_id]['source'] = 'local';
                    }
                }
            }
            $this->save_attached_files($post_id, $files);
        }

        /**
         * Supporting function for displaying the dropdown select box
         * for empty FTP upload directory or not.
         * Adapted from http://stackoverflow.com/a/7497848/1177153
         *
         * @param string $dir
         *
         * @return bool
         */
        public function is_dir_empty($dir)
        {
            if ( !is_readable($dir)) return false;

            $handle = opendir($dir);
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    return false;
                }
            }

            return true;
        }

        /**
         * Remove all file entries in meta which are not physically present
         *
         * @param int $post_id The post ID
         */
        public function remove_missing_files($post_id)
        {
            $files = $this->get_attached_files($post_id);
            foreach ($files as $file_id => $file)
            {
                $is_missing = apply_filters('cuar/private-content/files/is-missing?source=' . $file['source'], false, $post_id, $file);
                if ($is_missing)
                {
                    unset($files[$file_id]);
                }
            }
            $this->save_attached_files($post_id, $files);
        }

        /*------- AJAX FUNCTIONS ----------------------------------------------------------------------------------------*/

        /**
         * Append our javascript messages
         *
         * @param array $messages
         *
         * @return array
         */
        public function add_js_messages($messages)
        {
            $max_attachment_count = $this->get_max_attachment_count();

            $messages['confirmDeleteAttachedFile'] = __('Do you really want to remove this file?', 'cuar');
            $messages['tooManyAttachmentsAlready'] = sprintf(
                _n(
                    'You are not allowed to attach more than %d file',
                    'You are not allowed to attach more than %d files',
                    $max_attachment_count,
                    'cuar'),
                $max_attachment_count);

            $messages['maxAttachmentCount'] = $max_attachment_count;

            return $messages;
        }

        /**
         * Handle the file attachment process with AJAX
         */
        public function ajax_attach_file()
        {
            $errors = array();
            $method = isset($_POST['method']) ? $_POST['method'] : 0;
            $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;

            // Check nonce
            $nonce_action = 'cuar-attach-' . $method . '-' . $post_id;
            $nonce_name = 'cuar_' . $method . '_' . $post_id;
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action))
            {
                $errors[] = __('Trying to cheat?', 'cuar');
                wp_send_json_error($errors);
            }

            // Check permissions
            $post = get_post($post_id);
            if ( !is_user_logged_in()
                || $post===null
                || ($post->post_author!=get_current_user_id() && !current_user_can('cuar_pf_manage_attachments')))
            {
                $errors[] = __('You are not allowed to attach files to this kind of content', 'cuar');
                wp_send_json_error($errors);
            }

            $extra = isset($_POST['extra']) ? $_POST['extra'] : '';

            $initial_filename = isset($_POST['filename']) ? $_POST['filename'] : 0;
            $unique_filename = apply_filters('cuar/private-content/files/unique-filename?method=' . $method, $initial_filename, $post_id, $extra);

            $caption = isset($_POST['caption']) ? $_POST['caption'] : $unique_filename;

            // Check for missing parameters
            if (empty($post_id) || empty($unique_filename) || empty($method))
            {
                $errors[] = __('Missing parameters', 'cuar');
                wp_send_json_error($errors);
            }

            // Check allowed number of attached files
            $file_count = $this->get_attached_file_count($post_id);
            $max_file_count = $this->get_max_attachment_count();
            if ($max_file_count >= 0 && $file_count >= $max_file_count)
            {
                $errors[] = sprintf(
                    _n(
                        'You are not allowed to attach more than %d file',
                        'You are not allowed to attach more than %d files',
                        $max_file_count,
                        'cuar'),
                    $max_file_count);
                wp_send_json_error($errors);
            }

            // Check file exists
            $found_file_index = null;
            $found_file = $this->get_attached_file_by_name($post_id, $unique_filename);
            if ($found_file)
            {
                $errors[] = __('You cannot attach files with the same name', 'cuar');
                wp_send_json_error($errors);
            }

            // File does not exist, we'll add it now
            $errors = apply_filters('cuar/private-content/files/on-attach-file?method=' . $method, $errors, $this, $initial_filename, $post_id,
                $unique_filename, $caption, $extra);
            if ( !empty($errors))
            {
                wp_send_json_error($errors);
            }

            // Fine, update file meta
            $source = apply_filters('cuar/private-content/files/file-source?method=' . $method, 'local');
            if (empty($caption)) $caption = $unique_filename;
            $added_file = $this->add_attached_file($post_id, $unique_filename, $caption, $source, $extra);

            // Log an event
            do_action('cuar/private-content/files/on-add-attachment', $post_id, $added_file);

            wp_send_json_success($added_file);
        }

        /**
         * Handle the attachment meta edition process via AJAX
         *
         * @since 6.2
         */
        public function ajax_update_attached_file_meta()
        {
            $errors = array();
            $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
            $filename = isset($_POST['filename']) ? $_POST['filename'] : 0;

            // Check parameters
            if (empty($post_id) || empty($filename))
            {
                $errors[] = __('Missing parameters', 'cuar');
                wp_send_json_error($errors);
            }

            // Check nonce
            $nonce_action = 'cuar-update-attachment-' . $post_id;
            $nonce_name = 'cuar_update_attachment_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action))
            {
                $errors[] = __('Trying to cheat?', 'cuar');
                wp_send_json_error($errors);
            }

            // Check permissions
            $post = get_post($post_id);
            if ( !is_user_logged_in()
                || $post===null
                || ($post->post_author!=get_current_user_id() && !current_user_can('cuar_pf_manage_attachments')))
            {
                $errors[] = __('You are not allowed to update attached files', 'cuar');
                wp_send_json_error($errors);
            }

            // Check file exists
            $files = $this->get_attached_files($post_id);
            $found_file = $this->get_attached_file_by_name($post_id, $filename, $files);
            if ($found_file == false)
            {
                $errors[] = __('File not found', 'cuar');
                wp_send_json_error($errors);
            }

            // File exists, change the details which need to be changed
            $has_changed = false;
            if (isset($_POST['caption']) && 0 != strcmp($_POST['caption'], $found_file['caption']))
            {
                $has_changed = true;
                $files[$found_file['id']]['caption'] = $_POST['caption'];
            }

            if ($has_changed)
            {
                $this->save_attached_files($post_id, $files);

                // Log an event
                do_action('cuar/private-content/files/on-update-attachment', $post_id, $found_file);
            }

            wp_send_json_success($files[$found_file['id']]);
        }

        /**
         * Handle the file removal process via AJAX
         *
         * @since 6.2
         */
        public function ajax_remove_attached_file()
        {
            $errors = array();
            $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
            $filename = isset($_POST['filename']) ? $_POST['filename'] : 0;

            // Check parameters
            if (empty($post_id) || empty($filename))
            {
                $errors[] = __('Missing parameters', 'cuar');
                wp_send_json_error($errors);
            }

            // Check nonce
            $nonce_action = 'cuar-remove-attachment-' . $post_id;
            $nonce_name = 'cuar_remove_attachment_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action))
            {
                $errors[] = __('Trying to cheat?', 'cuar');
                wp_send_json_error($errors);
            }

            // Check permissions
            $post = get_post($post_id);
            if ( !is_user_logged_in()
                || $post===null
                || ($post->post_author!=get_current_user_id() && !current_user_can('cuar_pf_manage_attachments')))
            {
                $errors[] = __('You are not allowed to remove attached files', 'cuar');
                wp_send_json_error($errors);
            }

            // Check file exists
            $files = $this->get_attached_files($post_id);
            $found_file = $this->get_attached_file_by_name($post_id, $filename, $files);
            if ($found_file == false)
            {
                // Consider that is ok with us
                wp_send_json_success();
            }

            // File exists, we'll remove it now. First physically
            $source = $this->get_file_source($post_id, $found_file);
            $errors = apply_filters('cuar/private-content/files/on-remove-attached-file?source=' . $source, $errors, $post_id, $found_file);
            if ( !empty($errors))
            {
                wp_send_json_error($errors);
            }

            // Then from post meta if we could handle the physical removal
            unset($files[$found_file['id']]);
            $this->save_attached_files($post_id, $files);

            // Log an event
            do_action('cuar/private-content/files/on-remove-attachment', $post_id, $found_file);

            wp_send_json_success();
        }

        /*------- HANDLE FILE VIEWING AND DOWNLOADING --------------------------------------------------------------------*/

        /**
         * Handle the actions on a private file
         */
        public function handle_file_actions()
        {
            // If not on a matching post type, we do nothing
            if ( !is_singular('cuar_private_file')) return;

            // If not a known action, do nothing
            $action = get_query_var('cuar_action');
            if ($action != 'download' && $action != 'view')
            {
                return;
            }

            // If no index, default to 0
            $file_id = get_query_var('cuar_action_param');

            // If not logged-in, we ask for details
            if ( !is_user_logged_in())
            {
                $this->plugin->login_then_redirect_to_url($_SERVER['REQUEST_URI']);

                return;
            }

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');

            // If not authorized to download the file, we bail
            $post = get_queried_object();
            $current_user_id = get_current_user_id();
            $author_id = $post->post_author;
            $is_current_user_owner = $po_addon->is_user_owner_of_post($post->ID, $current_user_id);

            if ( !($is_current_user_owner || $author_id == $current_user_id || current_user_can('cuar_view_any_cuar_private_file')))
            {
                wp_die(__("You are not authorized to access this file", "cuar"));
                exit();
            }

            // Look up that file given its ID
            $files = $this->get_attached_files($post->ID);
            $found_file = null;
            foreach ($files as $fid => $file)
            {
                // Default case
                if ($fid == $file_id)
                {
                    $found_file = $file;
                    break;
                }
            }

            // File not found
            if ($found_file == null)
            {
                wp_die(__("There is no such file attached to this private content", "cuar"));
                exit();
            }

            // Default action to apply on file
            $action = apply_filters('cuar/private-content/files/default-action', $action, $found_file);

            // Seems we are all good, do some stuff before sending the file
            if ($author_id != $current_user_id)
            {
                $this->increment_file_download_count($post->ID, $file_id);
            }
            do_action('cuar/private-content/files/on-' . $action, $post->ID, $current_user_id, $this, $file_id);

            // Send the file
            do_action('cuar/private-content/files/output-file?source=' . $found_file['source'], $post->ID, $found_file, $action);
        }

        /*------- INITIALISATIONS ----------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            $capability_groups['cuar_private_file'] = array(
                'label'  => __('Private Files', 'cuar'),
                'groups' => array(
                    'global'       => array(
                        'group_name'   => __('Global', 'cuar'),
                        'capabilities' => array(
                            'cuar_pf_manage_attachments' => __('Manage file attachments', 'cuar'),
                        )
                    ),
                    'back-office'  => array(
                        'group_name'   => __('Back-office', 'cuar'),
                        'capabilities' => array(
                            'cuar_pf_list_all'          => __('List all files', 'cuar'),
                            'cuar_pf_edit'              => __('Create/Edit files', 'cuar'),
                            'cuar_pf_delete'            => __('Delete files', 'cuar'),
                            'cuar_pf_read'              => __('Access files', 'cuar'),
                            'cuar_pf_manage_categories' => __('Manage categories', 'cuar'),
                            'cuar_pf_edit_categories'   => __('Edit categories', 'cuar'),
                            'cuar_pf_delete_categories' => __('Delete categories', 'cuar'),
                            'cuar_pf_assign_categories' => __('Assign categories', 'cuar'),
                        )
                    ),
                    'front-office' => array(
                        'group_name'   => __('Front-office', 'cuar'),
                        'capabilities' => array(
                            'cuar_view_files'                 => __('View private files', 'cuar'),
                            'cuar_view_any_cuar_private_file' => __('View any private file', 'cuar'),
                        )
                    )
                )
            );

            return $capability_groups;
        }

        /**
         * Declare our content type
         *
         * @param array $types
         *
         * @return array
         */
        public function register_content_type($types)
        {
            $types['cuar_private_file'] = array(
                'label-singular'     => _x('File', 'cuar_private_file', 'cuar'),
                'label-plural'       => _x('Files', 'cuar_private_file', 'cuar'),
                'content-page-addon' => 'customer-private-files',
                'type'               => 'content'
            );

            return $types;
        }

        /**
         * Declare that our post type is owned by someone
         *
         * @param array $types
         *
         * @return array
         */
        public function register_private_post_types($types)
        {
            $types[] = "cuar_private_file";

            return $types;
        }

        /**
         * Register the custom post type for files and the associated taxonomies
         */
        public function register_custom_types()
        {
            $labels = array(
                'name'               => _x('Private Files', 'cuar_private_file', 'cuar'),
                'singular_name'      => _x('Private File', 'cuar_private_file', 'cuar'),
                'add_new'            => _x('Add New', 'cuar_private_file', 'cuar'),
                'add_new_item'       => _x('Add New Private File', 'cuar_private_file', 'cuar'),
                'edit_item'          => _x('Edit Private File', 'cuar_private_file', 'cuar'),
                'new_item'           => _x('New Private File', 'cuar_private_file', 'cuar'),
                'view_item'          => _x('View Private File', 'cuar_private_file', 'cuar'),
                'search_items'       => _x('Search Private Files', 'cuar_private_file', 'cuar'),
                'not_found'          => _x('No private files found', 'cuar_private_file', 'cuar'),
                'not_found_in_trash' => _x('No private files found in Trash', 'cuar_private_file', 'cuar'),
                'parent_item_colon'  => _x('Parent Private File:', 'cuar_private_file', 'cuar'),
                'menu_name'          => _x('Private Files', 'cuar_private_file', 'cuar'),
            );

            $args = array(
                'labels'              => $labels,
                'hierarchical'        => false,
                'supports'            => array('title', 'editor', 'author', 'thumbnail', 'comments', 'excerpt'),
                'taxonomies'          => array('cuar_private_file_category'),
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => false,
                'show_in_nav_menus'   => false,
                'publicly_queryable'  => true,
                'exclude_from_search' => true,
                'has_archive'         => false,
                'query_var'           => 'cuar_private_file',
                'can_export'          => false,
                'rewrite'             => false,
                'capabilities'        => array(
                    'edit_post'          => 'cuar_pf_edit',
                    'edit_posts'         => 'cuar_pf_edit',
                    'edit_others_posts'  => 'cuar_pf_edit',
                    'publish_posts'      => 'cuar_pf_edit',
                    'read_post'          => 'cuar_pf_read',
                    'read_private_posts' => 'cuar_pf_list_all',
                    'delete_post'        => 'cuar_pf_delete',
                    'delete_posts'       => 'cuar_pf_delete',
                )
            );

            register_post_type('cuar_private_file', apply_filters('cuar/private-content/files/register-post-type-args', $args));

            $labels = array(
                'name'                       => _x('File Categories', 'cuar_private_file_category', 'cuar'),
                'singular_name'              => _x('File Category', 'cuar_private_file_category', 'cuar'),
                'search_items'               => _x('Search File Categories', 'cuar_private_file_category', 'cuar'),
                'popular_items'              => _x('Popular File Categories', 'cuar_private_file_category', 'cuar'),
                'all_items'                  => _x('All File Categories', 'cuar_private_file_category', 'cuar'),
                'parent_item'                => _x('Parent File Category', 'cuar_private_file_category', 'cuar'),
                'parent_item_colon'          => _x('Parent File Category:', 'cuar_private_file_category', 'cuar'),
                'edit_item'                  => _x('Edit File Category', 'cuar_private_file_category', 'cuar'),
                'update_item'                => _x('Update File Category', 'cuar_private_file_category', 'cuar'),
                'add_new_item'               => _x('Add New File Category', 'cuar_private_file_category', 'cuar'),
                'new_item_name'              => _x('New File Category', 'cuar_private_file_category', 'cuar'),
                'separate_items_with_commas' => _x('Separate file categories with commas', 'cuar_private_file_category', 'cuar'),
                'add_or_remove_items'        => _x('Add or remove file categories', 'cuar_private_file_category', 'cuar'),
                'choose_from_most_used'      => _x('Choose from the most used file categories', 'cuar_private_file_category', 'cuar'),
                'menu_name'                  => _x('File Categories', 'cuar_private_file_category', 'cuar'),
            );

            $args = array(
                'labels'            => $labels,
                'public'            => true,
                'show_in_menu'      => false,
                'show_in_nav_menus' => 'customer-area',
                'show_ui'           => true,
                'show_tagcloud'     => false,
                'show_admin_column' => true,
                'hierarchical'      => true,
                'query_var'         => true,
                'rewrite'           => false,
                'capabilities'      => array(
                    'manage_terms' => 'cuar_pf_manage_categories',
                    'edit_terms'   => 'cuar_pf_edit_categories',
                    'delete_terms' => 'cuar_pf_delete_categories',
                    'assign_terms' => 'cuar_pf_assign_categories',
                )
            );

            register_taxonomy('cuar_private_file_category', array('cuar_private_file'),
                apply_filters('cuar/private-content/files/category/register-taxonomy-args', $args));
        }

        // General options
        public static $OPTION_ENABLE_ADDON = 'enable_private_files';
        public static $OPTION_FTP_PATH = 'frontend_ftp_upload_path';
        public static $OPTION_STORAGE_PATH = 'frontend_storage_path';

        /** @var CUAR_PrivateFileAdminInterface */
        private $admin_interface;
    }

// Make sure the addon is loaded
    new CUAR_PrivateFileAddOn();

endif; // if (!class_exists('CUAR_PrivateFileAddOn')) 

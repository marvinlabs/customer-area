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

                add_action('template_redirect', array(&$this, 'handle_file_actions'));
                add_action('before_delete_post', array(&$this, 'before_post_deleted'));

                add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'));

                add_action('wp_ajax_cuar_remove_attached_file', array(&$this, 'ajax_remove_attached_file'));
                add_action('wp_ajax_nopriv_cuar_remove_attached_file', array(&$this, 'ajax_remove_attached_file'));

                add_action('wp_ajax_cuar_attach_file', array(&$this, 'ajax_attach_file'));
                add_action('wp_ajax_nopriv_cuar_attach_file', array(&$this, 'ajax_attach_file'));

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

            return $defaults;
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function is_enabled()
        {
            return $this->plugin->get_option(self::$OPTION_ENABLE_ADDON);
        }

        public function get_ftp_path()
        {
            return $this->plugin->get_option(self::$OPTION_FTP_PATH);
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
        public function get_file_id_from_name($filename)
        {
            return md5($filename);
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
        public function get_file_permalink($post_id, $file_id, $action = 'download')
        {
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
         */
        public function add_attached_file($post_id, $filename, $caption, $source, $extra)
        {
            $file_id = $this->get_file_id_from_name($filename);
            $meta = array(
                'id'      => $file_id,
                'source'  => $source,
                'post_id' => $post_id,
                'file'    => $filename,
                'caption' => $caption,
                'extra'   => $extra
            );

            // Update an existing file if any
            $files = $this->get_attached_files($post_id);
            $found = false;
            foreach ($files as $fid => $file)
            {
                if ($fid == $file_id || $file['file'] == $filename)
                {
                    unset($files[$fid]);
                    $files[$file_id] = $meta;
                    $found = true;
                    break;
                }
            }

            // File not updated, just add it
            if ( !$found)
            {
                $files[$file_id] = $meta;
            }

            $this->save_attached_files($post_id, $files);
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

            // Check if present
            $file_id = $this->get_file_id_from_name($filename);
            if (isset($files[$file_id]))
            {
                return $files[$file_id];
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
                $file['id'] = $this->get_file_id_from_name($file['file']);
                $file['source'] = 'legacy';
                $file['caption'] = $file['file'];
                $file['extra'] = '';
            }

            return $file;
        }

        /*------- AJAX FUNCTIONS ----------------------------------------------------------------------------------------*/

        /**
         * Handle the file attachment process with AJAX
         */
        public function ajax_attach_file()
        {
            $errors = array();
            $method = isset($_POST['method']) ? $_POST['method'] : 0;
            $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
            $filename = isset($_POST['filename']) ? $_POST['filename'] : 0;
            $caption = isset($_POST['caption']) ? $_POST['caption'] : $filename;
            $extra = isset($_POST['extra']) ? $_POST['extra'] : '';

            if (empty($post_id) || empty($filename) || empty($method))
            {
                $errors[] = __('Missing parameters', 'cuar');
                wp_send_json_error($errors);
            }

            // Check file exists
            $found_file_index = null;
            $found_file = $this->get_attached_file_by_name($post_id, $filename);
            if ($found_file)
            {
                $errors[] = __('You cannot attach files with the same name', 'cuar');
                wp_send_json_error($errors);
            }

            // File does not exist, we'll add it now
            $errors = apply_filters('cuar/private-content/files/on-attach-file?method=' . $method, $errors, $this, $post_id, $filename, $caption, $extra);
            if ( !empty($errors))
            {
                wp_send_json_error($errors);
            }

            // Fine, update file meta
            $source = apply_filters('cuar/private-content/files/file-source?method=' . $method, 'local');
            if (empty($caption)) $caption = $filename;
            $this->add_attached_file($post_id, $filename, $caption, $source, $extra);

            wp_send_json_success();
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

            if (empty($post_id) || empty($filename))
            {
                $errors[] = __('Missing parameters', 'cuar');
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

            wp_send_json_success();
        }

        /*------- LEGACY FUNCTION ---------------------------------------------------------------------------------------*/

        /**
         * Handles the case when we need to change the owner of the file of an existing post
         *
         * @param int   $post_id
         * @param array $previous_owner
         * @param array $new_owner
         *
         * @deprecated
         */
        public function handle_private_file_owner_changed($post_id, $previous_owner, $new_owner)
        {
            $po_addon = $this->plugin->get_addon('post-owner');

            $count = $this->get_attached_file_count($post_id);
            for ($i = 0; $i < $count; ++$i)
            {
                $previous_file = $this->get_attached_file($post_id, $i);
                if ($previous_file)
                {
                    $previous_file['path'] = $po_addon->get_legacy_owner_file_path($post_id,
                        $previous_file['file'],
                        $previous_owner['ids'],
                        $previous_owner['type'],
                        true);

                    if (file_exists($previous_file['path']))
                    {
                        $new_file_path = $po_addon->get_legacy_owner_file_path($post_id,
                            $previous_file['file'],
                            $new_owner['ids'],
                            $new_owner['type'],
                            true);

                        if ($previous_file['path'] == $new_file_path) return;
                        if (copy($previous_file['path'], $new_file_path)) unlink($previous_file['path']);
                    }
                }
            }
        }

        /**
         * Change the upload directory on the fly when uploading our private file
         *
         * @param unknown $default_dir
         *
         * @return unknown|multitype:boolean string unknown
         *
         * @deprecated
         */
        public function custom_upload_dir($default_dir)
        {
            if ( !isset($_POST['post_ID']) || $_POST['post_ID'] < 0) return $default_dir;
            if ($_POST['post_type'] != 'cuar_private_file') return $default_dir;

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');

            $dir = $po_addon->get_base_private_storage_directory();
            $url = $po_addon->get_base_private_storage_url();

            $bdir = $dir;
            $burl = $url;

            $subdir = '/' . $po_addon->get_private_storage_directory($_POST['post_ID']);

            $dir .= $subdir;
            $url .= $subdir;

            $custom_dir = array(
                'path'    => $dir,
                'url'     => $url,
                'subdir'  => $subdir,
                'basedir' => $bdir,
                'baseurl' => $burl,
                'error'   => false,
            );

            return $custom_dir;
        }

        /**
         *
         * @param int   $post_id
         * @param array $previous_owner
         * @param array $new_owner
         *
         * @deprecated
         */
        public function handle_new_private_file_upload($post_id, $previous_owner, $new_owner, $file)
        {
            if ( !isset($file) || empty($file)) return array('error' => __('no file to upload', 'cuar'));

            $po_addon = $this->plugin->get_addon('post-owner');

            $previous_file = get_post_meta($post_id, 'cuar_private_file_file', true);

            // Do some file type checking on the uploaded file if needed
            $new_file_name = $file['name'];
            $supported_types = apply_filters('cuar/private-content/files/supported-types', null);
            if ($supported_types != null)
            {
                $arr_file_type = wp_check_filetype(basename($file['name']));
                $uploaded_type = $arr_file_type['type'];

                if ( !in_array($uploaded_type, $supported_types))
                {
                    $msg = sprintf(__("This file type is not allowed. You can only upload: %s", 'cuar',
                        implode(', ', $supported_types)));

                    $this->plugin->add_admin_notice($msg);

                    return array('error' => $msg);
                }
            }

            // Delete the existing file if any
            if ($previous_file)
            {
                $previous_file['path'] = $po_addon->get_owner_file_path($post_id,
                    $previous_file['file'],
                    $previous_owner['ids'],
                    $previous_owner['type'],
                    true);

                if ($previous_file['path'] && file_exists($previous_file['path']))
                {
                    unlink($previous_file['path']);
                }
            }

            // Use the WordPress API to upload the file
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');

            // We will send files to a custom directory
            add_filter('upload_dir', array(&$this, 'custom_upload_dir'));
            if ( !isset($_POST['post_ID']) || $_POST['post_ID'] < 0) $_POST['post_ID'] = $post_id;
            if ( !isset($_POST['post_type']) || $_POST['post_type'] != 'cuar_private_file') $_POST['post_type'] = 'cuar_private_file';

            // Let WP handle the rest
            $upload = wp_handle_upload($file, array('test_form' => false));

            if (empty($upload))
            {
                $msg = sprintf(__('An unknown error happened while uploading your file.', 'cuar'));
                $this->plugin->add_admin_notice($msg);

                return array('error' => $msg);
            }
            else if (isset($upload['error']))
            {
                $msg = sprintf(__('An error happened while uploading your file: %s', 'cuar'), $upload['error']);
                $this->plugin->add_admin_notice($msg);

                return array('error' => $msg);
            }
            else
            {
                $upload['file'] = basename($upload['file']);
                unset($upload['url']);
                update_post_meta($post_id, 'cuar_private_file_file', $upload);

                return true;
            }
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

            // Seems we are all good, do some stuff before sending the file
            if ($action == 'download')
            {
                if ($author_id != $current_user_id)
                {
                    $this->increment_file_download_count($post->ID, $file_id);
                }

                do_action('cuar/private-content/files/on-download', $post->ID, $current_user_id, $this, $file_id);
            }
            else if ($action == 'view')
            {
                if ($author_id != $current_user_id)
                {
                    $this->increment_file_download_count($post->ID, $file_id);
                }

                do_action('cuar/private-content/files/on-view', $post->ID, $current_user_id, $this, $file_id);
            }

            // Send the file
            do_action('cuar/private-content/files/output-file?source=' . $found_file['source'], $post->ID, $found_file, $action);
        }

        /*------- INITIALISATIONS ----------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            $capability_groups['cuar_private_file'] = array(
                'label'  => __('Private Files', 'cuar'),
                'groups' => array(
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
                'supports'            => array('title', 'editor', 'author', 'thumbnail', 'comments'),
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

        /** @var CUAR_PrivateFileAdminInterface */
        private $admin_interface;
    }

// Make sure the addon is loaded
    new CUAR_PrivateFileAddOn();

endif; // if (!class_exists('CUAR_PrivateFileAddOn')) 

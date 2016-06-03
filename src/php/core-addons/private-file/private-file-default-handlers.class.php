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

/**
 * Default file handling routines
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivateFilesDefaultHandlers
{
    /** @var CUAR_Plugin */
    private $plugin;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;

        // Select methods
        add_filter('cuar/private-content/files/select-methods', array(&$this, 'register_select_methods'));
        add_action('cuar/private-content/files/render-select-method?id=classic-upload', array(&$this, 'render_classic_upload_form'));
        add_action('cuar/private-content/files/render-select-method?id=ftp-folder', array(&$this, 'render_ftp_folder_form'));

        // Addition of files for the methods we manage
        add_filter('cuar/private-content/files/on-attach-file?method=ftp-folder', array(&$this, 'attach_ftp_file'), 10, 7);
        add_filter('cuar/private-content/files/on-attach-file?method=classic-upload', array(&$this, 'attach_uploaded_file'), 10, 7);

        // Handling of local files
        add_filter('cuar/private-content/files/unique-filename?method=ftp-folder', array(&$this, 'unique_local_filename'), 10, 4);
        add_filter('cuar/private-content/files/unique-filename?method=classic-upload', array(&$this, 'unique_local_filename_from_files_param'), 10, 4);
        add_action('cuar/private-content/files/on-remove-attached-file?source=local', array(&$this, 'remove_attached_local_file'), 10, 3);
        add_filter('cuar/private-content/files/is-missing?source=local', array(&$this, 'is_local_file_missing'), 10, 3);
        add_action('cuar/private-content/files/remove-orphan-files?source=local', array(&$this, 'remove_orphan_local_files'), 10, 1);
        add_action('cuar/private-content/files/output-file?source=local', array(&$this, 'output_local_file'), 10, 3);

        // Handling of server files
        add_filter('cuar/private-content/files/on-attach-file?method=server', array(&$this, 'attach_server_file'), 10, 7);
        add_filter('cuar/private-content/files/unique-filename?method=server', array(&$this, 'unique_server_filename'), 10, 4);
        add_action('cuar/private-content/files/on-remove-attached-file?source=server', array(&$this, 'remove_attached_server_file'), 10, 3);
        add_filter('cuar/private-content/files/is-missing?source=server', array(&$this, 'is_server_file_missing'), 10, 3);
        add_action('cuar/private-content/files/output-file?source=server', array(&$this, 'output_server_file'), 10, 3);
        add_action('cuar/private-content/files/remove-orphan-files?source=server', array(&$this, 'remove_orphan_local_files'), 10, 1);
        add_action('cuar/private-content/files/file-path', array(&$this, 'get_server_file_path'), 10, 3);
        add_action('cuar/private-content/files/file-id', array(&$this, 'get_server_file_id'), 10, 2);

        // Handling of legacy files
        add_action('cuar/private-content/files/output-file?source=legacy', array(&$this, 'output_legacy_file'), 10, 3);
    }

    /**
     * Get a unique filename for storing a file in the post's storage folder
     *
     * @param string $filename The original filename
     * @param int    $post_id  The post ID
     * @param mixed  $extra    Available extra information
     *
     * @return string A unique filename
     */
    public function unique_local_filename_from_files_param($filename, $post_id, $extra)
    {
        if (empty($filename) && isset($_FILES['cuar_file']))
        {
            $filename = $_FILES['cuar_file']['name'];
        }

        if (empty($filename)) return '';

        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');
        $path = $po_addon->get_private_storage_directory($post_id, true, false);
        $unique_filename = wp_unique_filename($path, $filename, null);

        return $unique_filename;
    }

    /**
     * Get a unique filename for storing a file in the post's storage folder
     *
     * @param string $filename The original filename
     * @param int    $post_id  The post ID
     * @param mixed  $extra    Available extra information
     *
     * @return string A unique filename
     */
    public function unique_local_filename($filename, $post_id, $extra)
    {
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');
        $path = $po_addon->get_private_storage_directory($post_id, true, false);
        $unique_filename = wp_unique_filename($path, $filename, null);

        return $unique_filename;
    }

    /**
     * Get a unique filename for storing a file in the post's storage folder. If the method is to leave the file in the original directory, we simply return
     * its current filename
     *
     * @param string $filename The original filename
     * @param int    $post_id  The post ID
     * @param mixed  $extra    Available extra information
     *
     * @return string A unique filename
     */
    public function unique_server_filename($filename, $post_id, $extra)
    {
        if (isset($extra['method']) && $extra['method'] == 'noop') return $filename;

        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');
        $path = $po_addon->get_private_storage_directory($post_id, true, false);
        $unique_filename = wp_unique_filename($path, $filename, null);

        return $unique_filename;
    }

    /**
     * Stream a local file to the client from the post's storage folder
     *
     * @param int    $post_id    The post ID
     * @param array  $found_file The file description
     * @param string $action     The action (download|view)
     */
    public function output_local_file($post_id, $found_file, $action)
    {
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');

        $filename = $found_file['file'];
        $filepath = $po_addon->get_private_file_path($filename, $post_id, false);

        $this->output_file($filepath, $filename, $action);
    }

    /**
     * Stream a server file to the client from its folder
     *
     * @param string $file_path The current file path
     * @param int    $post_id   The post ID
     * @param array  $file      The file description
     *
     * @return string
     */
    public function get_server_file_path($file_path, $post_id, $file)
    {
        if ($file['source'] != 'server') return $file_path;

        $filename = $file['file'];
        $is_protected = $file['extra']['is_protected'];
        if ($is_protected)
        {
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $file_path = $po_addon->get_private_file_path($filename, $post_id, false);
        }
        else
        {
            $file_path = $file['extra']['abs_path'];
        }

        return $file_path;
    }

    /**
     * Stream a server file to the client from its folder
     *
     * @param string $file_id The current file ID
     * @param array  $file    The file description
     *
     * @return string
     */
    public function get_server_file_id($file_id, $file)
    {
        if ($file['source'] != 'server') return $file_id;

        $is_protected = $file['extra']['is_protected'];
        if ( !$is_protected)
        {
            return md5($file['extra']['abs_path']);
        }

        return $file_id;
    }

    /**
     * Stream a server file to the client from its folder
     *
     * @param int    $post_id    The post ID
     * @param array  $found_file The file description
     * @param string $action     The action (download|view)
     */
    public function output_server_file($post_id, $found_file, $action)
    {
        $filename = $found_file['file'];
        $is_protected = $found_file['extra']['is_protected'];
        if ($is_protected)
        {
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $filepath = $po_addon->get_private_file_path($filename, $post_id, false);
        }
        else
        {
            $filepath = $found_file['extra']['abs_path'];
        }

        $this->output_file($filepath, $filename, $action);
    }

    /**
     * Stream a local file to the client from the post owner's storage folder (legacy file storage)
     *
     * @param int    $post_id    The post ID
     * @param array  $found_file The file description
     * @param string $action     The action (download|view)
     */
    public function output_legacy_file($post_id, $found_file, $action)
    {
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');

        $filename = $found_file['file'];
        $filepath = $po_addon->get_legacy_private_file_path($filename, $post_id, false);

        $this->output_file($filepath, $filename, $action);
    }

    /**
     * Remove all files from a post storage folder that have no corresponding post meta description
     *
     * @param int $post_id The post ID
     */
    public function remove_orphan_local_files($post_id)
    {
        /** @var CUAR_PrivateFileAddOn $pf_addon */
        $pf_addon = $this->plugin->get_addon('private-files');
        $attached_files = $pf_addon->get_attached_files($post_id);

        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');
        $path = $po_addon->get_private_storage_directory($post_id, true, false);
        $physical_files = file_exists($path) ? scandir($path) : array();

        foreach ($physical_files as $filename)
        {
            // Skip folders
            $file_path = $path . '/' . $filename;
            if ( !is_file($file_path)) continue;

            // If no corresponding file meta, we delete that file from the folder
            $file_meta = $pf_addon->get_attached_file_by_name($post_id, $filename, $attached_files);
            if ($file_meta == false)
            {
                unlink($file_path);
            }
        }
    }

    /**
     * Tell if a file on the local file system is missing
     *
     * @param bool  $result  The result of the test
     * @param int   $post_id The post ID
     * @param array $file    The file description
     *
     * @return bool true if the file is missing
     */
    public function is_local_file_missing($result, $post_id, $file)
    {
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');

        $folder = trailingslashit($po_addon->get_private_storage_directory($post_id, true, false));
        $file_path = $folder . $file['file'];

        return !file_exists($file_path);
    }

    /**
     * Tell if a file on the server is missing
     *
     * @param bool  $result  The result of the test
     * @param int   $post_id The post ID
     * @param array $file    The file description
     *
     * @return bool true if the file is missing
     */
    public function is_server_file_missing($result, $post_id, $file)
    {
        $filename = $file['file'];
        $is_protected = $file['is_protected'];
        if ($is_protected)
        {
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $filepath = $po_addon->get_private_file_path($filename, $post_id, false);
        }
        else
        {
            $filepath = $file['abs_path'];
        }

        return !file_exists($filepath);
    }

    /**
     * Move or copy a file from a server folder to the final directory
     *
     * @param array                 $errors           The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon         The private files add-on instance
     * @param string                $initial_filename The filename as it had been transmitted from AJAX
     * @param int                   $post_id          The post ID owning the files
     * @param string                $filename         The file name
     * @param string                $caption          The caption
     * @param string                $extra            Contains the method (move|copy|noop) and the file path
     *
     * @return array Errors if any
     */
    public function attach_server_file($errors, $pf_addon, $initial_filename, $post_id, $filename, $caption, $extra)
    {
        $supported_types = apply_filters('cuar/private-content/files/supported-types', null);
        if ($supported_types != null)
        {
            $arr_file_type = wp_check_filetype(basename($filename));
            $uploaded_type = $arr_file_type['type'];
            if ( !in_array($uploaded_type, $supported_types))
            {
                $errors[] = sprintf(__("This file type is not allowed. You can only upload: %s", 'cuar'), implode(', ', $supported_types));

                return $errors;
            }
        }

        if ( !isset($extra['method']))
        {
            $errors[] = __("A method must be specified (copy|move|noop) in the extra parameter", 'cuar');

            return $errors;
        }

        if ( !isset($extra['path']))
        {
            $errors[] = __("A path must be specified in the extra parameter", 'cuar');

            return $errors;
        }

        // If the method is to leave the file where it is, we do nothing
        if ($extra['method'] == 'noop')
        {
            return $errors;
        }

        // Else we copy/move the original file to the post's protected folder
        $src_folder = trailingslashit($extra['path']);
        $src_path = $src_folder . $initial_filename;

        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');
        $dest_folder = trailingslashit($po_addon->get_private_storage_directory($post_id, true, true));
        $dest_path = $dest_folder . $filename;

        if (@copy($src_path, $dest_path))
        {
            if ($extra['method'] == 'move')
            {
                @unlink($src_path);
            }
        }
        else
        {
            $errors[] = sprintf(__('An error happened while copying %1$s from the server folder %2$s', 'cuar'), $filename, $src_folder);
        }

        return $errors;
    }

    /**
     * Move or copy a file from the local FTP upload folder to the final directory
     *
     * @param array                 $errors           The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon         The private files add-on instance
     * @param string                $initial_filename The filename as it had been transmitted from AJAX
     * @param int                   $post_id          The post ID owning the files
     * @param string                $filename         The file name
     * @param string                $caption          The caption
     * @param string                $extra            Contains the FTP operation (ftp-move|ftp-copy)
     *
     * @return array Errors if any
     */
    public function attach_ftp_file($errors, $pf_addon, $initial_filename, $post_id, $filename, $caption, $extra)
    {
        $supported_types = apply_filters('cuar/private-content/files/supported-types', null);
        if ($supported_types != null)
        {
            $arr_file_type = wp_check_filetype(basename($filename));
            $uploaded_type = $arr_file_type['type'];
            if ( !in_array($uploaded_type, $supported_types))
            {
                $errors[] = sprintf(__("This file type is not allowed. You can only upload: %s", 'cuar'), implode(', ', $supported_types));

                return $errors;
            }
        }

        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');

        $src_folder = trailingslashit($pf_addon->get_ftp_path());
        $src_path = $src_folder . $initial_filename;

        $dest_folder = trailingslashit($po_addon->get_private_storage_directory($post_id, true, true));
        $dest_path = $dest_folder . $filename;

        if (@copy($src_path, $dest_path))
        {
            if ($extra == 'ftp-move')
            {
                @unlink($src_path);
            }
        }
        else
        {
            $errors[] = sprintf(__('An error happened while copying %s from the FTP folder', 'cuar'), $filename);
        }

        return $errors;
    }

    /**
     * @param array                 $errors           The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon         The private files add-on instance
     * @param string                $initial_filename The filename as it had been transmitted from AJAX
     * @param int                   $post_id          The post ID owning the files
     * @param string                $filename         The file name
     * @param string                $caption          The caption
     * @param string                $extra            Optional extra information
     *
     * @return array Errors if any
     */
    public function attach_uploaded_file($errors, $pf_addon, $initial_filename, $post_id, $filename, $caption, $extra)
    {
        $uploaded_file = isset($_FILES['cuar_file']) ? $_FILES['cuar_file'] : null;
        if (empty($uploaded_file))
        {
            $errors[] = sprintf(__('No file has been uploaded', 'cuar'), $filename);

            return $errors;
        }

        $supported_types = apply_filters('cuar/private-content/files/supported-types', null);
        if ($supported_types != null)
        {
            $arr_file_type = wp_check_filetype(basename($uploaded_file['name']));
            $uploaded_type = $arr_file_type['type'];
            if ( !in_array($uploaded_type, $supported_types))
            {
                $errors[] = sprintf(__("This file type is not allowed. You can only upload: %s", 'cuar'), implode(', ', $supported_types));

                return $errors;
            }
        }

        // Use the WordPress API to upload the file
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');

        // We will send files to a custom directory
        // Let WP handle the rest
        if ( !isset($_POST['post_ID']) || $_POST['post_ID'] < 0) $_POST['post_ID'] = $post_id;
        // if ( !isset($_POST['post_type']) || $_POST['post_type'] != 'cuar_private_file') $_POST['post_type'] = 'cuar_private_file';

        add_filter('upload_dir', array(&$this, 'custom_upload_dir'));
        $upload_result = wp_handle_upload($uploaded_file, array('test_form' => false));
        remove_filter('upload_dir', array(&$this, 'custom_upload_dir'));

        if (empty($upload_result))
        {
            $errors[] = sprintf(__('An unknown error happened while uploading your file.', 'cuar'));

            return $errors;
        }
        else if (isset($upload_result['error']))
        {
            $errors[] = sprintf(__('An error happened while uploading your file: %s', 'cuar'), $upload_result['error']);

            return $errors;
        }

        return $errors;
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
     * Physically remove a file attached to a post
     *
     * @param array $errors  The eventual errors
     * @param int   $post_id The post ID owning the files
     * @param array $file    The file description
     *
     * @return array Errors if any
     */
    public function remove_attached_local_file($errors, $post_id, $file)
    {
        /** @var CUAR_PrivateFileAddOn $pf_addon */
        $pf_addon = $this->plugin->get_addon('private-files');
        $file_path = $pf_addon->get_file_path($post_id, $file);
        if (@file_exists($file_path))
        {
            @unlink($file_path);
        }

        return $errors;
    }

    /**
     * Physically remove a file attached to a post
     *
     * @param array $errors  The eventual errors
     * @param int   $post_id The post ID owning the files
     * @param array $file    The file description
     *
     * @return array Errors if any
     */
    public function remove_attached_server_file($errors, $post_id, $file)
    {
        $filename = $file['file'];
        $is_protected = $file['is_protected'];
        if ($is_protected)
        {
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $file_path = $po_addon->get_private_file_path($filename, $post_id, false);
        }
        else
        {
            $file_path = $file['abs_path'];
        }

        if (@file_exists($file_path))
        {
            @unlink($file_path);
        }

        return $errors;
    }

    /**
     * Add our default methods to select files
     *
     * @param array $methods
     *
     * @return array
     */
    public function register_select_methods($methods)
    {
        $methods['classic-upload'] = array(
            'label'   => __('Classic upload', 'cuar'),
            'caption' =>
                __('Drag and drop files here, they will be uploaded to the private folder.', 'cuar')
        );

        $methods['ftp-folder'] = array(
            'label'   => __('Get from server FTP folder', 'cuar'),
            'caption' =>
                __('Select one or more files from the FTP folder, they will either be moved or copied to the private folder.', 'cuar')
        );

        return $methods;
    }

    /**
     * Display a form to upload files with the classic browser upload method
     */
    public function render_classic_upload_form($post_id)
    {
        $this->plugin->enable_library('jquery.fileupload');

        $template_suffix = is_admin() ? '-admin' : '-frontend';

        $template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            array(
                'private-attachments-add-classic-upload' . $template_suffix . '.template.php',
                'private-attachments-add-classic-upload.template.php'
            ),
            'templates');

        include($template);
    }

    /**
     * Display a form to move files from the local FTP upload folder
     */
    public function render_ftp_folder_form($post_id)
    {
        /** @var CUAR_PrivateFileAddOn $pf_addon */
        $pf_addon = $this->plugin->get_addon('private-files');

        $ftp_dir = trailingslashit($pf_addon->get_ftp_path());
        $ftp_files = array();
        if (@file_exists($ftp_dir))
        {
            $file_filter = apply_filters('cuar/private-content/files/ftp-folder-exclusions', array(
                '.htaccess'
            ));

            $ftp_files = @scandir($ftp_dir);
            foreach ($ftp_files as $key => $filename)
            {
                $file_path = $ftp_dir . '/' . $filename;
                if ( !is_file($file_path) || in_array($filename, $file_filter))
                {
                    unset($ftp_files[$key]);
                }
            }
        }

        $template_suffix = is_admin() ? '-admin' : '-frontend';

        $template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            array(
                'private-attachments-add-ftp-folder' . $template_suffix . '.template.php',
                'private-attachments-add-ftp-folder.template.php'
            ),
            'templates');

        include($template);
    }

    /**
     * Supporting function for displaying the dropdown select box
     * for empty FTP upload directory or not.
     * Adapted from http://stackoverflow.com/a/7497848/1177153
     */
    public function is_dir_empty($dir)
    {
        if ( !is_readable($dir)) return null;
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
     * Try to determine a MIME type from the file extension
     *
     * @param string $file_extension The file extension
     *
     * @return string A MIME type
     */
    private function get_mime_type_from_extension($file_extension)
    {
        $known_mime_types = array(
            "323"     => "text/h323",
            "acx"     => "application/internet-property-stream",
            "ai"      => "application/postscript",
            "aif"     => "audio/x-aiff",
            "aifc"    => "audio/x-aiff",
            "aiff"    => "audio/x-aiff",
            "asf"     => "video/x-ms-asf",
            "asr"     => "video/x-ms-asf",
            "asx"     => "video/x-ms-asf",
            "au"      => "audio/basic",
            "avi"     => "video/x-msvideo",
            "axs"     => "application/olescript",
            "bas"     => "text/plain",
            "bcpio"   => "application/x-bcpio",
            "bin"     => "application/octet-stream",
            "bmp"     => "image/bmp",
            "c"       => "text/plain",
            "cat"     => "application/vnd.ms-pkiseccat",
            "cdf"     => "application/x-cdf",
            "cer"     => "application/x-x509-ca-cert",
            "class"   => "application/octet-stream",
            "clp"     => "application/x-msclip",
            "cmx"     => "image/x-cmx",
            "cod"     => "image/cis-cod",
            "cpio"    => "application/x-cpio",
            "crd"     => "application/x-mscardfile",
            "crl"     => "application/pkix-crl",
            "crt"     => "application/x-x509-ca-cert",
            "csh"     => "application/x-csh",
            "css"     => "text/css",
            "dcr"     => "application/x-director",
            "der"     => "application/x-x509-ca-cert",
            "dir"     => "application/x-director",
            "dll"     => "application/x-msdownload",
            "dms"     => "application/octet-stream",
            "doc"     => "application/msword",
            "dot"     => "application/msword",
            "dvi"     => "application/x-dvi",
            "dxr"     => "application/x-director",
            "eps"     => "application/postscript",
            "etx"     => "text/x-setext",
            "evy"     => "application/envoy",
            "exe"     => "application/octet-stream",
            "fif"     => "application/fractals",
            "flr"     => "x-world/x-vrml",
            "gif"     => "image/gif",
            "gtar"    => "application/x-gtar",
            "gz"      => "application/x-gzip",
            "h"       => "text/plain",
            "hdf"     => "application/x-hdf",
            "hlp"     => "application/winhlp",
            "hqx"     => "application/mac-binhex40",
            "hta"     => "application/hta",
            "htc"     => "text/x-component",
            "htm"     => "text/html",
            "html"    => "text/html",
            "htt"     => "text/webviewhtml",
            "ico"     => "image/x-icon",
            "ief"     => "image/ief",
            "iii"     => "application/x-iphone",
            "ins"     => "application/x-internet-signup",
            "isp"     => "application/x-internet-signup",
            "jfif"    => "image/pipeg",
            "jpe"     => "image/jpeg",
            "jpeg"    => "image/jpeg",
            "jpg"     => "image/jpeg",
            "js"      => "application/x-javascript",
            "latex"   => "application/x-latex",
            "lha"     => "application/octet-stream",
            "lsf"     => "video/x-la-asf",
            "lsx"     => "video/x-la-asf",
            "lzh"     => "application/octet-stream",
            "m13"     => "application/x-msmediaview",
            "m14"     => "application/x-msmediaview",
            "m3u"     => "audio/x-mpegurl",
            "man"     => "application/x-troff-man",
            "mdb"     => "application/x-msaccess",
            "me"      => "application/x-troff-me",
            "mht"     => "message/rfc822",
            "mhtml"   => "message/rfc822",
            "mid"     => "audio/mid",
            "mny"     => "application/x-msmoney",
            "mov"     => "video/quicktime",
            "movie"   => "video/x-sgi-movie",
            "mp2"     => "video/mpeg",
            "mp3"     => "audio/mpeg",
            "mpa"     => "video/mpeg",
            "mpe"     => "video/mpeg",
            "mpeg"    => "video/mpeg",
            "mpg"     => "video/mpeg",
            "mpp"     => "application/vnd.ms-project",
            "mpv2"    => "video/mpeg",
            "ms"      => "application/x-troff-ms",
            "msg"     => "application/vnd.ms-outlook",
            "mvb"     => "application/x-msmediaview",
            "nc"      => "application/x-netcdf",
            "nws"     => "message/rfc822",
            "oda"     => "application/oda",
            "p10"     => "application/pkcs10",
            "p12"     => "application/x-pkcs12",
            "p7b"     => "application/x-pkcs7-certificates",
            "p7c"     => "application/x-pkcs7-mime",
            "p7m"     => "application/x-pkcs7-mime",
            "p7r"     => "application/x-pkcs7-certreqresp",
            "p7s"     => "application/x-pkcs7-signature",
            "pbm"     => "image/x-portable-bitmap",
            "pdf"     => "application/pdf",
            "pfx"     => "application/x-pkcs12",
            "pgm"     => "image/x-portable-graymap",
            "pko"     => "application/ynd.ms-pkipko",
            "pma"     => "application/x-perfmon",
            "pmc"     => "application/x-perfmon",
            "pml"     => "application/x-perfmon",
            "pmr"     => "application/x-perfmon",
            "pmw"     => "application/x-perfmon",
            "pnm"     => "image/x-portable-anymap",
            "pot"     => "application/vnd.ms-powerpoint",
            "ppm"     => "image/x-portable-pixmap",
            "pps"     => "application/vnd.ms-powerpoint",
            "ppt"     => "application/vnd.ms-powerpoint",
            "prf"     => "application/pics-rules",
            "ps"      => "application/postscript",
            "pub"     => "application/x-mspublisher",
            "qt"      => "video/quicktime",
            "ra"      => "audio/x-pn-realaudio",
            "ram"     => "audio/x-pn-realaudio",
            "ras"     => "image/x-cmu-raster",
            "rgb"     => "image/x-rgb",
            "rmi"     => "audio/mid",
            "roff"    => "application/x-troff",
            "rtf"     => "application/rtf",
            "rtx"     => "text/richtext",
            "scd"     => "application/x-msschedule",
            "sct"     => "text/scriptlet",
            "setpay"  => "application/set-payment-initiation",
            "setreg"  => "application/set-registration-initiation",
            "sh"      => "application/x-sh",
            "shar"    => "application/x-shar",
            "sit"     => "application/x-stuffit",
            "snd"     => "audio/basic",
            "spc"     => "application/x-pkcs7-certificates",
            "spl"     => "application/futuresplash",
            "src"     => "application/x-wais-source",
            "sst"     => "application/vnd.ms-pkicertstore",
            "stl"     => "application/vnd.ms-pkistl",
            "stm"     => "text/html",
            "sv4cpio" => "application/x-sv4cpio",
            "sv4crc"  => "application/x-sv4crc",
            "svg"     => "image/svg+xml",
            "swf"     => "application/x-shockwave-flash",
            "t"       => "application/x-troff",
            "tar"     => "application/x-tar",
            "tcl"     => "application/x-tcl",
            "tex"     => "application/x-tex",
            "texi"    => "application/x-texinfo",
            "texinfo" => "application/x-texinfo",
            "tgz"     => "application/x-compressed",
            "tif"     => "image/tiff",
            "tiff"    => "image/tiff",
            "tr"      => "application/x-troff",
            "trm"     => "application/x-msterminal",
            "tsv"     => "text/tab-separated-values",
            "txt"     => "text/plain",
            "uls"     => "text/iuls",
            "ustar"   => "application/x-ustar",
            "vcf"     => "text/x-vcard",
            "vrml"    => "x-world/x-vrml",
            "wav"     => "audio/x-wav",
            "wcm"     => "application/vnd.ms-works",
            "wdb"     => "application/vnd.ms-works",
            "wks"     => "application/vnd.ms-works",
            "wmf"     => "application/x-msmetafile",
            "wps"     => "application/vnd.ms-works",
            "wri"     => "application/x-mswrite",
            "wrl"     => "x-world/x-vrml",
            "wrz"     => "x-world/x-vrml",
            "xaf"     => "x-world/x-vrml",
            "xbm"     => "image/x-xbitmap",
            "xla"     => "application/vnd.ms-excel",
            "xlc"     => "application/vnd.ms-excel",
            "xlm"     => "application/vnd.ms-excel",
            "xls"     => "application/vnd.ms-excel",
            "xlt"     => "application/vnd.ms-excel",
            "xlw"     => "application/vnd.ms-excel",
            "xof"     => "x-world/x-vrml",
            "xpm"     => "image/x-xpixmap",
            "xwd"     => "image/x-xwindowdump",
            "z"       => "application/x-compress",
            "zip"     => "application/zip"
        );

        $file_extension = strtolower($file_extension);
        if (array_key_exists($file_extension, $known_mime_types))
        {
            return $known_mime_types[$file_extension];
        }

        return "application/octet-stream";
    }

    /**
     * Output the content of a file to the http stream
     *
     * @param string $filepath  The path to the file
     * @param string $filename  The name to give to the file
     * @param string $mime_type The mime type (determined automatically for well-known file types if blank)
     * @param string $action    view or download the file (hint to the browser)
     */
    private function output_file($filepath, $filename, $action = 'download', $mime_type = '')
    {
        if ( !is_readable($filepath))
        {
            wp_die('File not found or inaccessible at path: ' . $filepath);
        }

        $size = filesize($filepath);
        $filename = rawurldecode($filename);
        if ($mime_type == '')
        {
            $file_extension = pathinfo($filepath, PATHINFO_EXTENSION);
            $mime_type = $this->get_mime_type_from_extension($file_extension);
        }

        // Fix http://wordpress.org/support/topic/problems-with-image-files
        @ob_end_clean(); //turn off output buffering to decrease cpu usage
        @ob_clean();

        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

        header('Content-Type: ' . $mime_type);
        if ($action == 'view')
        {
            header('Content-Disposition: inline; filename="' . $filename . '"');
        }
        else
        {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');

        /* The three lines below basically make the	download non-cacheable */
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // multipart-download and download resuming support
        $range = null;
        if (isset($_SERVER['HTTP_RANGE']))
        {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(",", $range, 2);
            list($range, $range_end) = explode("-", $range);
            $range = intval($range);

            if ( !$range_end)
            {
                $range_end = $size - 1;
            }
            else
            {
                $range_end = intval($range_end);
            }

            $new_length = $range_end - $range + 1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range-$range_end/$size");
        }
        else
        {
            $new_length = $size;
            header("Content-Length: " . $size);
        }

        /* output the file itself */
        $chunk_size = 1 * (1024 * 1024); //you may want to change this
        $bytes_send = 0;
        if ($filepath = fopen($filepath, 'r'))
        {
            if ($range !== null)
            {
                fseek($filepath, $range);
            }

            while ( !feof($filepath) && ( !connection_aborted()) && ($bytes_send < $new_length))
            {
                $buffer = fread($filepath, $chunk_size);
                print($buffer); //echo($buffer); // is also possible
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($filepath);
        }
        else
        {
            wp_die('Error - can not open file: ' . $filepath);
        }

        die();
    }
}
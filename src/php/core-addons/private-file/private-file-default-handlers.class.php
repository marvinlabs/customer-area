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
        add_action('cuar/private-content/files/render-select-method?id=ftp-copy', array(&$this, 'render_ftp_copy_form'));
        add_action('cuar/private-content/files/render-select-method?id=ftp-move', array(&$this, 'render_ftp_move_form'));

        // Addition of files for the methods we manage
        add_filter('cuar/private-content/files/on-attach-file?method=ftp-copy', array(&$this, 'copy_ftp_file'), 10, 6);
        add_filter('cuar/private-content/files/on-attach-file?method=ftp-move', array(&$this, 'move_ftp_file'), 10, 6);
        add_filter('cuar/private-content/files/on-attach-file?method=classic-upload', array(&$this, 'upload_file'), 10, 6);

        // Handling of local files
        add_action('cuar/private-content/files/on-remove-attached-file?source=local', array(&$this, 'remove_attached_file'), 10, 3);
        add_filter('cuar/private-content/files/is-missing?source=local', array(&$this, 'is_local_file_missing'), 10, 3);
        add_action('cuar/private-content/files/remove-orphan-files?source=local', array(&$this, 'remove_orphan_files'), 10, 1);
    }

    /**
     * Remove all files from a post storage folder that have no corresponding post meta description
     *
     * @param int $post_id The post ID
     */
    public function remove_orphan_files($post_id)
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
     * Copy a file from the local FTP upload folder to the final storage directory
     *
     * @param array                 $errors   The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon The private files add-on instance
     * @param int                   $post_id  The post ID owning the files
     * @param string                $filename The file name
     * @param string                $caption  The caption
     * @param string                $extra    Optional extra information
     *
     * @return array Errors if any
     */
    public function copy_ftp_file($errors, $pf_addon, $post_id, $filename, $caption, $extra)
    {
        return $this->attach_ftp_file('copy', $errors, $pf_addon, $post_id, $filename, $caption, $extra);
    }

    /**
     * Move a file from the local FTP upload folder to the final storage directory
     *
     * @param array                 $errors   The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon The private files add-on instance
     * @param int                   $post_id  The post ID owning the files
     * @param string                $filename The file name
     * @param string                $caption  The caption
     * @param string                $extra    Optional extra information
     *
     * @return array Errors if any
     */
    public function move_ftp_file($errors, $pf_addon, $post_id, $filename, $caption, $extra)
    {
        return $this->attach_ftp_file('move', $errors, $pf_addon, $post_id, $filename, $caption, $extra);
    }

    /**
     * Move or copy a file from the local FTP upload folder to the final directory
     *
     * @param string                $ftp_operation
     * @param array                 $errors   The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon The private files add-on instance
     * @param int                   $post_id  The post ID owning the files
     * @param string                $filename The file name
     * @param string                $caption  The caption
     * @param string                $extra    Optional extra information
     *
     * @return array Errors if any
     */
    public function attach_ftp_file($ftp_operation, $errors, $pf_addon, $post_id, $filename, $caption, $extra)
    {
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');

        $src_folder = trailingslashit($pf_addon->get_ftp_path());
        $src_path = $src_folder . $filename;

        $dest_folder = trailingslashit($po_addon->get_private_storage_directory($post_id, true, true));
        $dest_filename = wp_unique_filename($dest_folder, $filename, null);
        $dest_path = $dest_folder . $dest_filename;

        if (copy($src_path, $dest_path))
        {
            if ($ftp_operation == 'move')
            {
                unlink($src_path);
            }
        }
        else
        {
            $errors[] = sprintf(__('An error happened while copying %s from the FTP folder', 'cuar'), $filename);
        }

        return $errors;
    }

    /**
     * @param array                 $errors   The eventual errors
     * @param CUAR_PrivateFileAddOn $pf_addon The private files add-on instance
     * @param int                   $post_id  The post ID owning the files
     * @param string                $filename The file name
     * @param string                $caption  The caption
     * @param string                $extra    Optional extra information
     *
     * @return array Errors if any
     */
    public function upload_file($errors, $pf_addon, $post_id, $filename, $caption, $extra)
    {
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
    public function remove_attached_file($errors, $post_id, $file)
    {
        /** @var CUAR_PrivateFileAddOn $pf_addon */
        $pf_addon = $this->plugin->get_addon('private-files');
        $file_path = $pf_addon->get_file_path($post_id, $file);
        if (file_exists($file_path))
        {
            unlink($file_path);
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
                __('Select the files you want to upload, they will be processed when you save the post.', 'cuar')
        );

        $methods['ftp-move'] = array(
            'label'   => __('Move from FTP folder', 'cuar'),
            'caption' =>
                __('Below are the files listed from the FTP folder you have set. Those files will be moved to their final location.', 'cuar')
        );

        $methods['ftp-copy'] = array(
            'label'   => __('Copy from FTP folder', 'cuar'),
            'caption' =>
                __('Below are the files listed from the FTP folder you have set. Those files will be copied to their final location.', 'cuar')
        );

        return $methods;
    }

    /**
     * Display a form to upload files with the classic browser upload method
     */
    public function render_classic_upload_form()
    {
        global $post;

        $template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            'private-files-upload-select-classic-upload.template.php',
            'templates');

        include($template);
    }

    /**
     * Display a form to move files from the local FTP upload folder
     */
    public function render_ftp_move_form() { $this->render_ftp_form('move'); }

    /**
     * Display a form to move files from the local FTP upload folder
     */
    public function render_ftp_copy_form() { $this->render_ftp_form('copy'); }

    /**
     * Display a form to move or copy files from the local FTP upload folder
     *
     * @param string $ftp_operation The type of operation we want to perform (move|copy)
     */
    private function render_ftp_form($ftp_operation)
    {
        global $post;

        /** @var CUAR_PrivateFileAddOn $pf_addon */
        $pf_addon = $this->plugin->get_addon('private-files');

        $ftp_dir = trailingslashit($pf_addon->get_ftp_path());
        $ftp_files = array();
        if (file_exists($ftp_dir))
        {
            $ftp_files = scandir($ftp_dir);
        }

        $template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            'private-files-upload-select-ftp-folder.template.php',
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
}
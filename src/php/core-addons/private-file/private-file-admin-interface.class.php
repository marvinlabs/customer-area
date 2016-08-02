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
 * Administation area for private files
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivateFileAdminInterface
{

    public function __construct($plugin, $private_file_addon)
    {
        $this->plugin = $plugin;
        $this->pf_addon = $private_file_addon;

        // Settings
        add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_settings_tab'), 520, 1);
        add_action('cuar/core/settings/print-settings?tab=cuar_private_files', array(&$this, 'print_settings'), 10, 2);
        add_filter('cuar/core/settings/validate-settings?tab=cuar_private_files', array(&$this, 'validate_options'), 10, 3);

        if ($this->pf_addon->is_enabled())
        {
            // File edit page
            add_action('add_meta_boxes', array(&$this, 'register_edit_page_meta_boxes'), 120);
            add_action('cuar/core/ownership/after-save-owner', array(&$this, 'do_save_post'), 10, 4);
            add_action('post_edit_form_tag', array(&$this, 'post_edit_form_tag'));
        }
    }

    /*------- CUSTOMISATION OF THE EDIT PAGE OF A PRIVATE FILES ------------------------------------------------------*/

    /**
     * Alter the edit form tag to say we have files to upload
     */
    public function post_edit_form_tag()
    {
        global $post;
        if ( !$post || get_post_type($post->ID) != 'cuar_private_file') return;
        echo ' enctype="multipart/form-data" autocomplete="off"';
    }

    /**
     * Register some additional boxes on the page to edit the files
     */
    public function register_edit_page_meta_boxes($post_type)
    {
        if ($post_type!='cuar_private_file') return;

        global $post;
        if ($post->post_author==get_current_user_id() || current_user_can('cuar_pf_manage_attachments'))
        {
            add_meta_box(
                'cuar_upload_metabox',
                __('Add file attachments', 'cuar'),
                array(&$this, 'print_upload_meta_box'),
                'cuar_private_file',
                'normal', 'high');
        }

        add_meta_box(
            'cuar_attachments_metabox',
            __('Attached Files', 'cuar'),
            array(&$this, 'print_attachments_meta_box'),
            'cuar_private_file',
            'normal', 'high');
    }

    /**
     * Print the metabox to upload a file
     */
    public function print_upload_meta_box()
    {
        wp_enqueue_script('cuar.admin');
        wp_nonce_field(plugin_basename(__FILE__), 'wp_cuar_nonce_file');

        do_action("cuar/private-content/files/before-upload-meta-box");

        global $post;
        $this->pf_addon->print_add_attachment_method_browser($post->ID);

        do_action("cuar/private-content/files/after-upload-meta-box");
    }

    /**
     * Print the metabox to upload a file
     */
    public function print_attachments_meta_box()
    {
        wp_enqueue_script('cuar.admin');
        do_action("cuar/private-content/files/before-attachments-meta-box");

        global $post;
        $this->pf_addon->print_current_attachments_manager($post->ID);
        $this->pf_addon->print_attachment_manager_scripts();

        do_action("cuar/private-content/files/after-attachments-meta-box");
    }

    /**
     * Callback to handle saving a post
     *
     * @param int     $post_id
     * @param WP_Post $post
     * @param array   $previous_owners
     * @param array   $new_owners
     */
    public function do_save_post($post_id, $post, $previous_owners, $new_owners)
    {
        global $post;

        // When auto-saving, we don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Only take care of our own post type
        if ( !$post || get_post_type($post->ID) != 'cuar_private_file') return;

        // Security check
        if ( !wp_verify_nonce($_POST['wp_cuar_nonce_file'], plugin_basename(__FILE__))) return;

        // Move the legacy files to the new storage folders
        $this->pf_addon->move_legacy_files($post_id, $previous_owners);

        // Remove files which are physically missing
        $this->pf_addon->remove_missing_files($post_id);

        // Delete physical files which are not registered in meta
        $this->pf_addon->remove_orphan_files($post_id);
    }

    /*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/

    public function add_settings_tab($tabs)
    {
        $tabs['cuar_private_files'] = __('Private Files', 'cuar');

        return $tabs;
    }

    /**
     * Add our fields to the settings page
     *
     * @param CUAR_Settings $cuar_settings The settings class
     */
    public function print_settings($cuar_settings, $options_group)
    {
        add_settings_section(
            'cuar_private_files_addon_general',
            __('General settings', 'cuar'),
            array(&$cuar_settings, 'print_empty_section_info'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG
        );

        add_settings_field(
            CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON,
            __('Enable add-on', 'cuar'),
            array(&$cuar_settings, 'print_input_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_private_files_addon_general',
            array(
                'option_id' => CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON,
                'type'      => 'checkbox',
                'after'     =>
                    __('Check this to enable the private files add-on.', 'cuar')
            )
        );

        add_settings_section(
            'cuar_private_files_addon_storage',
            __('File Storage', 'cuar'),
            array(&$this, 'print_storage_section_info'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG
        );

        add_settings_field(
            CUAR_PrivateFileAddOn::$OPTION_STORAGE_PATH,
            __('File storage', 'cuar'),
            array(&$cuar_settings, 'print_input_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_private_files_addon_storage',
            array(
                'option_id' => CUAR_PrivateFileAddOn::$OPTION_STORAGE_PATH,
                'type'      => 'text',
                'is_large'  => true,
                'after'     => $this->get_storage_setting_description()
            )
        );

        add_settings_field(
            CUAR_PrivateFileAddOn::$OPTION_FTP_PATH,
            __('FTP uploads folder', 'cuar'),
            array(&$cuar_settings, 'print_input_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_private_files_addon_storage',
            array(
                'option_id' => CUAR_PrivateFileAddOn::$OPTION_FTP_PATH,
                'type'      => 'text',
                'is_large'  => true,
                'after'     => $this->get_ftp_folder_setting_description()
            )
        );
    }

    /**
     * Validate our options
     *
     * @param CUAR_Settings $cuar_settings
     * @param array         $input
     * @param array         $validated
     *
     * @return array
     */
    public function validate_options($validated, $cuar_settings, $input)
    {
        // TODO OUTPUT ALLOWED FILE TYPES

        $cuar_settings->validate_boolean($input, $validated, CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON);

        // TODO: Would be good to have a validate_valid_folder function in CUAR_Settings class.
        $cuar_settings->validate_always($input, $validated, CUAR_PrivateFileAddOn::$OPTION_STORAGE_PATH);
        $cuar_settings->validate_not_empty($input, $validated, CUAR_PrivateFileAddOn::$OPTION_FTP_PATH);

        return $validated;
    }

    private function get_storage_setting_description()
    {
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon = $this->plugin->get_addon('post-owner');
        $storage_dir = $po_addon->get_base_private_storage_directory(true);

        $out = '<p>' . sprintf(__('<strong>Leave empty to use default folder</strong>:<br/><code>%s</code>', 'cuar'), $storage_dir) . '</p>';

        $out .= '<p class="description">';
        $out .= __('This folder is the root folder for storing private files. This should preferably be located outside of the web server public area or secured using a <code>.htaccess</code> file',
            'cuar');
        $out .= '</p>';

        $out .= $this->get_folder_proposed_actions($storage_dir, '770');

        return $out;
    }

    private function get_ftp_folder_setting_description()
    {
        $ftp_dir = $this->pf_addon->get_ftp_path();

        $out = '<p class="description">'
            . __('This folder can be used when you want to use files uploaded with FTP. This is handy when direct upload is failing for big files for instance.',
                'cuar')
            . '</p>';

        $out .= $this->get_folder_proposed_actions($ftp_dir, '770');

        return $out;
    }

    private function get_folder_proposed_actions($path, $permissions)
    {
        $out = '';

        // Folder does not exist.
        if ( !file_exists($path))
        {
            $out .= '<p class="cuar-error cuar-folder-action" data-path="' . esc_attr($path) . '" data-action="mkdir" data-extra="0' . $permissions
                . '" data-success-message="' . __('Save settings to check again', 'cuar') . '">';
            $out .= __('The folder does not exist.', 'cuar');

            // Propose to try create it
            $out .= '<br/><a href="#" class="button">';
            $out .= __('Create folder', 'cuar');
            $out .= '</a>';

            $out .= '</p>';

            return $out;
        }

        // Folder does not have proper permissions. Propose to change them?
        $current_perms = substr(sprintf('%o', fileperms($path)), -3);
        if ($permissions < $current_perms)
        {
            $out .= '<p class="cuar-error cuar-folder-action" data-path="' . esc_attr($path) . '" data-action="chmod" data-extra="0' . $permissions
                . '" data-success-message="' . __('Save settings to check again', 'cuar') . '">';
            $out .= sprintf(__('That directory should at least have the permissions set to %s. Currently it is %s. You should adjust that directory permissions as upload or download might not work properly.',
                'cuar'), $permissions, $current_perms);

            // Propose to try create it
            $out .= '<br/><a href="#" class="button">';
            $out .= __('Change permissions', 'cuar');
            $out .= '</a>';

            $out .= '</p>';
        }

        $accessible_from_web = $this->pf_addon->is_folder_accessible_from_web($path);
        if ($accessible_from_web !== false)
        {
            $out .= '<p class="cuar-error cuar-folder-action" data-path="' . esc_attr($path)
                . '" data-action="secure-htaccess" data-extra="" data-success-message="' . __('Save settings to check again', 'cuar') . '">';
            $out .= __('That directory seems to be accessible from the web. <strong>This is not safe</strong>. The most secure would be to move it outside of the web folder of your server',
                'cuar');
            $out .= '<br/>';
            $out .= $accessible_from_web;
            $out .= '<br/>';
            $out .= __('We can try to protect it with a <code>.htaccess</code> file with the button below, but <strong>this only works for Apache servers</strong> (will do nothing if you are running nginx or IIS)',
                'cuar');

            // Propose to try create it
            $out .= '<br/><a href="#" class="button">';
            $out .= __('Try to secure it', 'cuar');
            $out .= '</a>';

            $out .= '</p>';
        }

        return $out;
    }

    /**
     * Print some info about the section
     */
    public function print_storage_section_info()
    {
        wp_enqueue_script('cuar.admin');
        ?>
        <script type="text/javascript">
            <!--
            jQuery(document).ready(function ($) {
                $('.cuar-folder-action').ajaxFolderAction();
            });
            // -->
        </script>
        <?php
    }

    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PrivateFileAddOn */
    private $pf_addon;
}

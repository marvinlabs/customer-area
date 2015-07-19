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
        add_filter('cuar/core/permission-groups', array( &$this, 'get_configurable_capability_groups' ), 1000);
        add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_settings_tab'), 520, 1);
        add_action('cuar/core/settings/print-settings?tab=cuar_private_files', array(&$this, 'print_settings'), 10, 2);
        add_filter('cuar/core/settings/validate-settings?tab=cuar_private_files', array(&$this, 'validate_options'), 10, 3);

        if ($this->pf_addon->is_enabled())
        {
            // File edit page
            add_action('admin_menu', array(&$this, 'register_edit_page_meta_boxes'), 120);
            add_action('cuar/core/ownership/after-save-owner', array(&$this, 'do_save_post'), 10, 4);
            add_action('post_edit_form_tag', array(&$this, 'post_edit_form_tag'));
        }
    }

    /*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

    public function get_configurable_capability_groups( $capability_groups ) {
        $bo_caps = &$capability_groups['cuar_private_file']['groups']['back-office']['capabilities'];

        $bo_caps['cuar_pf_add_attachment'] = __( 'Add file attachment', 'cuar' );
        $bo_caps['cuar_pf_remove_attachment'] = __( 'Remove file attachment', 'cuar' );

        return $capability_groups;
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
    public function register_edit_page_meta_boxes()
    {
        add_meta_box(
            'cuar_upload_metabox',
            __('Add file attachments', 'cuar'),
            array(&$this, 'print_upload_meta_box'),
            'cuar_private_file',
            'normal', 'high');

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
        wp_nonce_field( plugin_basename(__FILE__), 'wp_cuar_nonce_file' );

        wp_enqueue_script('cuar.admin');

        do_action("cuar/private-content/files/before-upload-meta-box");

        /** @noinspection PhpUnusedLocalVariableInspection */
        global $post;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $select_methods = apply_filters('cuar/private-content/files/select-methods', array());

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            'private-attachments-add-methods-browser.template.php',
            'templates'));

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

        /** @noinspection PhpUnusedLocalVariableInspection */
        $attached_files = $this->pf_addon->get_attached_files($post->ID);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $attachment_item_template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            'private-attachments-list-item.template.php',
            'templates');

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/private-file',
            'private-attachments-list.template.php',
            'templates'));

        do_action("cuar/private-content/files/after-attachments-meta-box");
    }

    /**
     * Callback to handle saving a post
     *
     * @param int     $post_id
     * @param unknown $post
     * @param array   $previous_owner
     * @param array   $new_owner
     */
    public function do_save_post($post_id, $post, $previous_owner, $new_owner)
    {
        global $post;

        // When auto-saving, we don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Only take care of our own post type
        if ( !$post || get_post_type($post->ID) != 'cuar_private_file') return;

        // Security check
        if ( !wp_verify_nonce($_POST['wp_cuar_nonce_file'], plugin_basename(__FILE__))) return;

        // Move the legacy files to the new storage folders
        $this->pf_addon->move_legacy_files($post_id, $previous_owner);

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
            array(&$this, 'print_frontend_section_info'),
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

        if ( !file_exists($this->pf_addon->get_ftp_path()))
        {
            $folder_exists_message = '<span style="color: #c33;">'
                . __('This folder does not exist, please create it if you want to copy files from the FTP folder. Otherwise, you need not do anything.', 'cuar')
                . '</span>';
        }
        else
        {
            $folder_exists_message = "";
        }

        add_settings_field(
            CUAR_PrivateFileAddOn::$OPTION_FTP_PATH,
            __('FTP uploads folder', 'cuar'),
            array(&$cuar_settings, 'print_input_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_private_files_addon_general',
            array(
                'option_id' => CUAR_PrivateFileAddOn::$OPTION_FTP_PATH,
                'type'      => 'text',
                'is_large'  => true,
                'after'     => '<p class="description">'
                    . __('This folder can be used when you want to use files uploaded with FTP. This is handy when direct upload is failing for big files for instance.',
                        'cuar')
                    . $folder_exists_message
                    . '</p>'
            )
        );

        add_settings_section(
            'cuar_private_files_addon_storage',
            __('File Storage', 'cuar'),
            array(&$this, 'print_storage_section_info'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG
        );
    }

    /**
     * Validate our options
     *
     * @param CUAR_Settings $cuar_settings
     * @param array         $input
     * @param array         $validated
     */
    public function validate_options($validated, $cuar_settings, $input)
    {
        // TODO OUTPUT ALLOWED FILE TYPES

        $cuar_settings->validate_boolean($input, $validated, CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON);

        // TODO: Would be good to have a validate_valid_folder function in CUAR_Settings class.
        $cuar_settings->validate_not_empty($input, $validated, CUAR_PrivateFileAddOn::$OPTION_FTP_PATH);

        return $validated;
    }

    /**
     * Print some info about the section
     */
    public function print_frontend_section_info()
    {
        // echo '<p>' . __( 'Options for the private files add-on.', 'cuar' ) . '</p>';
    }

    /**
     * Print some info about the section
     */
    public function print_storage_section_info()
    {
        $po_addon = $this->plugin->get_addon('post-owner');
        $storage_dir = $po_addon->get_base_private_storage_directory(true);
        $sample_storage_dir = $po_addon->get_owner_storage_directory(array(get_current_user_id()), 'usr', true, true);

        $required_perms = '705';
        $current_perms = substr(sprintf('%o', fileperms($storage_dir)), -3);

        echo '<div class="cuar-section-description">';
        echo '<p>'
            . sprintf(__('The files will be stored in the following directory: <code>%s</code>.', 'cuar'),
                $storage_dir)
            . '</p>';

        echo '<p>'
            . sprintf(__('Each user has his own sub-directory. For instance, yours is: <code>%s</code>.', 'cuar'),
                $sample_storage_dir)
            . '</p>';

        if ($required_perms > $current_perms)
        {
            echo '<p style="color: orange;">'
                . sprintf(__('That directory should at least have the permissions set to 705. Currently it is '
                    . '%s. You should adjust that directory permissions as upload or download might not work '
                    . 'properly.', 'cuar'), $current_perms)
                . '</p>';
        }
        echo '</div>';
    }

    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PrivateFileAddOn */
    private $pf_addon;
}

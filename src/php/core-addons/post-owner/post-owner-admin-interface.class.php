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
 * Administration area for post ownership
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PostOwnerAdminInterface
{
    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PostOwnerAddOn */
    private $po_addon;

    /**
     * CUAR_PostOwnerAdminInterface constructor.
     *
     * @param CUAR_Plugin         $plugin
     * @param CUAR_PostOwnerAddOn $po_addon
     */
    public function __construct($plugin, $po_addon)
    {
        $this->plugin = $plugin;
        $this->po_addon = $po_addon;

        add_action('cuar/core/addons/after-init', array(&$this, 'customize_post_edit_pages'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
    }


    /*------- CUSTOMISATION OF THE EDIT PAGE FOR A POST WITH OWNER INFO --------------------------------------------------------------------------------------*/

    /**
     * Enqueues the select script on the user-edit and profile screens.
     */
    public function enqueue_scripts()
    {
        $screen = get_current_screen();
        $post_types = $this->plugin->get_content_post_types();

        if (isset($screen->id))
        {
            if (in_array($screen->id, $post_types))
            {
                $this->plugin->enable_library('jquery.select2');
            }
        }
    }

    public function customize_post_edit_pages()
    {
        add_action('add_meta_boxes', array(&$this, 'register_post_edit_meta_boxes'));

        $private_post_types = $this->plugin->get_content_post_types();
        foreach ($private_post_types as $pt)
        {
            add_action('save_post_' . $pt, array(&$this, 'do_save_post'), 10, 2);
        }
    }

    /**
     * Register some additional boxes on the page to edit the files
     */
    public function register_post_edit_meta_boxes($post_type)
    {
        $post_types = $this->plugin->get_content_post_types();
        foreach ($post_types as $type)
        {
            if ($post_type != $type) continue;

            add_meta_box(
                'cuar_post_owner',
                __('Assignment', 'cuar'),
                array(&$this, 'print_owner_meta_box'),
                $type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Print the metabox to select the owner of the file
     */
    public function print_owner_meta_box()
    {
        global $post;

        do_action("cuar/core/ownership/before-owner-meta-box");

        $owners = $this->po_addon->get_post_owners($post->ID);        
        $this->po_addon->print_owner_fields($owners);

        do_action("cuar/core/ownership/after-owner-meta-box");
    }

    /**
     * Callback to handle saving a post
     *
     * @param int    $post_id
     * @param string $post
     *
     * @return int
     */
    public function do_save_post($post_id, $post = null)
    {
        global $post;

        // When auto-saving, we don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

        // Only take care of private post types
        $private_post_types = $this->plugin->get_content_post_types();
        if ( !$post || !in_array(get_post_type($post->ID), $private_post_types)) return $post_id;

        // Save the owner details
        if ( !wp_verify_nonce($_POST['wp_cuar_nonce_owner'], 'cuar_save_owners')) return $post_id;

        // Save owner details
        $new_owners = $this->po_addon->get_owners_from_post_data();
        $this->po_addon->save_post_owners($post_id, $new_owners);

        return $post_id;
    }

}

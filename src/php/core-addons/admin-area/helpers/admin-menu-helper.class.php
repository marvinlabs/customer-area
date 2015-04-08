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


class CUAR_AdminMenuHelper
{
    private static $MENU_SEPARATOR = '<span class="cuar-menu-divider"></span>';

    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_AdminAreaAddOn */
    private $aa_addon;

    public function __construct($plugin, $aa_addon)
    {
        $this->plugin = $plugin;
        $this->aa_addon = $aa_addon;

        add_action('admin_menu', array(&$this, 'build_admin_menu'));
    }

    /**
     * Build the administration menu
     */
    public function build_admin_menu()
    {

        // Add the top-level admin menu
        $main_menu_slug = $this->add_main_menu_item();

        $submenus = array(
            // Add one menu item per private content type
            $this->get_private_types_menu_items(),

            // Add one menu item per container type
            apply_filters('cuar/core/admin/container-types-menu-pages', array()),

            // Add one menu item per owner type
            apply_filters('cuar/core/admin/owner-types-menu-pages', array()),

            // Add one menu item per extra page
            apply_filters('cuar/core/admin/owner-types-menu-pages', array()),
        );

        foreach ($submenus as $submenu_items)
        {
            $separator_added = false;
            foreach ($submenu_items as $item)
            {
                $submenu_page_title = $item['page_title'];
                $submenu_title = $item['title'];
                $submenu_slug = $item['slug'];
                $submenu_function = $item['function'];
                $submenu_capability = $item['capability'];

                if ( !$separator_added)
                {
                    $separator_added = true;
                    $submenu_title = self::$MENU_SEPARATOR . $submenu_title;
                }

                add_submenu_page($main_menu_slug, $submenu_page_title, $submenu_title,
                    $submenu_capability, $submenu_slug, $submenu_function);
            }
        }
    }

    /**
     * Add the main menu item
     * @return string The main menu item slug
     */
    private function add_main_menu_item()
    {
        $page_title = __('WP Customer Area', 'cuar');
        $menu_title = __('WP Customer Area', 'cuar');
        $menu_slug = 'customer-area';
        $capability = 'view-customer-area-menu';
        $function = array($this->aa_addon, 'print_dashboard');
        $icon = "";
        $position = '2.1.cuar';

        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon, $position);

        return $menu_slug;
    }

    /**
     * Get all submenu items corresponding to private content type listing
     * @return array
     */
    private function get_private_types_menu_items()
    {
        $items = array();

        $private_types = $this->plugin->get_private_types();
        foreach ($private_types as $type => $desc)
        {
            $post_type = get_post_type_object($type);
            if (current_user_can($post_type->cap->edit_post) || current_user_can($post_type->cap->read_post))
            {
                $items[] = array(
                    'page_title' => $desc['label-plural'],
                    'title'      => $desc['label-plural'],
                    'slug'       => $type,
                    'function'   => array($this->aa_addon, 'print_content_list_page'),
                    'capability' => 'administrator'
                );
            }
        }

        // Sort alphabetically
        usort($items, function ($a, $b)
        {
            return strcmp($a['title'], $b['title']);
        });

        return apply_filters('cuar/core/admin/submenu-items?group=private-types', $items);
    }

    /**
     * Get all submenu items corresponding to private content type listing
     * @return array
     */
    private function get_owner_types_menu_items()
    {
        $items = array();

        $groups = array(
            'content'   => $this->plugin->get_content_types(),
            'container' => $this->plugin->get_container_types()
        );

        foreach ($groups as $group => $private_types)
        {
            foreach ($private_types as $type => $desc)
            {
                $post_type = get_post_type_object($type);
                if (current_user_can($post_type->cap->edit_post) || current_user_can($post_type->cap->read_post))
                {
                    $items[] = array(
                        'page_title' => $desc['label-plural'],
                        'title'      => $desc['label-plural'],
                        'slug'       => $type,
                        'function'   => array($this->aa_addon, 'print_' . $group . '_list_page'),
                        'capability' => 0
                    );
                }
            }
        }

        // Sort alphabetically
        usort($items, function ($a, $b)
        {
            return strcmp($a['title'], $b['title']);
        });

        return apply_filters('cuar/core/admin/submenu-items?group=private-types', $items);
    }
}

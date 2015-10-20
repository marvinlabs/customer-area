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
    private static $MENU_SLUG = 'wpca';
    private static $MENU_SEPARATOR = '<span class="cuar-menu-divider"></span>';

    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_AdminAreaAddOn */
    private $aa_addon;

    private $private_types_items = null;
    private $tools_items = null;
    private $users_items = null;
    private $frontoffice_items = null;

    public function __construct($plugin, $aa_addon)
    {
        $this->plugin = $plugin;
        $this->aa_addon = $aa_addon;

        if (is_admin() &&
            (!$this->aa_addon->is_admin_area_access_restricted() || $this->aa_addon->current_user_can_access_admin()))
        {
            add_action('admin_menu', array(&$this, 'build_admin_menu'));
        }

        add_action('admin_bar_menu', array(&$this, 'build_adminbar_menu'), 32);
    }

    /**
     * Build the admin bar menu
     *
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function build_adminbar_menu($wp_admin_bar)
    {
        $wp_admin_bar->add_menu(array(
            'id'    => 'customer-area',
            'title' => __('WP Customer Area', 'cuar'),
            'href'  => admin_url('admin.php?page=wpca')
        ));

        $wp_admin_bar->add_menu(array(
            'parent' => 'customer-area',
            'id'     => 'wpca-frontoffice',
            'title'  => __('Your private area', 'cuar'),
            'href'   => '#'
        ));

        $submenus = array(
            $this->get_frontoffice_menu_items(),
            $this->get_private_types_menu_items(),
            $this->get_users_menu_items(),
            $this->get_tools_menu_items()
        );

        foreach ($submenus as $submenu_items)
        {
            foreach ($submenu_items as $item)
            {
                if (isset($item['capability']) && !current_user_can($item['capability'])) continue;

                $main_id = sanitize_title($item['slug']);
                $wp_admin_bar->add_menu(array(
                    'parent' => isset($item['parent']) ? $item['parent'] : 'customer-area',
                    'id'     => $main_id,
                    'title'  => $item['title'],
                    'href'   => isset($item['href']) ? $item['href'] : '#'
                ));

                if ( !isset($item['children']))
                {
                    continue;
                }

                foreach ($item['children'] as $subitem)
                {
                    if (isset($subitem['capability']) && !current_user_can($subitem['capability'])) continue;

                    $sub_id = sanitize_title($subitem['slug']);
                    $wp_admin_bar->add_menu(array(
                        'parent' => isset($subitem['parent']) ? $subitem['parent'] : $main_id,
                        'id'     => $sub_id,
                        'title'  => $subitem['title'],
                        'href'   => isset($subitem['href']) ? $subitem['href'] : '#'
                    ));
                }
            }
        }

        if (current_user_can('manage_options'))
        {
            $wp_admin_bar->add_menu(array(
                'parent' => 'customer-area',
                'id'     => 'customer-area-addons',
                'title'  => __('Add-ons', 'cuar'),
                'href'   => admin_url('admin.php?page=wpca&tab=addons')
            ));
        }
    }

    /**
     * Build the administration menu
     */
    public function build_admin_menu()
    {
        // Add the top-level admin menu
        $this->add_main_menu_item();

        $submenus = array(
            $this->get_private_types_menu_items(),
            $this->get_users_menu_items(),
            $this->get_tools_menu_items()
        );

        foreach ($submenus as $submenu_items)
        {
            $separator_added = false;
            foreach ($submenu_items as $item)
            {
                if (isset($item['adminbar-only']) && $item['adminbar-only'])
                {
                    continue;
                }

                $submenu_function = null;
                if (strstr($item['slug'], '.php') === false)
                {
                    $submenu_function = isset($item['function'])
                        ? $item['function']
                        : array($this->aa_addon, 'print_admin_page');
                }

                $submenu_title = $item['title'];
                if ( !$separator_added)
                {
                    $separator_added = true;
                    $submenu_title = self::$MENU_SEPARATOR . $submenu_title;
                }

                add_submenu_page(self::$MENU_SLUG,
                    $item['page_title'],
                    $submenu_title,
                    $item['capability'],
                    $item['slug'],
                    $submenu_function);
            }
        }
    }

    /**
     * Add the main menu item
     * @return string The main menu item slug
     */
    private function add_main_menu_item()
    {
        add_menu_page(__('WP Customer Area', 'cuar'),
            __('Customer Area', 'cuar'),
            'view-customer-area-menu',
            self::$MENU_SLUG, null, '', '2.1.cuar');

        add_submenu_page(self::$MENU_SLUG,
            __('About WP Customer Area', 'cuar'),
            __('About', 'cuar'),
            'view-customer-area-menu',
            self::$MENU_SLUG,
            array($this->aa_addon, 'print_admin_page')
        );
    }

    /**
     * Get all submenu items corresponding to private content type listing
     * @return array
     */
    private function get_private_types_menu_items()
    {
        if ($this->private_types_items == null)
        {
            $this->private_types_items = array();

            $types = array(
                'content'   => $this->plugin->get_content_types(),
                'container' => $this->plugin->get_container_types()
            );

            foreach ($types as $t => $private_types)
            {
                foreach ($private_types as $type => $desc)
                {
                    if ( !$this->plugin->is_type_managed($type, $private_types))
                    {
                        continue;
                    }

                    $post_type = get_post_type_object($type);
                    $taxonomies = get_object_taxonomies($post_type->name, 'object');

                    if (current_user_can($post_type->cap->edit_post) || current_user_can($post_type->cap->read_post))
                    {
                        $item = array(
                            'page_title' => $desc['label-plural'],
                            'title'      => $desc['label-plural'],
                            'slug'       => 'wpca-list,' . $t . ',' . $type,
                            'capability' => 'view-customer-area-menu'
                        );

                        if (current_user_can($post_type->cap->read_post))
                        {
                            $item['children'] = array();
                            $item['children'][] = array(
                                'title' => sprintf(__('All %s', 'cuar'), strtolower($desc['label-plural'])),
                                'slug'  => 'list-' . $type,
                                'href'  => admin_url('admin.php?page=wpca-list,' . $t . ',' . $type)
                            );
                        }

                        if (current_user_can($post_type->cap->edit_post))
                        {
                            $item['children'][] = array(
                                'title' => sprintf(__('New %s', 'cuar'), strtolower($desc['label-singular'])),
                                'slug'  => 'new-' . $type,
                                'href'  => admin_url('post-new.php?post_type=' . $type)
                            );
                        }

                        foreach ($taxonomies as $tax_slug => $tax)
                        {
                            if (current_user_can($tax->cap->manage_terms))
                            {
                                $item['children'][] = array(
                                    'title' => sprintf(__('Manage %s', 'cuar'), strtolower(__($tax->labels->name, 'cuar'))),
                                    'slug'  => 'manage-' . $tax_slug,
                                    'href'  => admin_url('edit-tags.php?taxonomy=' . $tax_slug)
                                );
                            }
                        }

                        $this->private_types_items[] = $item;
                    }
                }
            }

            $this->private_types_items = apply_filters('cuar/core/admin/submenu-items?group=private-types', $this->private_types_items);

            // Sort alphabetically
            usort($this->private_types_items, array('CUAR_AdminMenuHelper', 'sort_menu_items_by_title'));
        }

        return $this->private_types_items;
    }

    /**
     * Get all submenu items corresponding to user management (groups, CRM, ...)
     * @return array
     */
    private function get_users_menu_items()
    {
        if ($this->users_items == null)
        {
            $this->users_items = array();
            $this->users_items = apply_filters('cuar/core/admin/submenu-items?group=users', $this->users_items);

            // Sort alphabetically
            usort($this->users_items, array('CUAR_AdminMenuHelper', 'sort_menu_items_by_title'));
        }

        return $this->users_items;
    }

    /**
     * Get all submenu items corresponding to front-office listing
     * @return array
     */
    private function get_frontoffice_menu_items()
    {
        if ($this->frontoffice_items == null)
        {
            $this->frontoffice_items = array();
            $this->frontoffice_items = apply_filters('cuar/core/admin/submenu-items?group=frontoffice', $this->frontoffice_items);

            // Sort alphabetically
            usort($this->frontoffice_items, array('CUAR_AdminMenuHelper', 'sort_menu_items_by_title'));
        }

        return $this->frontoffice_items;
    }

    /**
     * Get all submenu items corresponding to private content type listing
     * @return array
     */
    private function get_tools_menu_items()
    {
        if ($this->tools_items == null)
        {
            $this->tools_items = array();
            $this->tools_items = apply_filters('cuar/core/admin/submenu-items?group=tools', $this->tools_items);

            // Sort alphabetically
            usort($this->tools_items, array('CUAR_AdminMenuHelper', 'sort_menu_items_by_title'));
        }

        return $this->tools_items;
    }

    /**
     * Sort a menu item by title
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    public static function sort_menu_items_by_title($a, $b)
    {
        return strcmp($a['title'], $b['title']);
    }
}

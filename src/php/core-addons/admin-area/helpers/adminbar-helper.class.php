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
 * Take care of the admin bar menu
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_AdminBarHelper
{
    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_AdminAreaAddOn */
    private $aa_addon;

    public function __construct($plugin, $aa_addon)
    {
        $this->plugin = $plugin;
        $this->aa_addon = $aa_addon;

        // TODO Enable it when it works with new admin structure
        // add_action('admin_bar_menu', array(&$this, 'build_adminbar_menu'), 32);
        add_action('show_admin_bar', array(&$this, 'restrict_admin_bar'));
    }

    /**
     * If necessary, hide the admin bar to some users
     * @return bool true if the admin bar is visible
     */
    public function restrict_admin_bar()
    {
        if ( !is_user_logged_in()
            || ($this->aa_addon->is_admin_area_access_restricted() && !current_user_can('cuar_view_top_bar'))
        )
        {
            return false;
        }

        return true;
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
            'href'  => admin_url('admin.php?page=customer-area')
        ));

        $wp_admin_bar->add_menu(array(
            'parent' => 'customer-area',
            'id'     => 'customer-area-front-office',
            'title'  => __('Your private area', 'cuar'),
            'href'   => '#'
        ));

        $submenu_items = apply_filters('cuar/core/admin/adminbar-menu-items', array());
        foreach ($submenu_items as $item)
        {
            $wp_admin_bar->add_menu($item);
        }

        if (current_user_can('manage_options'))
        {
            $wp_admin_bar->add_menu(array(
                'parent' => 'customer-area',
                'id'     => 'customer-area-settings',
                'title'  => __('Settings', 'cuar'),
                'href'   => admin_url('admin.php?page=cuar-settings')
            ));
        }

        if (current_user_can('manage_options'))
        {
            $wp_admin_bar->add_menu(array(
                'parent' => 'customer-area',
                'id'     => 'customer-area-addons',
                'title'  => __('Add-ons', 'cuar'),
                'href'   => admin_url('admin.php?page=customer-area&tab=addons')
            ));
        }
    }
}


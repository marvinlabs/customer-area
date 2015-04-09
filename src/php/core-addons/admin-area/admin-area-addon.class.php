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
include_once(CUAR_INCLUDES_DIR . '/core-addons/admin-area/private-content-table.class.php');
require_once(CUAR_INCLUDES_DIR . '/core-addons/admin-area/helpers/adminbar-helper.class.php');
require_once(CUAR_INCLUDES_DIR . '/core-addons/admin-area/helpers/admin-menu-helper.class.php');


/**
 * Add-on to organise the administration area (menus, ...)
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_AdminAreaAddOn extends CUAR_AddOn
{
    /** @var CUAR_AdminMenuHelper */
    private $admin_menu_helper;

    /** @var CUAR_AdminBarHelper */
    private $adminbar_helper;

    public function __construct()
    {
        parent::__construct('admin-area', '6.0.0');
    }

    public function get_addon_name()
    {
        return __('Administration Area', 'cuar');
    }

    public function run_addon($plugin)
    {
        $this->adminbar_helper = new CUAR_AdminBarHelper($plugin, $this);

        if (is_admin())
        {
            $this->admin_menu_helper = new CUAR_AdminMenuHelper($plugin, $this);

            add_action('cuar/core/on-plugin-update', array(&$this, 'plugin_version_upgrade'), 10, 2);
            add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'), 5);
            add_action('admin_init', array(&$this, 'restrict_admin_access'), 1);

            // Settings
            add_action('cuar/core/settings/print-settings?tab=cuar_core', array(&$this, 'print_core_settings'), 20,
                2);
            add_filter('cuar/core/settings/validate-settings?tab=cuar_core', array(&$this, 'validate_core_options'),
                20, 3);
        }
        else
        {
        }
    }

    /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

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
        $defaults[self::$OPTION_RESTRICT_ADMIN_AREA_ACCESS] = false;

        return $defaults;
    }

    /**
     * Is admin area access restriction enabled?
     * @return boolean true if the admin area access is enabled and controlled by permissions
     */
    public function is_admin_area_access_restricted()
    {
        return $this->plugin->get_option(self::$OPTION_RESTRICT_ADMIN_AREA_ACCESS);
    }

    /**
     * Add our fields to the settings page
     *
     * @param CUAR_Settings $cuar_settings The settings class
     */
    public function print_core_settings($cuar_settings, $options_group)
    {
        add_settings_field(
            self::$OPTION_RESTRICT_ADMIN_AREA_ACCESS,
            __('Restrict WP admin area', 'cuar'),
            array(&$cuar_settings, 'print_input_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_general_settings',
            array(
                'option_id' => self::$OPTION_RESTRICT_ADMIN_AREA_ACCESS,
                'type'      => 'checkbox',
                'after'     => __('Restrict the access to the administration panel of WordPress.', 'cuar')
                    . '<p class="description">'
                    . sprintf(__('If you check this box, you will be able to control access to various WordPress administrative features (the area under /wp-admin/, the top bar, etc.). '
                        . 'Once enabled, you can select which roles will be allowed to access it in the <a href="%1$s">general permissions tab</a>.',
                        'cuar'),
                        admin_url('admin.php?page=cuar-settings&cuar_tab=cuar_capabilities'))
                    . '</p>'
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
    public function validate_core_options($validated, $cuar_settings, $input)
    {
        $cuar_settings->validate_boolean($input, $validated, self::$OPTION_RESTRICT_ADMIN_AREA_ACCESS);

        return $validated;
    }

    private static $OPTION_RESTRICT_ADMIN_AREA_ACCESS = 'cuar_restrict_admin_area_access';

    /*------- OTHER FUNCTIONS ----------------------------------------------------------------------------------------*/

    /**
     * Declare the configurable capabilities for Customer Area
     *
     * @param array $capability_groups The capability groups
     *
     * @return array The updated capability groups
     */
    public function get_configurable_capability_groups($capability_groups)
    {
        $capability_groups['cuar_general'] = array(
            'label'  => __('General', 'cuar'),
            'groups' => array()
        );


        if ($this->is_admin_area_access_restricted())
        {
            $capability_groups['cuar_general']['groups'] = array(
                'back-office'  => array(
                    'group_name'   => __('Back-office', 'cuar'),
                    'capabilities' => array(
                        'cuar_access_admin_panel' => __('Access the WordPress admin area', 'cuar'),
                        'view-customer-area-menu' => __('View the Customer Area menu', 'cuar'),
                    )
                ),
                'front-office' => array(
                    'group_name'   => __('Front-office', 'cuar'),
                    'capabilities' => array(
                        'cuar_view_top_bar' => __('See the WordPress top bar', 'cuar'),
                    )
                )
            );
        }

        return $capability_groups;
    }

    /**
     * Restrict access to the administration panel to users having the permission to do so. If the access is forbidden,
     * the user gets redirected to the customer area home.
     */
    public function restrict_admin_access()
    {
        if ($this->is_admin_area_access_restricted()
            && !current_user_can('cuar_access_admin_panel')
            && $_SERVER['PHP_SELF'] != '/wp-admin/admin-ajax.php'
        )
        {

            $cp_addon = $this->plugin->get_addon('customer-pages');
            $customer_area_url = apply_filters('cuar/core/admin/admin-area-forbidden-redirect-url',
                $cp_addon->get_page_url("customer-home"));

            if (isset($customer_area_url))
            {
                wp_redirect($customer_area_url);
            }
            else
            {
                wp_redirect(home_url());
            }
        }
    }

    /**
     * Print a page that lists private content
     */
    public function print_container_list_page()
    {
        $this->print_private_post_list_page('container');
    }

    /**
     * Print a page that lists private content
     */
    public function print_content_list_page()
    {
        $this->print_private_post_list_page('content');
    }

    /**
     * Print a page that lists private content
     *
     * @param string $private_type_group (container|content)
     */
    protected function print_private_post_list_page($private_type_group)
    {
        $post_type = $_GET['page'];
        $post_type_object = get_post_type_object($post_type);

        $title_links = array();
        $title_links = $this->add_new_post_link($post_type_object, $post_type, $title_links);
        $title_links = $this->add_manage_taxonomy_links($post_type, $title_links);

        switch ($private_type_group) {
            case 'content':
                $title_links = apply_filters('cuar/core/admin/content-list-page/title-links?post_type=' . $post_type,
                    $title_links);
                $list_table = apply_filters('cuar/core/admin/content-list-page/list-table-object?post_type=' . $post_type, null);
                $default_list_table_class = 'CUAR_PrivateContentTable';
                break;

            case 'container':
                $title_links = apply_filters('cuar/core/admin/container-list-page/title-links?post_type=' . $post_type,
                    $title_links);
                $list_table = apply_filters('cuar/core/admin/container-list-page/list-table-object?post_type=' . $post_type, null);
                $default_list_table_class = 'CUAR_PrivateContentTable';
                break;

            default:
                wp_die('Unknown private type. Must either be container or content');
                exit;
        }

        if ($list_table == null)
        {
            $list_table = new $default_list_table_class($this->plugin, array(
                'plural'   => $post_type_object->labels->name,
                'singular' => $post_type_object->labels->singular_name,
                'ajax'     => false
            ), $post_type);
        }

        $list_table->process_bulk_action();
        $list_table->prepare_items();

        $default_filter_template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/admin-area',
            'private-post-list-default-filters-' . $private_type_group . '.template.php',
            'templates',
            'private-post-list-default-filters.template.php');

        /** @noinspection PhpIncludeInspection */
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/admin-area',
            'private-post-list-page.template.php',
            'templates'));
    }

    /**
     * Print the dashboard/about page
     */
    public function print_dashboard()
    {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'blog';
        $tabs = array(
            'whats-new' => array(
                'url'   => admin_url('admin.php?page=customer-area&tab=whats-new'),
                'label' => __("What's new", 'cuar')
            ),
            'changelog' => array(
                'url'   => admin_url('admin.php?page=customer-area&tab=changelog'),
                'label' => __("Change log", 'cuar')
            ),
            'blog'      => array(
                'url'   => admin_url('admin.php?page=customer-area&tab=blog'),
                'label' => __("Live from the blog", 'cuar')
            ),
            'addons'    => array(
                'url'   => admin_url('admin.php?page=customer-area&tab=addons'),
                'label' => __("Add-ons &amp; themes", 'cuar')
            ),
        );

        $page_title = sprintf(__('Welcome to WP Customer Area %s', 'cuar'), $this->plugin->get_major_version());
        $hero_message_primary = sprintf(__('<strong>WP Customer Area</strong> %s is more powerful, stable and secure than ever before. We hope you will enjoy using it.',
            'cuar'), $this->plugin->get_major_version());
        $tab_content_template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/admin-area',
            'dashboard-part-' . $current_tab . '.template.php',
            'templates');

        /** @noinspection PhpIncludeInspection */
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/admin-area',
            'dashboard-page.template.php',
            'templates'));
    }

    /**
     * When the plugin is upgraded
     *
     * @param unknown $from_version
     * @param unknown $to_version
     */
    public function plugin_version_upgrade($from_version, $to_version)
    {
        // If upgrading from before 1.5.0 we must add some caps to admin & editors
        if (version_compare($from_version, '1.5.0', '<'))
        {
            $admin_role = get_role('administrator');
            if ($admin_role)
            {
                $admin_role->add_cap('view-customer-area-menu');
            }
            $editor_role = get_role('editor');
            if ($editor_role)
            {
                $editor_role->add_cap('view-customer-area-menu');
            }
        }
    }

    /**
     * @param $post_type_object
     * @param $post_type
     * @param $title_links
     *
     * @return mixed
     */
    private function add_new_post_link($post_type_object, $post_type, $title_links)
    {
        if (current_user_can($post_type_object->cap->edit_posts))
        {
            $title_links[__('Add new', 'cuar')] = admin_url('post-new.php?post_type=' . $post_type);
        }

        return $title_links;
    }

    /**
     * @param $post_type
     * @param $title_links
     *
     * @return mixed
     */
    private function add_manage_taxonomy_links($post_type, $title_links)
    {
        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
        foreach ($taxonomies as $tax) {
            if (current_user_can($tax->cap->manage_terms))
            {
                $title_links[sprintf(__('Manage %1$s', 'cuar'), $tax->labels->name)] = admin_url('edit-tags.php?taxonomy=' . $tax->name);
            }
        }

        return $title_links;
    }

}

// Make sure the addon is loaded
new CUAR_AdminAreaAddOn();


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

if (!class_exists('CUAR_AdminAreaAddOn')) :

    /**
     * Add-on to organise the administration area (menus, ...)
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_AdminAreaAddOn extends CUAR_AddOn
    {

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
            add_action('admin_bar_menu', array(&$this, 'build_adminbar_menu'), 32);
            add_action('show_admin_bar', array(&$this, 'restrict_admin_bar'));

            if (is_admin()) {
                add_action('admin_menu', array(&$this, 'build_admin_menu'));
                add_action('cuar/core/on-plugin-update', array(&$this, 'plugin_version_upgrade'), 10, 2);
                add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'), 5);
                add_action('admin_init', array(&$this, 'restrict_admin_access'), 1);

                // Settings
                add_action('cuar/core/settings/print-settings?tab=cuar_core', array(&$this, 'print_core_settings'), 20, 2);
                add_filter('cuar/core/settings/validate-settings?tab=cuar_core', array(&$this, 'validate_core_options'), 20, 3);
            } else {
            }
        }

        /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

        /**
         * Set the default values for the options
         *
         * @param array $defaults
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
                    'type' => 'checkbox',
                    'after' => __('Restrict the access to the administration panel of WordPress.', 'cuar')
                        . '<p class="description">'
                        . sprintf(__('If you check this box, you will be able to control access to various WordPress administrative features (the area under /wp-admin/, the top bar, etc.). '
                                . 'Once enabled, you can select which roles will be allowed to access it in the <a href="%1$s">general permissions tab</a>.', 'cuar'),
                            admin_url('admin.php?page=cuar-settings&cuar_tab=cuar_capabilities'))
                        . '</p>')
            );
        }

        /**
         * Validate our options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array $input
         * @param array $validated
         * @return array
         */
        public function validate_core_options($validated, $cuar_settings, $input)
        {
            $cuar_settings->validate_boolean($input, $validated, self::$OPTION_RESTRICT_ADMIN_AREA_ACCESS);
            return $validated;
        }

        private static $OPTION_RESTRICT_ADMIN_AREA_ACCESS = 'cuar_restrict_admin_area_access';

        /*------- OTHER FUNCTIONS --------------------------------------------------------------*/

        /**
         * Declare the configurable capabilities for Customer Area
         *
         * @param array $capability_groups The capability groups
         * @return array The updated capability groups
         */
        public function get_configurable_capability_groups($capability_groups)
        {
            $capability_groups['cuar_general'] = array(
                'label' => __('General', 'cuar'),
                'groups' => array());


            if ($this->is_admin_area_access_restricted()) {
                $capability_groups['cuar_general']['groups'] = array(
                    'back-office' => array(
                        'group_name' => __('Back-office', 'cuar'),
                        'capabilities' => array(
                            'cuar_access_admin_panel' => __('Access the WordPress admin area', 'cuar'),
                            'view-customer-area-menu' => __('View the Customer Area menu', 'cuar'),
                        )
                    ),
                    'front-office' => array(
                        'group_name' => __('Front-office', 'cuar'),
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
            ) {

                $cp_addon = $this->plugin->get_addon('customer-pages');
                $customer_area_url = apply_filters('cuar/core/admin/admin-area-forbidden-redirect-url',
                    $cp_addon->get_page_url("customer-home"));

                if (isset($customer_area_url)) {
                    wp_redirect($customer_area_url);
                } else {
                    wp_redirect(home_url());
                }
            }
        }

        /**
         * If necessary, hide the admin bar to some users
         * @return bool true if the admin bar is visible
         */
        public function restrict_admin_bar()
        {
            if ($this->is_admin_area_access_restricted() && !current_user_can('cuar_view_top_bar')) {
                return false;
            }
            return true;
        }

        /**
         * Build the administration menu
         */
        public function build_admin_menu()
        {
            // Add the top-level admin menu
            $page_title = __('WP Customer Area', 'cuar');
            $menu_title = __('WP Customer Area', 'cuar');
            $menu_slug = 'customer-area';
            $capability = 'view-customer-area-menu';
            $function = array(&$this, 'print_dashboard');
            $icon = "";
            $position = '2.1.cuar';

            $this->pagehook = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon, $position);

            // Now add the submenu pages from add-ons
            $submenu_items = apply_filters('cuar/core/admin/main-menu-pages', array());

            foreach ($submenu_items as $item) {
                $submenu_page_title = $item['page_title'];
                $submenu_title = $item['title'];
                $submenu_slug = $item['slug'];
                $submenu_function = $item['function'];
                $submenu_capability = $item['capability'];

                add_submenu_page($menu_slug, $submenu_page_title, $submenu_title,
                    $submenu_capability, $submenu_slug, $submenu_function);
            }
        }

        /**
         * Build the admin bar menu
         * @param WP_Admin_Bar $wp_admin_bar
         */
        public function build_adminbar_menu($wp_admin_bar)
        {
            $wp_admin_bar->add_menu(array(
                'id' => 'customer-area',
                'title' => __('WP Customer Area', 'cuar'),
                'href' => admin_url('admin.php?page=customer-area')
            ));

            $wp_admin_bar->add_menu(array(
                'parent' => 'customer-area',
                'id' => 'customer-area-front-office',
                'title' => __('Your private area', 'cuar'),
                'href' => '#'
            ));

            $submenu_items = apply_filters('cuar/core/admin/adminbar-menu-items', array());
            foreach ($submenu_items as $item) {
                $wp_admin_bar->add_menu($item);
            }

            if (current_user_can('manage_options')) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'customer-area',
                    'id' => 'customer-area-settings',
                    'title' => __('Settings', 'cuar'),
                    'href' => admin_url('admin.php?page=cuar-settings')
                ));
            }

            if (current_user_can('manage_options')) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'customer-area',
                    'id' => 'customer-area-support',
                    'title' => __('Help & support', 'cuar'),
                    'href' => admin_url('admin.php?page=cuar-settings&cuar_tab=cuar_troubleshooting')
                ));

                $wp_admin_bar->add_menu(array(
                    'parent' => 'customer-area',
                    'id' => 'customer-area-addons',
                    'title' => __('Add-ons', 'cuar'),
                    'href' => admin_url('admin.php?page=cuar-settings&cuar_tab=cuar_addons')
                ));
            }
        }

        public function print_dashboard()
        {
            $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'blog';
            $tabs = array(
                'whats-new' => array(
                    'url' => admin_url('admin.php?page=customer-area&tab=whats-new'),
                    'label' => __("What's new", 'cuar')
                ),
                'changelog' => array(
                    'url' => admin_url('admin.php?page=customer-area&tab=changelog'),
                    'label' => __("Change log", 'cuar')
                ),
                'blog' => array(
                    'url' => admin_url('admin.php?page=customer-area&tab=blog'),
                    'label' => __("Live from the blog", 'cuar')
                ),
                'addons' => array(
                    'url' => admin_url('admin.php?page=customer-area&tab=addons'),
                    'label' => __("Add-ons &amp; themes", 'cuar')
                ),
            );

            $page_title = sprintf(__('Welcome to WP Customer Area %s', 'cuar'), $this->plugin->get_major_version());
            $hero_message_primary = sprintf(__('<strong>WP Customer Area</strong> %s is more powerful, stable and secure than ever before. We hope you will enjoy using it.', 'cuar'), $this->plugin->get_major_version());
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
            if (version_compare($from_version, '1.5.0', '<')) {
                $admin_role = get_role('administrator');
                if ($admin_role) {
                    $admin_role->add_cap('view-customer-area-menu');
                }
                $editor_role = get_role('editor');
                if ($editor_role) {
                    $editor_role->add_cap('view-customer-area-menu');
                }
            }
        }

        /** @var string */
        private $pagehook;
    }

// Make sure the addon is loaded
    new CUAR_AdminAreaAddOn();

endif; // if (!class_exists('CUAR_AdminAreaAddOn')) 

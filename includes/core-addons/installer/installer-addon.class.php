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

if (!class_exists('CUAR_InstallerAddOn')) :

    /**
     * Add-on to show a setup assistant when the plugin is installed for the first time
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_InstallerAddOn extends CUAR_AddOn
    {
        public function __construct()
        {
            parent::__construct('installer', '6.0.0');
        }

        public function get_addon_name()
        {
            return __('Installer', 'cuar');
        }

        public function run_addon($plugin)
        {
            if (is_admin()) {
                add_action('cuar/core/activation/run-deferred-action?action_id=check-plugin-version', array(&$this, 'check_plugin_version'));

                add_action('admin_menu', array(&$this, 'admin_menus'));
                add_action('admin_head', array(&$this, 'admin_head'));
                // add_action('admin_init', array(&$this, 'welcome'));
            }
        }

        /*------- SETTINGS -------------------------------------------------------------------------------------------*/

        /**
         * Set the default values for the options
         *
         * @param array $defaults
         * @return array
         */
        public function set_default_options($defaults)
        {
            $defaults = parent::set_default_options($defaults);
            $defaults[self::$OPTION_IS_INSTALLED] = false;
            return $defaults;
        }

        /**
         *
         */
        public function set_installed()
        {
            $this->plugin->update_option(self::$OPTION_IS_INSTALLED, true);
        }

        /**
         * @return bool
         */
        public function is_installed()
        {
            return $this->plugin->get_option(self::$OPTION_IS_INSTALLED);
        }

        /*------- DEFERRED HOOKS -------------------------------------------------------------------------------------*/

        public function check_plugin_version()
        {
            $plugin_data = get_plugin_data(WP_CONTENT_DIR . '/plugins/' . CUAR_PLUGIN_FILE, false, false);
            $current_version = $plugin_data['Version'];
            $active_version = $this->plugin->get_version();

            // Before 1.4.0 we did not have any versioning
            if (!isset($active_version)) $active_version = '1.4.0';

            // Update the current version
            $this->plugin->update_option(CUAR_Settings::$OPTION_CURRENT_VERSION, $current_version);

            // Redirect to the setup assistant if we have a first time install
            // Else launch update scripts if necessary
            if (!$this->is_installed()) {
                wp_redirect(admin_url('admin.php?page=cuar-setup'));
                exit;
            } else if (CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION !== FALSE) {
                do_action('cuar/core/on-plugin-update', CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION, $current_version);
            } else if ($active_version != $current_version) {
                do_action('cuar/core/on-plugin-update', $active_version, $current_version);

                wp_redirect(admin_url('admin.php?page=cuar-about'));
                exit;
            }
        }

        /**
         * Add admin menus/screens. They get removed later on in admin_head
         */
        public function admin_menus()
        {
            if (empty($_GET['page'])) return;

            switch ($_GET['page']) {
                case 'cuar-about' : {
                    $page_name = __('About WP Customer Area', 'cuar');
                    $page_title = __('Welcome to WP Customer Area', 'cuar');
                    add_dashboard_page($page_title, $page_name, 'manage_options', 'cuar-about', array($this, 'print_about_page'));
                    break;
                }

                case 'cuar-setup' : {
                    $page_name = __('Install WP Customer Area', 'cuar');
                    $page_title = __('Install WP Customer Area', 'cuar');
                    add_dashboard_page($page_title, $page_name, 'manage_options', 'cuar-setup', array($this, 'print_setup_page'));
                    break;
                }
            }
        }

        /**
         *
         */
        public function admin_head()
        {
            remove_submenu_page('index.php', 'cuar-about');
            remove_submenu_page('index.php', 'cuar-setup');
        }

        public function print_about_page()
        {
            $template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/installer',
                'installer-about-page.template.php',
                'templates');

            if (!empty($template)) {
                /** @noinspection PhpIncludeInspection */
                include($template);
            }
        }

        public function print_setup_page()
        {
            $template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/installer',
                'installer-setup-page.template.php',
                'templates');

            if (!empty($template)) {
                /** @noinspection PhpIncludeInspection */
                include($template);
            }
        }

        public static $OPTION_IS_INSTALLED = 'cuar/core/is_installed';
    }

// Make sure the addon is loaded
    new CUAR_InstallerAddOn();

endif; // class_exists('CUAR_InstallerAddOn')
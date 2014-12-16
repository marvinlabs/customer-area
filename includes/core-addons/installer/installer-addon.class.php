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
     * Add-on to show a setup assistant when the pulgin is installed for the first time
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
                add_action('admin_menu', array(&$this, 'admin_menus'));
                add_action('admin_head', array(&$this, 'admin_head'));
                // add_action('admin_init', array(&$this, 'welcome'));
            }
        }

        /*------- DEFERRED HOOKS -------------------------------------------------------------------------------------*/

        /**
         * Add admin menus/screens. They get removed later on in admin_head
         */
        public function admin_menus()
        {
            if (empty($_GET['page'])) return;

            $welcome_page_name = __('About WP Customer Area', 'cuar');
            $welcome_page_title = __('Welcome to WP Customer Area', 'cuar');

            switch ($_GET['page']) {
                case 'cuar-about' :
                    add_dashboard_page($welcome_page_title, $welcome_page_name, 'manage_options', 'cuar-about', array($this, 'print_about_page'));
                    break;
            }
        }

        /**
         *
         */
        public function admin_head()
        {
            remove_submenu_page('index.php', 'cuar-about');
        }

        public function print_about_page()
        {
            echo 'hello';
        }

    }

    // Make sure the addon is loaded
    new CUAR_InstallerAddOn();

endif; // if (!class_exists('CUAR_InstallerAddOn'))

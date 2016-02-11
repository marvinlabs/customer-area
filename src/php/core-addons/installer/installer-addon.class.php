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
        public static $OPTION_IS_INSTALLED = 'cuar/core/installer/is_installed';
        public static $OPTION_PENDING_REDIRECT = 'cuar/core/installer/pending_redirect';

        /**
         * Constructor
         */
        public function __construct()
        {
            parent::__construct('installer', '6.0.0');
        }

        /**
         * @return string The addon name
         */
        public function get_addon_name()
        {
            return __('Installer', 'cuar');
        }

        /**
         * Run the add-on (register hooks, etc.)
         * @param CUAR_Plugin $plugin
         */
        public function run_addon($plugin)
        {
            if (is_admin()) {
                add_action('cuar/core/activation/run-deferred-action?action_id=check-plugin-version', array(&$this, 'check_plugin_version'));
                add_action('cuar/core/activation/after-deferred-actions', array(&$this, 'maybe_redirect_after_activation'));

                add_action('admin_menu', array(&$this, 'admin_menus'));
                add_action('admin_head', array(&$this, 'admin_head'));

                add_action('wp_ajax_cuar_installer_create_pages_and_nav', array(&$this, 'create_pages_and_navigation'));
                add_action('wp_ajax_cuar_mark_permissions_as_configured', array(&$this, 'mark_permissions_as_configured'));
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
            $defaults[self::$OPTION_PENDING_REDIRECT] = '';
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

        /**
         * @param string $redirect
         */
        public function set_pending_redirect($redirect = '')
        {
            $this->plugin->update_option(self::$OPTION_PENDING_REDIRECT, $redirect);
        }

        /**
         * @return string
         */
        public function get_pending_redirect()
        {
            return $this->plugin->get_option(self::$OPTION_PENDING_REDIRECT);
        }

        /*------- DEFERRED HOOKS -------------------------------------------------------------------------------------*/

        public function redo_activation_checks()
        {
            CUAR_PluginActivationManager::on_activate();
        }

        /**
         * Hook called after all deferred activation actions have been run. If we need to redirect to the setup or to
         * the about page, then this is where it happens.
         */
        public function maybe_redirect_after_activation()
        {
            $pending_redirect = $this->get_pending_redirect();
            $this->set_pending_redirect();

            if (!empty($pending_redirect)) {
                wp_redirect(admin_url('admin.php?page=' . $pending_redirect));
                exit;
            }
        }

        /**
         * Deferred activation action to check current and previous plugin version, run the update scripts and decide
         * if we should show the setup assistant.
         */
        public function check_plugin_version()
        {
            $current_version = CUAR_PLUGIN_VERSION;
            $active_version = $this->plugin->get_version();

            // Before 1.4.0 we did not have any versioning
            if (!isset($active_version)) $active_version = '1.4.0';

            // Update the current version
            $this->plugin->update_option(CUAR_Settings::$OPTION_CURRENT_VERSION, $current_version);

            // Redirect to the setup assistant if we have a first time install
            // Else launch update scripts if necessary
            if (!$this->is_installed() && !(isset($_GET['page']) && $_GET['page'] == 'wpca-setup')) {
                $this->set_pending_redirect('wpca-setup');
            } else if (CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION !== FALSE) {
                do_action('cuar/core/on-plugin-update', CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION, $current_version);
            } else if ($active_version != $current_version) {
                do_action('cuar/core/on-plugin-update', $active_version, $current_version);
                $this->set_pending_redirect('wpca&tab=whats-new');
            }
        }

        /*------- AJAX CALLBACKS -------------------------------------------------------------------------------------*/

        /**
         * Called when the user clicks the button to create pages and navigation menu
         */
        public function create_pages_and_navigation()
        {
            // Use the Customer Pages add-on to do the job
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');
            $cp_addon->create_all_missing_pages();
            $cp_addon->recreate_default_navigation_menu();

            // Clear the notices added by the above functions
            $this->plugin->clear_admin_notices();

            // No way to screw up currently, always success
            $response = new StdClass();
            $response->success = true;
            $response->message = __('The required pages and the navigation menu have been created', 'cuar');

            wp_send_json($response);
        }

        /**
         * Called when the user clicks the configure permissions button.
         */
        public function mark_permissions_as_configured()
        {
            /** @var CUAR_CapabilitiesAddOn $cm_addon */
            $cm_addon = $this->plugin->get_addon('capabilities-manager');
            $cm_addon->ignore_unconfigured_capabilities_flag();

            // No way to screw up currently, always success
            $response = new StdClass();
            $response->success = true;

            wp_send_json($response);
        }

        /*------- SETUP ASSISTANT AND ABOUT PAGES --------------------------------------------------------------------*/

        /**
         * Add admin menus/screens. They get removed later on in admin_head (those pages are just supposed to be
         * accessed via redirection)
         */
        public function admin_menus()
        {
            if (empty($_GET['page'])) return;

            switch ($_GET['page']) {
                case 'wpca-setup' : {
                    $page_name = __('Install WP Customer Area', 'cuar');
                    $page_title = __('Install WP Customer Area', 'cuar');
                    add_dashboard_page($page_title, $page_name, 'manage_options', 'wpca-setup', array($this, 'print_setup_page'));
                    break;
                }
            }
        }

        /**
         * Remove the sub-menus we added above (those pages are just supposed to be accessed via redirection)
         */
        public function admin_head()
        {
            remove_submenu_page('index.php', 'wpca-setup');
        }

        /**
         * Prints the page that helps the user to install the plugin
         */
        public function print_setup_page()
        {
            $this->set_installed();

            $template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/installer',
                'installer-page.template.php',
                'templates');

            if (!empty($template)) {
                $page_title = sprintf(__('Welcome to WP Customer Area %s', 'cuar'), $this->plugin->get_major_version());
                $hero_message_primary = sprintf(__('<strong>WP Customer Area</strong> %s is more powerful, stable and secure than ever before. We hope you will enjoy using it.', 'cuar'), $this->plugin->get_major_version());
                $hero_message_secondary = __('There is a quick installation to run before being able to publish your first private files and pages. It should be over in less than a minute!', 'cuar');
                $content_template = $this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/installer',
                    'installer-part-setup-wizard.template.php',
                    'templates');

                /** @noinspection PhpIncludeInspection */
                include($template);
            }
        }

        /**
         * Output a list of blocks corresponding to a RSS feed.
         *
         * @param string $feed_link The URL of the RSS feed
         * @param int $max_items The max number of items to print (-1 for no limit)
         */
        // TODO Refactor this function into a more general Rss utility class
        // TODO Use templates for RSS
        private function print_feed_content($feed_link, $max_items = 4)
        {
            $out = '<div class="cuar-feed-content"><ul>';

            include_once(ABSPATH . WPINC . '/feed.php');
            $feed = fetch_feed($feed_link);

            if (is_wp_error($feed)) {
                $out .= '<li>';
                $out .= $feed->get_error_message();
                $out .= '</li>';
            } else {
                $count = 0;
                foreach ($feed->get_items() as $item) {
                    $out .= '<li>';
                    if ($enclosure = $item->get_enclosure()) {
                        $out .= '<a href="' . $item->get_permalink() . '" class="cuar-feed-image">';
                        $out .= '<img src="' . $enclosure->get_link() . '" />';
                        $out .= '</a>';
                    }

                    $out .= sprintf('<h3 class="cuar-feed-title"><a href="%1$s">%2$s</a></h3> <span class="cuar-feed-date">%3$s</span>',
                        $item->get_permalink(),
                        $item->get_title(),
                        $item->get_date(get_option('date_format')));

                    $out .= sprintf('<div class="cuar-feed-summary">%1$s</div>', $item->get_description());
                    $out .= '<div class="clear"></div></li>';

                    $count++;
                    if ($max_items > 0 && $count >= $max_items) break;
                }
            }
            $out .= '</ul></div>';

            echo $out;
        }
    }

    // Make sure the addon is loaded
    new CUAR_InstallerAddOn();

endif; // class_exists('CUAR_InstallerAddOn')
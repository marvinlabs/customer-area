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
                add_action('cuar/core/activation/after-deferred-actions', array(&$this, 'maybe_redirect_after_activation'));

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

        public function maybe_redirect_after_activation()
        {
            $pending_redirect = $this->get_pending_redirect();
            $this->set_pending_redirect();

            if (!empty($pending_redirect)) {
                wp_redirect(admin_url('admin.php?page=' . $pending_redirect));
                exit;
            }
        }

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
            if (!$this->is_installed() && !(isset($_GET['page']) && $_GET['page'] == 'cuar-setup')) {
                $this->set_pending_redirect('cuar-setup');
            } else if (CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION !== FALSE) {
                do_action('cuar/core/on-plugin-update', CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION, $current_version);
            } else if ($active_version != $current_version) {
                do_action('cuar/core/on-plugin-update', $active_version, $current_version);
                $this->set_pending_redirect('cuar-about');
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
         * Remove the sub-menus we added above (those pages are just supposed to be accessed via redirection)
         */
        public function admin_head()
        {
            remove_submenu_page('index.php', 'cuar-about');
            remove_submenu_page('index.php', 'cuar-setup');
        }

        /**
         * Prints the page that gives information about the plugin when an update occurred
         */
        public function print_about_page()
        {
            $template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/installer',
                'installer-page.template.php',
                'templates');

            if (!empty($template)) {
                $page_title = sprintf(__('Welcome to WP Customer Area %s', 'cuar'), $this->plugin->get_major_version());
                $hero_message_primary = sprintf(__('<strong>WP Customer Area</strong> %s is more powerful, stable and secure than ever before. We hope you will enjoy using it.', 'cuar'), $this->plugin->get_major_version());
                $hero_message_secondary = __('Congratulations, you have just updated the plugin! ', 'cuar');
                $content_template = $this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/installer',
                    'installer-part-about.template.php',
                    'templates');

                /** @noinspection PhpIncludeInspection */
                include($template);
            }
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

        public function print_whats_new()
        {
            $whats_new = array(
                array(
                    'title' => __('Improved setup &amp; updates', 'cuar'),
                    'text' => __('We have implemented a new setup assistant that will make it even easier to '
                        . 'install the plugin. Updates will be smoother too.', 'cuar')
                ),
                array(
                    'title' => __('Better permissions', 'cuar'),
                    'text' => __('Some new permissions have been added to give you more control about what your '
                        . 'users can do. On top of that, we have also improved the permissions screen to make it '
                        . 'faster to set permissions.', 'cuar')
                ),
                array(
                    'title' => __('Stability improvements', 'cuar'),
                    'text' => __('As with any new release, we constantly provide bug fixes. This update is no exception '
                        . 'with no less than 20 issues corrected.', 'cuar')
                ),
            );

            /** @noinspection PhpIncludeInspection */
            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/installer',
                'installer-part-whats-new.template.php',
                'templates'));
        }

        public function print_related_actions()
        {
            /** @noinspection PhpIncludeInspection */
            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/installer',
                'installer-part-related-actions.template.php',
                'templates'));
        }

        public function print_latest_blog_posts()
        {
            echo $this->get_feed_content(__('http://wp-customerarea.com/feed/rss', 'cuar'));
        }

        private function get_feed_content($feed_link, $max_items = 4)
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
                    $out .= sprintf('<h3><a class="cuar-feed-title" href="%1$s">%2$s</a></h3> <span class="cuar-feed-date">%3$s</span>',
                        $item->get_permalink(),
                        $item->get_title(),
                        $item->get_date(get_option('date_format')));

                    $out .= sprintf('<div class="cuar-feed-summary">%1$s</div>', $item->get_description());
                    $out .= '</li>';

                    $count++;
                    if ($count >= $max_items) break;
                }
            }
            $out .= '</ul></div>';

            return $out;
        }

        public static $OPTION_IS_INSTALLED = 'cuar/core/installer/is_installed';
        public static $OPTION_PENDING_REDIRECT = 'cuar/core/installer/pending_redirect';
    }

// Make sure the addon is loaded
    new CUAR_InstallerAddOn();

endif; // class_exists('CUAR_InstallerAddOn')
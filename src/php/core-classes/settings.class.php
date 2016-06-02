<?php
/*
 * Copyright 2013 MarvinLabs (contact@marvinlabs.com) This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
require_once(CUAR_INCLUDES_DIR . '/helpers/wordpress-helper.class.php');

if ( !class_exists('CUAR_Settings')) :

    /**
     * Creates the UI to change the plugin settings in the admin area.
     * Also used to access the plugin settings
     * stored in the DB (@see CUAR_Plugin::get_option)
     */
    class CUAR_Settings
    {
        public function __construct($plugin)
        {
            $this->plugin = $plugin;
            $this->setup();
            $this->reload_options();
        }

        /**
         * Get the value of a particular plugin option
         *
         * @param string $option_id
         *            the ID of the option to get
         *
         * @return mixed the value
         */
        public function get_option($option_id)
        {
            return isset($this->options [$option_id]) ? $this->options [$option_id] : null;
        }

        /**
         * Setup the WordPress hooks we need
         */
        public function setup()
        {
            add_action('cuar/core/admin/submenu-items?group=tools', array(&$this, 'add_menu_items'), 10);

            if (is_admin())
            {
                add_action('admin_init', array(&$this, 'page_init'));
                add_action('cuar/core/admin/print-admin-page?page=settings', array(&$this, 'print_settings_page'), 99);

                // Links under the plugin name
                $plugin_file = 'customer-area/customer-area.php';
                add_filter("plugin_action_links_{$plugin_file}", array(&$this, 'print_plugin_action_links'), 10, 2);

                // We have some core settings to take care of too
                add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_core_settings_tab'), 200, 1);

                add_action('cuar/core/settings/print-settings?tab=cuar_core', array(&$this, 'print_core_settings'), 10,
                    2);
                add_filter('cuar/core/settings/validate-settings?tab=cuar_core',
                    array(&$this, 'validate_core_settings'), 10, 3);

                add_action('cuar/core/settings/print-settings?tab=cuar_frontend',
                    array(&$this, 'print_frontend_settings'), 10, 2);
                add_filter('cuar/core/settings/validate-settings?tab=cuar_frontend',
                    array(&$this, 'validate_frontend_settings'), 10, 3);

                add_action('wp_ajax_cuar_validate_license', array('CUAR_Settings', 'ajax_validate_license'));
            }
        }

        public function flush_rewrite_rules()
        {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }

        /**
         * Add the menu item
         */
        public function add_menu_items($submenus)
        {
            if (is_admin())
            {
                add_options_page(__('WP Customer Area Settings', 'cuar'),
                    __('WP Customer Area', 'cuar'),
                    'manage_options',
                    self::$OPTIONS_PAGE_SLUG,
                    array(&$this, 'print_settings_page')
                );
            }

            $item = array(
                'page_title'    => __('WP Customer Area Settings', 'cuar'),
                'title'         => __('Settings', 'cuar'),
                'slug'          => self::$OPTIONS_PAGE_SLUG,
                'href'          => admin_url('options-general.php?page=' . self::$OPTIONS_PAGE_SLUG),
                'capability'    => 'manage_options',
                'adminbar-only' => true,
                'children'      => array()
            );

            $tabs = apply_filters('cuar/core/settings/settings-tabs', array());
            $tabs_to_skip = array('cuar_addons', 'cuar_troubleshooting');
            foreach ($tabs as $tab_id => $tab_label)
            {
                if (in_array($tab_id, $tabs_to_skip))
                {
                    continue;
                }

                $item['children'][] = array(
                    'slug'  => 'customer-area-admin-settings-' . $tab_id,
                    'title' => $tab_label,
                    'href'  => admin_url('options-general.php?page=' . self::$OPTIONS_PAGE_SLUG . '&tab=' . $tab_id)
                );
            }

            $submenus[] = $item;

            return $submenus;
        }

        public function print_plugin_action_links($links, $file)
        {
            $link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . self::$OPTIONS_PAGE_SLUG . '">'
                . __('Settings', 'cuar') . '</a>';
            array_unshift($links, $link);

            return $links;
        }

        /**
         * Output the settings page
         */
        public function print_settings_page()
        {
            if (isset($_GET['run-setup-wizard']))
            {
                $success = false;

                if (isset($_POST['submit']))
                {
                    $errors = array();
                    if ( !isset($_POST["cuar_page_title"]) || empty($_POST["cuar_page_title"]))
                    {
                        $errors[] = __('The page title cannot be empty', 'cuar');
                    }

                    if (empty($errors))
                    {
                        $post_data = array(
                            'post_content'   => '[customer-area /]',
                            'post_title'     => $_POST["cuar_page_title"],
                            'post_status'    => 'publish',
                            'post_type'      => 'page',
                            'comment_status' => 'closed',
                            'ping_status'    => 'closed'
                        );
                        $page_id = wp_insert_post($post_data);
                        if (is_wp_error($page_id))
                        {
                            $errors[] = $page_id->get_error_message();
                        }
                        else
                        {
                            $this->plugin->get_addon('customer-pages')->set_customer_page_id($page_id);
                        }

                        if (empty($errors))
                        {
                            $success = true;
                        }
                    }
                }

                if ($_GET['run-setup-wizard'] == 1664)
                {
                    $success = true;
                }

                if ($success)
                {
                    include(CUAR_INCLUDES_DIR . '/setup-wizard-done.view.php');
                }
                else
                {
                    include(CUAR_INCLUDES_DIR . '/setup-wizard.view.php');
                }
            }
            else
            {
                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-classes',
                    'settings.template.php',
                    'templates'));
            }
        }

        /**
         * Register the settings
         */
        public function page_init()
        {
            $this->setup_tabs();

            // Register the main settings and for the current tab too
            register_setting(self::$OPTIONS_GROUP, self::$OPTIONS_GROUP, array(
                &$this,
                'validate_options'
            ));
            register_setting(self::$OPTIONS_GROUP . '_' . $this->current_tab, self::$OPTIONS_GROUP, array(
                &$this,
                'validate_options'
            ));

            // Let the current tab add its own settings to the page
            do_action("cuar/core/settings/print-settings?tab=" . $this->current_tab, $this,
                self::$OPTIONS_GROUP . '_' . $this->current_tab);
        }

        /**
         * Create the tabs to show
         */
        public function setup_tabs()
        {
            $this->tabs = apply_filters('cuar/core/settings/settings-tabs', array());

            // Get current tab from GET or POST params or default to first in list
            $this->current_tab = isset($_GET ['tab']) ? $_GET ['tab'] : '';
            if ( !isset($this->tabs [$this->current_tab]))
            {
                $this->current_tab = isset($_POST ['tab']) ? $_POST ['tab'] : '';
            }
            if ( !isset($this->tabs [$this->current_tab]))
            {
                reset($this->tabs);
                $this->current_tab = key($this->tabs);
            }
        }

        /**
         * Save the plugin settings
         *
         * @param array $input
         *            The new option values
         *
         * @return
         *
         */
        public function validate_options($input)
        {
            $validated = array();

            // Allow addons to validate their settings here
            $validated = apply_filters('cuar/core/settings/validate-settings?tab=' . $this->current_tab, $validated,
                $this, $input);

            $this->options = array_merge($this->options, $validated);

            // Also flush rewrite rules
            global $wp_rewrite;
            $wp_rewrite->flush_rules();

            return $this->options;
        }

        /* ------------ CORE SETTINGS ----------------------------------------------------------------------------------- */

        /**
         * Add a tab
         *
         * @param array $tabs
         *
         * @return array
         */
        public function add_core_settings_tab($tabs)
        {
            $tabs ['cuar_core'] = __('General', 'cuar');
            $tabs ['cuar_frontend'] = __('Frontend', 'cuar');

            return $tabs;
        }

        /**
         * Add our fields to the settings page
         *
         * @param CUAR_Settings $cuar_settings
         *            The settings class
         */
        public function print_core_settings($cuar_settings, $options_group)
        {
            // General settings
            add_settings_section('cuar_general_settings',
                __('General Settings', 'cuar'),
                array(&$cuar_settings, 'print_empty_section_info'),
                self::$OPTIONS_PAGE_SLUG);

            add_settings_field(self::$OPTION_ADMIN_SKIN,
                __('Admin theme', 'cuar'),
                array(&$cuar_settings, 'print_theme_select_field'),
                self::$OPTIONS_PAGE_SLUG,
                'cuar_general_settings',
                array(
                    'option_id'  => self::$OPTION_ADMIN_SKIN,
                    'theme_type' => 'admin'
                )
            );
        }

        /**
         * Validate core options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         * @param array         $validated
         *
         * @return array
         */
        public function validate_core_settings($validated, $cuar_settings, $input)
        {
            $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_ADMIN_SKIN);

            return $validated;
        }

        /**
         * Add our fields to the settings page
         *
         * @param CUAR_Settings $cuar_settings
         *            The settings class
         */
        public function print_frontend_settings($cuar_settings, $options_group)
        {
            // General settings
            add_settings_section('cuar_general_settings',
                __('General Settings', 'cuar'),
                array(&$cuar_settings, 'print_frontend_section_info'),
                self::$OPTIONS_PAGE_SLUG);

            if ( !current_theme_supports('customer-area.stylesheet'))
            {
                add_settings_field(self::$OPTION_INCLUDE_CSS,
                    __('Use skin', 'cuar'),
                    array(&$cuar_settings, 'print_input_field'),
                    self::$OPTIONS_PAGE_SLUG,
                    'cuar_general_settings',
                    array(
                        'option_id' => self::$OPTION_INCLUDE_CSS,
                        'type'      => 'checkbox',
                        'after'     => __('Includes the WP Customer Area skin to provide a stylesheet for the plugin.', 'cuar') . '<p class="description">'
                            . __('You can uncheck this if your theme includes support for WP Customer Area.', 'cuar') . '</p>'
                    )
                );

                add_settings_field(self::$OPTION_FRONTEND_SKIN,
                    __('Skin to use', 'cuar'),
                    array(&$cuar_settings, 'print_theme_select_field'),
                    self::$OPTIONS_PAGE_SLUG,
                    'cuar_general_settings',
                    array(
                        'option_id'  => self::$OPTION_FRONTEND_SKIN,
                        'theme_type' => 'frontend',
                        'after'      => '<p class="description">'
                            . sprintf(__('You can make your own skin, please refer to <a href="%1$s">our documentation about skins</a>.', 'cuar'),
                                'http://wp-customerarea.com' . __('/documentation/the-skin-system/', 'cuar'))
                            . '</p>'
                    )
                );
            }

            add_settings_field(self::$OPTION_DEBUG_TEMPLATES,
                __('Debug templates', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                self::$OPTIONS_PAGE_SLUG,
                'cuar_general_settings',
                array(
                    'option_id' => self::$OPTION_DEBUG_TEMPLATES,
                    'type'      => 'checkbox',
                    'after'     => __('Print debug information about the templates used by Customer Area.', 'cuar')
                        . '<p class="description">'
                        . __('If checked, the plugin will print HTML comments in the page source code to show which template files '
                            . 'are used. This is very helpful if you are a developer and want to customize the plugin layout.',
                            'cuar') . '</p>'
                )
            );
        }

        /**
         * Validate frontend options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         * @param array         $validated
         */
        public function validate_frontend_settings($validated, $cuar_settings, $input)
        {
            $cuar_settings->validate_boolean($input, $validated, self::$OPTION_DEBUG_TEMPLATES);

            if ( !current_theme_supports('customer-area-css'))
            {
                $cuar_settings->validate_boolean($input, $validated, self::$OPTION_INCLUDE_CSS);
                $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_FRONTEND_SKIN);
            }

            return $validated;
        }

        public function print_empty_section_info()
        {
        }

        public function print_frontend_section_info()
        {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#cuar_include_css').change(function () {
                        $('#cuar_frontend_theme_url').parents('tr').slideToggle();
                    });
                    if ($('#cuar_include_css').is(':checked')) {
                        $('#cuar_frontend_theme_url').parents('tr').show();
                    } else {
                        $('#cuar_frontend_theme_url').parents('tr').hide();
                    }
                });
            </script>
            <?php
        }

        /**
         * Set the default values for the core options
         *
         * @param array $defaults
         *
         * @return array
         */
        public static function set_default_core_options($defaults)
        {
            $defaults [self::$OPTION_INCLUDE_CSS] = true;
            $defaults [self::$OPTION_DEBUG_TEMPLATES] = false;
            $defaults [self::$OPTION_ADMIN_SKIN] = CUAR_ADMIN_SKIN;
            $defaults [self::$OPTION_FRONTEND_SKIN] = CUAR_FRONTEND_SKIN;

            return $defaults;
        }

        /* ------------ VALIDATION HELPERS ------------------------------------------------------------------------------ */

        /**
         * Validate an address
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_address($input, &$validated, $option_id)
        {
            $validated[$option_id] = CUAR_AddressHelper::sanitize_address($input[$option_id]);
        }

        /**
         * Validate a value in any case
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_always($input, &$validated, $option_id)
        {
            $validated[$option_id] = trim($input[$option_id]);
        }

        /**
         * Validate a page
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_post_id($input, &$validated, $option_id)
        {
            if ($input[$option_id] == -1 || get_post($input[$option_id]))
            {
                $validated[$option_id] = $input[$option_id];
            }
            else
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . $input[$option_id] . __(' is not a valid post', 'cuar'), 'error');
            }
        }

        /**
         * Validate a boolean value within an array
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_boolean($input, &$validated, $option_id)
        {
            $validated[$option_id] = isset($input[$option_id]) ? true : false;
        }

        /**
         * Validate a role
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_role($input, &$validated, $option_id)
        {
            $role = $input[$option_id];
            if (isset($role) && ($role == "cuar_any" || null != get_role($input[$option_id])))
            {
                $validated[$option_id] = $input[$option_id];
            }
            else
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . $input[$option_id] . __(' is not a valid role', 'cuar'), 'error');
            }
        }

        /**
         * Validate a value in any case
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_license_key($input, &$validated, $option_id)
        {
            $validated[$option_id] = trim($input[$option_id]);
        }

        /**
         * Validate a value which should simply be not empty
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_not_empty($input, &$validated, $option_id)
        {
            if (isset($input[$option_id]) && !empty ($input[$option_id]))
            {
                $validated[$option_id] = $input[$option_id];
            }
            else
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . $input[$option_id] . __(' cannot be empty', 'cuar'), 'error');

                $validated[$option_id] = $this->default_options [$option_id];
            }
        }

        /**
         * Validate an email address
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_email($input, &$validated, $option_id)
        {
            if (isset($input[$option_id]) && is_email($input[$option_id]))
            {
                $validated[$option_id] = $input[$option_id];
            }
            else
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . $input[$option_id] . __(' is not a valid email', 'cuar'), 'error');

                $validated[$option_id] = $this->default_options [$option_id];
            }
        }

        /**
         * Validate an enum value within an array
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         * @param array  $enum_values
         *            Array of possible values
         */
        public function validate_enum($input, &$validated, $option_id, $enum_values)
        {
            if ( !in_array($input[$option_id], $enum_values))
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . $input[$option_id] . __(' is not a valid value', 'cuar'), 'error');

                $validated[$option_id] = $this->default_options [$option_id];

                return;
            }

            $validated[$option_id] = $input[$option_id];
        }

        /**
         * Validate an integer value within an array
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         * @param int    $min
         *            Min value for the int (set to null to ignore check)
         * @param int    $max
         *            Max value for the int (set to null to ignore check)
         */
        public function validate_int($input, &$validated, $option_id, $min = null, $max = null)
        {
            // Must be an int
            if ( !is_int(intval($input[$option_id])))
            {
                add_settings_error($option_id, 'settings-errors', $option_id . ': ' . __('must be an integer', 'cuar'),
                    'error');

                $validated[$option_id] = $this->default_options [$option_id];

                return;
            }

            // Must be > min
            if ($min !== null && $input[$option_id] < $min)
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . sprintf(__('must be greater than %s', 'cuar'), $min), 'error');

                $validated[$option_id] = $this->default_options [$option_id];

                return;
            }

            // Must be < max
            if ($max !== null && $input[$option_id] > $max)
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . sprintf(__('must be lower than %s', 'cuar'), $max), 'error');

                $validated[$option_id] = $this->default_options [$option_id];

                return;
            }

            // All good
            $validated[$option_id] = intval($input[$option_id]);
        }

        /**
         * Validate a value which should be an owner
         *
         * @param array  $input
         *            Input array
         * @param array  $validated
         *            Output array
         * @param string $option_id
         *            Key of the value to check in the input array
         */
        public function validate_owners($input, &$validated, $option_id, $owner_type_option_id)
        {
            if (is_array($input[$option_id]) && !empty($input[$option_id]))
            {
                $owners = array();
                foreach ($input[$option_id] as $type => $ids)
                {
                    if (is_array($ids))
                    {
                        $owners[$type] = $ids;
                    }
                    else
                    {
                        $owners[$type] = array($ids);
                    }
                }
                $validated[$option_id] = $owners;
                $validated[$owner_type_option_id] = '';
            }
            else
            {
                add_settings_error($option_id, 'settings-errors', $option_id . ': ' . __('Invalid owner', 'cuar'), 'error');
                $validated[$option_id] = $this->default_options [$option_id];
            }
        }

        /**
         * Validate a term id (for now, we just check it is not empty and strictly positive)
         *
         * @param array  $input     Input array
         * @param array  $validated Output array
         * @param string $option_id Key of the value to check in the input array
         */
        public function validate_term($input, &$validated, $option_id, $taxonomy, $allow_multiple = false)
        {
            $term_ids = isset($input[$option_id]) ? $input[$option_id] : '';

            if ( !$allow_multiple && is_array($term_ids))
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . __('you cannot select multiple terms.', 'cuar'), 'error');
                $validated[$option_id] = -1;

                return;
            }

            if ($allow_multiple)
            {
                if ( !is_array($term_ids))
                {
                    $term_ids = empty($term_ids) ? array() : array($term_ids);
                }

                $validated[$option_id] = $term_ids;
            }
            else
            {
                $validated[$option_id] = empty($term_ids) ? -1 : $term_ids;
            }
        }

        /**
         * Validate a post type
         *
         * @param array  $input     Input array
         * @param array  $validated Output array
         * @param string $option_id Key of the value to check in the input array
         */
        public function validate_post_type($input, &$validated, $option_id, $allow_multiple = false)
        {
            $term_ids = isset($input[$option_id]) ? $input[$option_id] : '';

            if ( !$allow_multiple && is_array($term_ids))
            {
                add_settings_error($option_id, 'settings-errors',
                    $option_id . ': ' . __('you cannot select multiple terms.', 'cuar'), 'error');
                $validated[$option_id] = -1;

                return;
            }

            if ($allow_multiple)
            {
                if ( !is_array($term_ids))
                {
                    $term_ids = empty($term_ids) ? array() : array($term_ids);
                }

                $validated[$option_id] = $term_ids;
            }
            else
            {
                $validated[$option_id] = empty($term_ids) ? -1 : $term_ids;
            }
        }

        /**
         * Handles remote requests to validate a license
         */
        public static function ajax_validate_license()
        {
            $cuar_plugin = CUAR_Plugin::get_instance();

            $addon_id = $_POST["addon_id"];
            $license = $_POST["license"];

            /** @var CUAR_AddOn $addon */
            $addon = $cuar_plugin->get_addon($addon_id);

            $license_key_option_id = $addon->get_license_key_option_name();
            $cuar_plugin->update_option($license_key_option_id, $license);

            $licensing = $cuar_plugin->get_licensing();
            $result = $licensing->validate_license($license, $addon);

            $today = new DateTime();
            $license_check_option_id = $addon->get_license_check_option_name();
            $cuar_plugin->update_option($license_check_option_id, $today->format('Y-m-d'));

            $license_status_option_id = $addon->get_license_status_option_name();
            $cuar_plugin->update_option($license_status_option_id, $result);

            echo json_encode($result);
            exit;
        }

        /* ------------ FIELDS OUTPUT ----------------------------------------------------------------------------------- */

        /**
         * Output a text field for a license key
         *
         * @param string $option_id
         */
        public function print_license_key_field($args)
        {
            extract($args);

            $license_key = $this->options[$option_id];
            $last_status = $this->plugin->get_option($status_option_id, null);

            if ($last_status != null && !empty($license_key))
            {
                $status_class = $last_status->success ? 'cuar-ajax-success' : 'cuar-ajax-failure';
                $status_message = $last_status->message;
            }
            else if (empty($license_key))
            {
                $status_class = '';
                $status_message = '';
                $this->plugin->update_option($status_option_id, null);
                $this->plugin->update_option($check_option_id, null);
            }
            else
            {
                $status_class = '';
                $status_message = '';
            }

            if (isset($before))
            {
                echo $before;
            }

            echo '<div class="license-control">';
            echo sprintf('<input type="text" id="%s" name="%s[%s]" value="%s" class="regular-text" data-addon="%s" />',
                esc_attr($option_id), self::$OPTIONS_GROUP, esc_attr($option_id), esc_attr($this->options[$option_id]),
                esc_attr($addon_id));
            echo sprintf('<span class="cuar-ajax-container"><span id="%s_check_result" class="%s">%s</span></span>',
                esc_attr($option_id), $status_class, $status_message);
            echo '</div>';

            if (isset($after))
            {
                echo $after;
            }

//			$last_check = $this->plugin->get_option( $check_option_id, null );
//			if ( $last_check!=null ) {
//				$next_check = new DateTime( $last_check );
//				$next_check->modify('+10 day');
//
//				$should_check_license = ( new DateTime('now') > $next_check );
//			} else {
//				$should_check_license = true;
//			}
            wp_enqueue_script('cuar.admin');
        }

        /**
         * Output a text field for a setting
         *
         * @param string $option_id
         * @param string $type
         * @param string $caption
         */
        public function print_input_field($args)
        {
            extract($args);

            if ($type == 'checkbox')
            {
                if (isset($before))
                {
                    echo $before;
                }

                echo sprintf('<input type="%s" id="%s" name="%s[%s]" value="open" %s />&nbsp;', esc_attr($type),
                    esc_attr($option_id), self::$OPTIONS_GROUP, esc_attr($option_id),
                    ($this->options [$option_id] != 0) ? 'checked="checked" ' : '');

                if (isset($after))
                {
                    echo $after;
                }
            }
            else if ($type == 'textarea')
            {
                if (isset($before))
                {
                    echo $before;
                }

                echo sprintf('<textarea id="%s" name="%s[%s]" class="large-text">%s</textarea>', esc_attr($option_id),
                    self::$OPTIONS_GROUP, esc_attr($option_id), $content);

                if (isset($after))
                {
                    echo $after;
                }
            }
            else if ($type == 'editor')
            {
                if ( !isset($editor_settings))
                {
                    /** @noinspection PhpDeprecationInspection Fine since we are on the admin side*/
                    $editor_settings = cuar_wp_editor_settings();
                }
                $editor_settings ['textarea_name'] = self::$OPTIONS_GROUP . "[" . $option_id . "]";

                wp_editor($this->options [$option_id], $option_id, $editor_settings);
            }
            else if ($type == 'upload')
            {
                wp_enqueue_script('cuar.admin');
                wp_enqueue_media();

                $extra_class = 'cuar-upload-input regular-text';

                if (isset($before))
                {
                    echo $before;
                }

                echo '<div id="cuar-upload-control-' . $option_id . '">';
                echo sprintf('<input type="%s" id="%s" name="%s[%s]" value="%s" class="%s" />', esc_attr($type),
                    esc_attr($option_id), self::$OPTIONS_GROUP, esc_attr($option_id),
                    esc_attr(stripslashes($this->options [$option_id])), esc_attr($extra_class));

                echo '<span>&nbsp;<input type="button" class="cuar-upload-button button-secondary" value="' . __('Upload File', 'cuar') . '"/></span>';

                echo '<script type="text/javascript">';
                echo '    jQuery(document).ready(function($) { $("#cuar-upload-control-' . $option_id . '").mediaInputControl(); });';
                echo '</script>';
                echo '</div>';

                if (isset($after))
                {
                    echo $after;
                }
            }
            else
            {
                $extra_class = isset($is_large) && $is_large == true ? 'large-text' : 'regular-text';

                if (isset($before))
                {
                    echo $before;
                }

                echo sprintf('<input type="%s" id="%s" name="%s[%s]" value="%s" class="%s" />', esc_attr($type),
                    esc_attr($option_id), self::$OPTIONS_GROUP, esc_attr($option_id),
                    esc_attr($this->options [$option_id]), esc_attr($extra_class));

                if (isset($after))
                {
                    echo $after;
                }
            }
        }


        /**
         * Output a submit button
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_submit_button($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            echo sprintf('<p><input type="submit" name="%s" id="%s[%s]" value="%s" class="button %s" /></p>',
                esc_attr($option_id),
                self::$OPTIONS_GROUP, esc_attr($option_id),
                $label,
                esc_attr($option_id)
            );

            wp_nonce_field($nonce_action, $nonce_name);

            if (isset($after))
            {
                echo $after;
            }

            if (isset($confirm_message) && !empty($confirm_message))
            {
                ?>
                <script type="text/javascript">
                    <!--
                    jQuery(document).ready(function ($) {
                        $('input.<?php echo esc_attr($option_id); ?>').click('click', function () {
                            var answer = confirm("<?php echo str_replace('"', '\\"', $confirm_message); ?>");
                            return answer;
                        });
                    });
                    //-->
                </script>
                <?php
            }
        }

        /**
         * Output a select field for a setting
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_select_field($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            echo sprintf('<select id="%s" name="%s[%s]">', esc_attr($option_id), self::$OPTIONS_GROUP,
                esc_attr($option_id));

            $option_value = isset($this->options[$option_id]) ? $this->options[$option_id] : null;

            foreach ($options as $value => $label)
            {
                $selected = ($option_value == $value) ? 'selected="selected"' : '';

                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, $label);
            }

            echo '</select>';

            if (isset($after))
            {
                echo $after;
            }
        }

        /**
         * Output a select field for a setting
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_post_select_field($args)
        {
            extract($args);

            $query_args = array(
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC'
            );
            $pages_query = new WP_Query($query_args);

            if (isset($before))
            {
                echo $before;
            }

            echo sprintf('<select id="%s" name="%s[%s]" class="cuar-post-select">', esc_attr($option_id),
                self::$OPTIONS_GROUP, esc_attr($option_id));

            $value = -1;
            $label = __('None', 'cuar');
            $selected = (isset($this->options[$option_id]) && $this->options[$option_id] == $value)
                ? 'selected="selected"' : '';
            echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, $label);

            while ($pages_query->have_posts())
            {
                $pages_query->the_post();
                $value = get_the_ID();
                $label = get_the_title();

                $selected = (isset($this->options[$option_id]) && $this->options[$option_id] == $value)
                    ? 'selected="selected"' : '';

                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, $label);
            }

            echo '</select>';

            if (isset($this->options[$option_id]) && $this->options[$option_id] > 0)
            {
                if ($show_create_button)
                {
                    printf('<input type="submit" value="%1$s" id="%2$s" name="%2$s" class="cuar-submit-create-post"/>',
                        esc_attr__('Delete existing &amp; create new &raquo;', 'cuar'),
                        esc_attr($this->get_submit_create_post_button_name($option_id))
                    );
                }

                edit_post_link(__('Edit it &raquo;', 'cuar'), '<span class="cuar-edit-page-link">', '</span>',
                    $this->options[$option_id]);
            }
            else
            {
                if ($show_create_button)
                {
                    printf('<input type="submit" value="%1$s" id="%2$s" name="%2$s" class="cuar-submit-create-post"/>',
                        esc_attr__('Create it &raquo;', 'cuar'),
                        esc_attr($this->get_submit_create_post_button_name($option_id))
                    );
                }
            }

            wp_reset_postdata();

            if (isset($after))
            {
                echo $after;
            }
        }

        public function get_submit_create_post_button_name($option_id)
        {
            return 'submit_' . $option_id . '_create';
        }

        /**
         * Output a set of fields for an address
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_address_fields($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            $address = CUAR_AddressHelper::sanitize_address($this->options[$option_id]);

            /** @var CUAR_AddressesAddOn $am_addon */
            $am_addon = $this->plugin->get_addon("address-manager");
            $am_addon->print_address_editor($address, $option_id, '', array(), '', 'settings');

            if (isset($after))
            {
                echo $after;
            }
        }

        /**
         * Output a select field for a setting
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_owner_select_field($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            // Handle legacy owner option
            if ( !empty($this->options[$owner_type_option_id]))
            {
                $owner_type = $this->options[$owner_type_option_id];
                $owner_ids = $this->options [$option_id];

                $owners = array($owner_type => $owner_ids);
            }
            else
            {
                $owners = $this->options [$option_id];
            }

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon("post-owner");
            $po_addon->print_owner_fields($owners, $option_id, self::$OPTIONS_GROUP);

            if (isset($after))
            {
                echo $after;
            }
        }

        /**
         * Output a select field for a setting
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_role_select_field($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            $this->plugin->enable_library('jquery.select2');

            echo sprintf('<select id="%s" name="%s[%s]">', esc_attr($option_id), self::$OPTIONS_GROUP,
                esc_attr($option_id));

            global $wp_roles;
            if ( !isset($wp_roles))
            {
                $wp_roles = new WP_Roles();
            }
            $all_roles = $wp_roles->role_objects;

            if ($show_any_option)
            {
                $value = 'cuar_any';
                $label = __('Any Role', 'cuar');
                $selected = ($this->options [$option_id] == $value) ? 'selected="selected"' : '';

                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, $label);
            }

            foreach ($all_roles as $role)
            {
                $value = $role->name;
                $label = CUAR_WordPressHelper::getRoleDisplayName($role->name);
                $selected = ($this->options [$option_id] == $value) ? 'selected="selected"' : '';

                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, $label);
            }

            echo '</select>';

            echo '<script type="text/javascript">
                <!--
                jQuery("document").ready(function ($) {
                    $("#' . esc_attr($option_id) . '").select2({
                        ' . (!is_admin() ? 'dropdownParent: $("#' . esc_attr($option_id) . '.parent()"),' : '') . '
                        width: "100%"
                    });
                });
                //-->
            </script>';

            if (isset($after))
            {
                echo $after;
            }
        }

        /**
         * Output a select field for a term
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_term_select_field($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            $this->plugin->enable_library('jquery.select2');
            $terms = get_terms($taxonomy, array(
                'hide_empty' => 0,
                'orderby'    => 'name'
            ));

            $current_option_value = $this->options[$option_id];

            $field_name = esc_attr(self::$OPTIONS_GROUP) . '[' . esc_attr($option_id) . ']';
            if (isset($multiple) && $multiple)
            {
                $field_name .= '[]';
            }

            $field_id = esc_attr($option_id);

            $multiple = isset($multiple) && $multiple ? 'multiple="multiple" ' : '';
            ?>

            <select id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" <?php echo $multiple; ?>>

                <?php foreach ($terms as $term) :
                    $value = $term->term_id;
                    $label = $term->name;

                    if (is_array($current_option_value))
                    {
                        $selected = in_array($value, $current_option_value) ? 'selected="selected"' : '';
                    }
                    else
                    {
                        $selected = ($current_option_value == $value) ? 'selected="selected"' : '';
                    }
                    ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>><?php echo $label; ?></option>

                <?php endforeach; ?>

            </select>

            <script type="text/javascript">
                <!--
                jQuery("document").ready(function ($) {
                    $("#<?php echo esc_attr($option_id); ?>").select2({
                        <?php if ( !is_admin()) echo "dropdownParent: $('#" . esc_attr($option_id) . "').parent(),"; ?>
                        width: "100%"
                    });
                });
                //-->
            </script>
            <?php
            if (isset($after))
            {
                echo $after;
            }
        }

        /**
         * Output a select field for a post type
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_post_type_select_field($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            $this->plugin->enable_library('jquery.select2');

            $post_types = get_post_types('', 'objects');
            $current_option_value = $this->options[$option_id];

            $field_name = esc_attr(self::$OPTIONS_GROUP) . '[' . esc_attr($option_id) . ']';
            if (isset($multiple) && $multiple)
            {
                $field_name .= '[]';
            }

            $field_id = esc_attr($option_id);

            $multiple = isset($multiple) && $multiple ? 'multiple="multiple" ' : '';
            ?>

            <select id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" <?php echo $multiple; ?>>

                <?php foreach ($post_types as $type => $obj) :
                    if (isset($exclude) && in_array($type, $exclude)) continue;

                    $value = $type;
                    $label = isset($obj->labels->singular_name) ? $obj->labels->singular_name : $type;

                    if (is_array($current_option_value))
                    {
                        $selected = in_array($value, $current_option_value) ? 'selected="selected"' : '';
                    }
                    else
                    {
                        $selected = ($current_option_value == $value) ? 'selected="selected"' : '';
                    }
                    ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>><?php echo $label; ?></option>

                <?php endforeach; ?>

            </select>

            <script type="text/javascript">
                <!--
                jQuery("document").ready(function ($) {
                    $("#<?php echo esc_attr($option_id); ?>").select2({
                        <?php if ( !is_admin()) echo "dropdownParent: $('#" . esc_attr($option_id) . "').parent(),"; ?>
                        width: "100%"
                    });
                });
                //-->
            </script>
            <?php
            if (isset($after))
            {
                echo $after;
            }
        }

        /**
         * Output a select field for a theme
         *
         * @param string $option_id
         * @param array  $options
         * @param string $caption
         */
        public function print_theme_select_field($args)
        {
            extract($args);

            if (isset($before))
            {
                echo $before;
            }

            $this->plugin->enable_library('jquery.select2');

            echo sprintf('<select id="%s" name="%s[%s]">', esc_attr($option_id), self::$OPTIONS_GROUP,
                esc_attr($option_id));

            $theme_locations = apply_filters('cuar/core/settings/theme-root-directories', array(
                array(
                    'base'  => 'plugin',
                    'type'  => $theme_type,
                    'dir'   => CUAR_PLUGIN_DIR . '/skins/' . $theme_type,
                    'label' => __('Main plugin folder', 'cuar')
                ),
                array(
                    'base'  => 'user-theme',
                    'type'  => $theme_type,
                    'dir'   => untrailingslashit(get_stylesheet_directory()) . '/customer-area/skins/' . $theme_type,
                    'label' => __('Current theme folder', 'cuar')
                ),
                array(
                    'base'  => 'wp-content',
                    'type'  => $theme_type,
                    'dir'   => untrailingslashit(WP_CONTENT_DIR) . '/customer-area/skins/' . $theme_type,
                    'label' => __('WordPress content folder', 'cuar')
                )
            ));

            foreach ($theme_locations as $theme_location)
            {
                if ($theme_location['type']!=$theme_type) continue;

                $dir_content = glob($theme_location['dir'] . '/*');
                if (false === $dir_content)
                {
                    continue;
                }

                $subfolders = array_filter($dir_content, 'is_dir');

                foreach ($subfolders as $s)
                {
                    $theme_name = basename($s);
                    $label = $theme_location['label'] . ' - ' . $theme_name;
                    if ($theme_location['base'] == 'addon')
                    {
                        $value = esc_attr($theme_location['base'] . '%%' . $theme_location['addon-name'] . '%%' . $theme_name);
                    }
                    else
                    {
                        $value = esc_attr($theme_location['base'] . '%%' . $theme_name);
                    }
                    $selected = ($this->options[$option_id] == $value) ? 'selected="selected"' : '';

                    echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, $label);
                }
            }

            echo '</select>';

            echo '<script type="text/javascript">
                <!--
                jQuery("document").ready(function ($) {
                    $("#' . esc_attr($option_id) . '").select2({
                        ' . (!is_admin() ? 'dropdownParent: $("#' . esc_attr($option_id) . '.parent()"),' : '') . '
                        width: "100%"
                    });
                });
                //-->
            </script>';

            if (isset($after))
            {
                echo $after;
            }
        }

        /* ------------ OTHER FUNCTIONS --------------------------------------------------------------------------------- */

        /**
         * Prints a sidebox on the side of the settings screen
         *
         * @param string $title
         * @param string $content
         */
        public function print_sidebox($title, $content)
        {
            echo '<div class="cuar-sidebox">';
            echo '<h2 class="cuar-sidebox-title">' . $title . '</h2>';
            echo '<div class="cuar-sidebox-content">' . $content . '</div>';
            echo '</div>';
        }

        /**
         * Update an option and persist to DB if asked to
         *
         * @param string  $option_id
         * @param mixed   $new_value
         * @param boolean $commit
         */
        public function update_option($option_id, $new_value, $commit = true)
        {
            $this->options [$option_id] = $new_value;
            if ($commit)
            {
                $this->save_options();
            }
        }

        /**
         * Persist the current plugin options to DB
         */
        public function save_options()
        {
            update_option(CUAR_Settings::$OPTIONS_GROUP, $this->options);
        }

        public function reset_defaults()
        {
            $this->options = array();
            $this->save_options();
            $this->reload_options();
        }

        public function set_options($opt)
        {
            $this->options = $opt;
            $this->save_options();
            $this->reload_options();
        }

        public function update_option_default($option_id, $new_value)
        {
            $this->default_options[$option_id] = $new_value;
        }

        /**
         * Persist the current plugin options to DB
         */
        public function get_options()
        {
            return $this->options;
        }

        /**
         * Persist the current plugin options to DB
         */
        public function get_default_options()
        {
            return $this->default_options;
        }

        /**
         * Load the options (and defaults if the options do not exist yet
         */
        private function reload_options()
        {
            $current_options = get_option(CUAR_Settings::$OPTIONS_GROUP);

            $this->default_options = apply_filters('cuar/core/settings/default-options', array());

            if ( !is_array($current_options))
            {
                $current_options = array();
            }
            $this->options = array_merge($this->default_options, $current_options);

            do_action('cuar/core/settings/on-options-loaded', $this->options);
        }

        public static $OPTIONS_PAGE_SLUG = 'wpca-settings';
        public static $OPTIONS_GROUP = 'cuar_options';

        // Core options
        public static $OPTION_DEBUG_TEMPLATES = 'cuar_debug_templates';
        public static $OPTION_CURRENT_VERSION = 'cuar_current_version';
        public static $OPTION_INCLUDE_CSS = 'cuar_include_css';
        public static $OPTION_ADMIN_SKIN = 'cuar_admin_theme_url';
        public static $OPTION_FRONTEND_SKIN = 'cuar_frontend_theme_url';

        /**
         * @var CUAR_Plugin The plugin instance
         */
        private $plugin;

        /**
         * @var array
         */
        private $default_options;

        /**
         * @var array
         */
        private $tabs;

        /**
         * @var string
         */
        private $current_tab;
    }

    // This filter needs to be executed too early to be registered in the constructor
    add_filter('cuar/core/settings/default-options', array(
        'CUAR_Settings',
        'set_default_core_options'
    ));


endif; // if (!class_exists('CUAR_Settings')) :

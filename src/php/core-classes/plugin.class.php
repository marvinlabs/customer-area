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

if ( !class_exists('CUAR_Plugin')) :

    /**
     * The main plugin class
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_Plugin
    {

        /** @var CUAR_MessageCenter */
        private $message_center;

        /** @var CUAR_Settings */
        private $settings;

        /** @var CUAR_PluginActivationManager */
        private $activation_manager;

        /** @var CUAR_TemplateEngine */
        private $template_engine;

        /** @var CUAR_Licensing */
        private $licensing;

        /** @var CUAR_Logger */
        private $logger;

        public function __construct()
        {
            $this->message_center = new CUAR_MessageCenter(array('wpca-status', 'wpca-setup', 'wpca'));
            $this->activation_manager = new CUAR_PluginActivationManager();
            $this->template_engine = new CUAR_TemplateEngine('customer-area', false);
            $this->licensing = new CUAR_Licensing(new CUAR_PluginStore());
            $this->logger = new CUAR_Logger();
        }

        public function run()
        {
            $this->message_center->register_hooks();
            $this->activation_manager->register_hooks();

            add_action('plugins_loaded', array(&$this, 'load_textdomain'), 3);
            add_action('plugins_loaded', array(&$this, 'load_settings'), 5);
            add_action('plugins_loaded', array(&$this, 'load_addons'), 10);

            add_action('init', array(&$this, 'load_scripts'), 7);
            add_action('init', array(&$this, 'load_styles'), 8);
            add_action('init', array(&$this, 'load_defaults'), 9);

            if (is_admin())
            {
                add_action('admin_notices', array(&$this, 'print_admin_notices'));
                add_action('init', array(&$this, 'load_defaults'), 9);

                add_action('permalink_structure_changed', array(&$this, 'check_permalinks_enabled'));

                add_action('cuar/core/activation/run-deferred-action?action_id=check-template-files', array(&$this, 'check_templates'));
                add_action('cuar/core/activation/run-deferred-action?action_id=check-permalink-settings', array(&$this, 'check_permalinks_enabled'));
            }
            else
            {
                add_action('plugins_loaded', array(&$this, 'load_theme_functions'), 7);
            }
        }

        /**
         * @return CUAR_Plugin
         */
        public static function get_instance()
        {
            global $cuar_plugin;

            return $cuar_plugin;
        }

        /**
         * @return CUAR_MessageCenter
         */
        public function get_message_center()
        {
            return $this->message_center;
        }

        /**
         * @return CUAR_TemplateEngine
         */
        public function get_template_engine()
        {
            return $this->template_engine;
        }

        /**
         * @return CUAR_Licensing
         */
        public function get_licensing()
        {
            return $this->licensing;
        }

        /**
         * @return CUAR_Logger
         */
        public function get_logger()
        {
            return $this->logger;
        }

        /*------- MAIN HOOKS INTO WP ------------------------------------------------------------------------------------*/

        public function load_settings()
        {
            $this->settings = new CUAR_Settings($this);

            // Configure some components
            $this->template_engine->enable_debug($this->get_option(CUAR_Settings::$OPTION_DEBUG_TEMPLATES));
        }

        /**
         * Load the translation file for current language. Checks in wp-content/languages first
         * and then the customer-area/languages.
         *
         * Edits to translation files inside customer-area/languages will be lost with an update
         * **If you're creating custom translation files, please use the global language folder.**
         *
         * @param string $domain      The text domain
         * @param string $plugin_name The plugin folder name
         */
        public function load_textdomain($domain = 'cuar', $plugin_name = 'customer-area')
        {
            if (empty($domain)) $domain = 'cuar';
            if (empty($plugin_name)) $plugin_name = 'customer-area';

            // Traditional WordPress plugin locale filter
            $locale = apply_filters('plugin_locale', get_locale(), $domain);
            $mo_file = sprintf('%1$s-%2$s.mo', $domain, $locale);

            $locations = array(
                WP_CONTENT_DIR . '/customer-area/languages/' . $mo_file,
                WP_LANG_DIR . '/customer-area/' . $mo_file,
                WP_LANG_DIR . '/' . $mo_file
            );

            // Try the user locations
            foreach ($locations as $path)
            {
                if (file_exists($path))
                {
                    load_textdomain($domain, $path);

                    return;
                }
            }

            // Not found above, load the default plugin file if it exists
            load_plugin_textdomain($domain, false, $plugin_name . '/languages');
        }

        /**
         * Loads the required javascript files (only when not in admin area)
         */
        public function load_scripts()
        {
            global $wp_locale;

            // TODO Move those messages to their respective add-ons
            if (is_admin())
            {
                $messages = apply_filters('cuar/core/js-messages?zone=admin', array(
                    'ajaxUrl'                       => admin_url('admin-ajax.php'),
                    'checkingLicense'               => __('Checking license...', 'cuar'),
                    'unreachableLicenseServerError' => __('Failed to contact server', 'cuar'),
                    'jeditableIndicator'            => esc_attr__('Saving...', 'cuar'),
                    'jeditableTooltip'              => esc_attr__('Click to edit...', 'cuar'),
                    'jeditableSubmit'               => esc_attr__('OK', 'cuar'),
                    'jeditableCancel'               => esc_attr__('Cancel', 'cuar'),
                    'datepickerDateFormat'          => _x('MM d, yy', 'Date picker JS date format', 'cuar'),
                    'datepickerCloseText'           => _x('Clear', 'Date picker text', 'cuar'),
                    'datepickerCurrentText'         => _x('Today', 'Date picker text', 'cuar'),
                    'datepickerMonthNames'          => array_values($wp_locale->month),
                    'datepickerMonthNamesShort'     => array_values($wp_locale->month_abbrev),
                    'datepickerMonthStatus'         => _x('Show a different month', 'Date picker text', 'cuar'),
                    'datepickerDayNames'            => array_values($wp_locale->weekday),
                    'datepickerDayNamesShort'       => array_values($wp_locale->weekday_abbrev),
                    'datepickerDayNamesMin'         => array_values($wp_locale->weekday_initial),
                    'datepickerFirstDay'            => get_option('start_of_week'),
                    'datepickerIsRTL'               => $wp_locale->is_rtl(),
                ));
                wp_register_script('cuar.admin', CUAR_PLUGIN_URL . 'assets/admin/js/customer-area.min.js', array('jquery'));
                wp_localize_script('cuar.admin', 'cuar', $messages);
            }
            else
            {
                $messages = apply_filters('cuar/core/js-messages?zone=frontend', array(
                    'ajaxUrl'                   => admin_url('admin-ajax.php'),
                    'jeditableIndicator'        => esc_attr__('Saving...', 'cuar'),
                    'jeditableTooltip'          => esc_attr__('Click to edit...', 'cuar'),
                    'jeditableSubmit'           => esc_attr__('OK', 'cuar'),
                    'jeditableCancel'           => esc_attr__('Cancel', 'cuar'),
                    'datepickerDateFormat'      => _x('MM d, yy', 'Date picker JS date format', 'cuar'),
                    'datepickerCloseText'       => _x('Clear', 'Date picker text', 'cuar'),
                    'datepickerCurrentText'     => _x('Today', 'Date picker text', 'cuar'),
                    'datepickerMonthNames'      => array_values($wp_locale->month),
                    'datepickerMonthNamesShort' => array_values($wp_locale->month_abbrev),
                    'datepickerMonthStatus'     => _x('Show a different month', 'Date picker text', 'cuar'),
                    'datepickerDayNames'        => array_values($wp_locale->weekday),
                    'datepickerDayNamesShort'   => array_values($wp_locale->weekday_abbrev),
                    'datepickerDayNamesMin'     => array_values($wp_locale->weekday_initial),
                    'datepickerFirstDay'        => get_option('start_of_week'),
                    'datepickerIsRTL'           => $wp_locale->is_rtl(),
                ));
                wp_register_script('cuar.frontend', CUAR_PLUGIN_URL . 'assets/frontend/js/customer-area.min.js', array('jquery'));
                wp_localize_script('cuar.frontend', 'cuar', $messages);
            }
        }

        /**
         * Loads the required css (only when not in admin area)
         */
        public function load_styles()
        {
            if (is_admin())
            {
                wp_enqueue_style(
                    'cuar.admin',
                    $this->get_admin_theme_url() . '/assets/css/styles.min.css');
            }
            else if ( !current_theme_supports('customer-area.stylesheet')
                && $this->get_option(CUAR_Settings::$OPTION_INCLUDE_CSS)
            )
            {

                wp_enqueue_style(
                    'cuar.frontend',
                    $this->get_frontend_theme_url() . '/assets/css/styles.min.css',
                    array('dashicons'),
                    $this->get_version()
                );
            }
        }

        /**
         * Initialise some defaults for the plugin (add basic capabilities, ...)
         */
        public function load_defaults()
        {
            // Start a session when we save a post in order to store error logs
            if ( !session_id()) session_start();
        }

        /*------- TEMPLATING & THEMING ----------------------------------------------------------------------------------*/

        public function get_theme($theme_type)
        {
            return explode('%%', $this->get_option($theme_type == 'admin' ? CUAR_Settings::$OPTION_ADMIN_SKIN : CUAR_Settings::$OPTION_FRONTEND_SKIN, ''));
        }

        public function get_theme_url($theme_type)
        {
            $theme = $this->get_theme($theme_type);

            if (count($theme) == 1)
            {
                // Still not on CUAR 4.0? Option value is already the URL
                return $theme[0];
            }
            else if (count($theme) == 2)
            {
                $base = '';
                switch ($theme[0])
                {
                    case 'plugin':
                        $base = untrailingslashit(CUAR_PLUGIN_URL) . '/skins/';
                        break;
                    case 'user-theme':
                        $base = untrailingslashit(get_stylesheet_directory_uri()) . '/customer-area/skins/';
                        break;
                    case 'wp-content':
                        $base = untrailingslashit(WP_CONTENT_URL) . '/customer-area/skins/';
                        break;
                }

                return $base . $theme_type . '/' . $theme[1];
            }
            else if (count($theme) == 3)
            {
                // For addons
                // 0 = 'addon'
                // 1 = addon folder name
                // 2 = skin folder name
                switch ($theme[0])
                {
                    case 'addon':
                        return untrailingslashit(WP_PLUGIN_URL) . '/' . $theme[1] . '/skins/' . $theme_type . '/' . $theme[2];
                }
            }

            return '';
        }

        public function get_theme_path($theme_type)
        {
            $theme = $this->get_theme($theme_type);

            if (count($theme) == 1)
            {
                // Still not on CUAR 4.0? then we have a problem
                return '';
            }
            else if (count($theme) == 2)
            {
                $base = '';
                switch ($theme[0])
                {
                    case 'plugin':
                        $base = untrailingslashit(CUAR_PLUGIN_DIR) . '/skins';
                        break;
                    case 'user-theme':
                        $base = untrailingslashit(get_stylesheet_directory()) . '/customer-area/skins';
                        break;
                    case 'wp-content':
                        $base = untrailingslashit(WP_CONTENT_DIR) . '/customer-area/skins';
                        break;
                }

                return $base . '/' . $theme_type . '/' . $theme[1];
            }
            else if (count($theme) == 3)
            {
                // For addons
                // 0 = 'addon'
                // 1 = addon folder name
                // 2 = skin folder name
                switch ($theme[0])
                {
                    case 'addon':
                        return untrailingslashit(WP_PLUGIN_DIR) . '/' . $theme[1] . '/skins/' . $theme_type . '/' . $theme[2];
                }
            }

            return '';
        }

        public function get_admin_theme_url()
        {
            return $this->get_theme_url('admin');
        }

        /**
         * This function offers a way for addons to do their stuff after this plugin is loaded
         */
        public function get_frontend_theme_url()
        {
            return $this->get_theme_url('frontend');
        }

        /**
         * This function offers a way for addons to do their stuff after this plugin is loaded
         */
        public function get_frontend_theme_path()
        {
            return $this->get_theme_path('frontend');
        }

        public function load_theme_functions()
        {
            if (current_theme_supports('customer-area.stylesheet')
                || !$this->get_option(CUAR_Settings::$OPTION_INCLUDE_CSS)
            )
            {
                return;
            }

            $theme_path = trailingslashit($this->get_frontend_theme_path());
            if (empty($theme_path)) return;

            $functions_path = $theme_path . 'cuar-functions.php';
            if (file_exists($functions_path))
            {
                include_once($functions_path);
            }
        }

        public function is_customer_area_page()
        {
            $cp_addon = $this->get_addon('customer-pages');

            return $cp_addon->is_customer_area_page();
        }

        public function get_customer_page_id($slug)
        {
            $cp_addon = $this->get_addon('customer-pages');

            return $cp_addon->get_page_id($slug);
        }

        public function login_then_redirect_to_page($page_slug)
        {
            $cp_addon = $this->get_addon('customer-pages');
            $redirect_page_id = $cp_addon->get_page_id($page_slug);
            if ($redirect_page_id > 0)
            {
                $redirect_url = get_permalink($redirect_page_id);
            }
            else
            {
                $redirect_url = '';
            }

            $this->login_then_redirect_to_url($redirect_url);
        }

        public function login_then_redirect_to_url($redirect_to = '')
        {
            $login_url = apply_filters('cuar/routing/login-url', null, $redirect_to);
            if ($login_url == null)
            {
                $login_url = wp_login_url($redirect_to);
            }

            wp_redirect($login_url);
            exit;
        }

        /*------- GENERAL MAINTENANCE -----------------------------------------------------------------------------------*/

        public function set_attention_needed($message_id, $message, $priority)
        {
            $this->message_center->add_warning($message_id, $message, $priority);
        }

        public function clear_attention_needed($message_id)
        {
            $this->message_center->remove_warning($message_id);
        }

        public function is_attention_needed($message_id)
        {
            return $this->message_center->is_warning_registered($message_id);
        }

        public function is_warning_ignored($warning_id)
        {
            return $this->message_center->is_warning_ignored($warning_id);
        }

        public function ignore_warning($warning_id)
        {
            $this->message_center->ignore_warning($warning_id);
        }

        public function get_attention_needed_messages()
        {
            return $this->message_center->get_warnings();
        }

        /*------- SETTINGS ----------------------------------------------------------------------------------------------*/

        /**
         * Access to the settings (delegated to our settings class instance)
         *
         * @param string $option_id The ID of the option to retrieve
         *
         * @return mixed The option value
         */
        public function get_option($option_id)
        {
            return $this->settings->get_option($option_id);
        }

        public function update_option($option_id, $new_value, $commit = true)
        {
            $this->settings->update_option($option_id, $new_value, $commit);
        }

        public function save_options()
        {
            $this->settings->save_options();
        }

        public function reset_defaults()
        {
            $this->settings->reset_defaults();
        }

        public function get_default_options()
        {
            return $this->settings->get_default_options();
        }

        public function get_version()
        {
            return $this->get_option(CUAR_Settings::$OPTION_CURRENT_VERSION);
        }

        public function get_major_version()
        {
            $tokens = explode('.', $this->get_version());

            return $tokens[0] . '.' . $tokens[1];
        }

        public function get_options()
        {
            return $this->settings->get_options();
        }

        public function set_options($opt)
        {
            $this->settings->set_options($opt);
        }

        /*------- ADD-ONS -----------------------------------------------------------------------------------------------*/

        /**
         * This function offers a way for addons to do their stuff after this plugin is loaded
         */
        public function load_addons()
        {
            do_action('cuar/core/addons/before-init', $this);
            do_action('cuar/core/addons/init', $this);
            do_action('cuar/core/addons/after-init', $this);

            // Check add-on versions
            if (is_admin())
            {
                $this->check_addons_required_versions();
            }
        }

        /**
         * Register an add-on in the plugin
         *
         * @param CUAR_AddOn $addon
         */
        public function register_addon($addon)
        {
            $this->registered_addons[$addon->addon_id] = $addon;
        }

        /**
         * Get registered add-ons
         */
        public function get_registered_addons()
        {
            return $this->registered_addons;
        }

        public function tag_addon_as_commercial($addon_id)
        {
            $this->commercial_addons[$addon_id] = $this->get_addon($addon_id);
        }

        public function get_commercial_addons()
        {
            return $this->commercial_addons;
        }

        public function has_commercial_addons()
        {
            return !empty($this->commercial_addons);
        }

        /**
         * Shows a compatibity warning
         */
        public function check_permalinks_enabled()
        {
            if ( !get_option('permalink_structure'))
            {
                $this->set_attention_needed('permalinks-disabled',
                    sprintf(__('Permalinks are disabled, Customer Area will not work properly. Please <a href="%1$s">enable them in the WordPress settings</a>.',
                        'cuar'),
                        admin_url('options-permalink.php')),
                    10);
            }
            else
            {
                $this->clear_attention_needed('permalinks-disabled');
            }
        }

        /**
         * Shows a compatibity warning
         */
        public function check_addons_required_versions()
        {
            $current_version = $this->get_version();
            $needs_attention = false;

            foreach ($this->registered_addons as $id => $addon)
            {
                if ($current_version < $addon->min_cuar_version)
                {
                    $needs_attention = true;
                    break;
                }
            }

            if ($needs_attention)
            {
                $this->set_attention_needed('outdated-plugin-version',
                    __('Some add-ons require a newer version of the main plugin.', 'cuar'),
                    10);
            }
            else
            {
                $this->clear_attention_needed('outdated-plugin-version');
            }
        }

        /**
         * Get add-on
         */
        public function get_addon($id)
        {
            return isset($this->registered_addons[$id]) ? $this->registered_addons[$id] : null;
        }

        /** @var array */
        private $registered_addons = array();

        /** @var array */
        private $commercial_addons = array();

        /*------- ADMIN NOTICES -----------------------------------------------------------------------------------------*/

        /**
         * Print the eventual errors that occured during a post save/update
         */
        public function print_admin_notices()
        {
            $notices = $this->get_admin_notices();

            if ($notices)
            {
                foreach ($notices as $n)
                {
                    echo sprintf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($n['type']), $n['msg']);
                }
            }
            $this->clear_admin_notices();
        }

        /**
         * Remove the notices stored in the session for save posts
         */
        public function clear_admin_notices()
        {
            if (isset($_SESSION['cuar_admin_notices']))
            {
                unset($_SESSION['cuar_admin_notices']);
            }
        }

        /**
         * Remove the stored notices
         */
        private function get_admin_notices()
        {
            return isset($_SESSION['cuar_admin_notices']) && !empty($_SESSION['cuar_admin_notices']) ? $_SESSION['cuar_admin_notices'] : false;
        }

        /**
         * Add an admin notice (useful when in a save post function for example)
         *
         * @param string $msg
         * @param string $type error or updated
         */
        public function add_admin_notice($msg, $type = 'error')
        {
            if (empty($_SESSION['cuar_admin_notices']))
            {
                $_SESSION['cuar_admin_notices'] = array();
            }
            $_SESSION['cuar_admin_notices'][] = array(
                'type' => $type,
                'msg'  => $msg
            );
        }

        /*------- EXTERNAL LIBRARIES ------------------------------------------------------------------------------------*/

        /**
         * Allow the use of an external library provided by Customer Area
         *
         * @param string $id The ID for the external library
         */
        public function enable_library($library_id)
        {
            // Only if the theme does not already support this and we are viewing the frontend
            if ( !is_admin())
            {
                $theme_support = get_theme_support('customer-area.library.' . $library_id);
                if ($theme_support === true || (is_array($theme_support) && in_array('files', $theme_support[0]))) return;
            }

            do_action('cuar/core/libraries/before-enable?id=' . $library_id);

            switch ($library_id)
            {
                case 'jquery.select2':
                {
                    wp_enqueue_script('jquery.select2', CUAR_PLUGIN_URL . 'libs/select2/select2.min.js', array('jquery'), $this->get_version());

                    $locale = get_locale();
                    if ($locale && !empty($locale))
                    {
                        $locale = str_replace("_", "-", $locale);
                        $locale_parts = explode("-", $locale);

                        $loc_files = array('select2_locale_' . $locale . '.js');

                        if (count($locale_parts) > 0)
                        {
                            $loc_files[] = 'select2_locale_' . $locale_parts[0] . '.js';
                        }

                        foreach ($loc_files as $lf)
                        {
                            if (file_exists(CUAR_PLUGIN_DIR . '/libs/select2/' . $lf))
                            {
                                wp_enqueue_script('jquery.select2.locale', CUAR_PLUGIN_URL . 'libs/select2/' . $lf, array('jquery.select2'),
                                    $this->get_version());
                                break;
                            }
                        }
                    }

                    wp_enqueue_style('jquery.select2', CUAR_PLUGIN_URL . 'libs/select2/select2.css', $this->get_version());
                }
                    break;

                case 'jquery.datepicker':
                {
                    wp_enqueue_script('jquery-ui-datepicker');
                    $protocol = is_ssl() ? 'https' : 'http';
                    wp_enqueue_style('jquery-ui', $protocol . '://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css');
                }
                    break;

                case 'bootstrap.dropdown':
                {
                    wp_enqueue_script('bootstrap.transition', CUAR_PLUGIN_URL . 'libs/bootstrap/js/transition.js', array('jquery'), $this->get_version());
                    wp_enqueue_script('bootstrap.dropdown', CUAR_PLUGIN_URL . 'libs/bootstrap/js/dropdown.js', array('jquery', 'bootstrap.transition'),
                        $this->get_version());
                }
                    break;

                case 'bootstrap.transition':
                {
                    wp_enqueue_script('bootstrap.transition', CUAR_PLUGIN_URL . 'libs/bootstrap/js/transition.js', array('jquery'), $this->get_version());
                }
                    break;

                case 'bootstrap.collapse':
                {
                    wp_enqueue_script('bootstrap.collapse', CUAR_PLUGIN_URL . 'libs/bootstrap/js/collapse.js', array('jquery'), $this->get_version());
                }
                    break;

                case 'bootstrap.slider':
                {
                    wp_enqueue_script('bootstrap.slider', CUAR_PLUGIN_URL . 'libs/bootstrap-slider/dist/bootstrap-slider.min.js', array('jquery'), $this->get_version());
                }
                    break;

                case 'jquery.repeatable-fields':
                {
                    wp_enqueue_script('jquery.repeatable-fields', CUAR_PLUGIN_URL . 'libs/repeatable-fields/repeatable-fields.min.js',
                        array('jquery', 'jquery-ui-sortable'), $this->get_version());
                }
                    break;

                case 'jquery.autogrow':
                {
                    wp_enqueue_script('jquery.autogrow', CUAR_PLUGIN_URL . 'libs/autogrow/autogrow.min.js', array('jquery'), $this->get_version());
                }
                    break;

                case 'jquery.jeditable':
                {
                    wp_enqueue_script('jquery.autogrow', CUAR_PLUGIN_URL . 'libs/autogrow/autogrow.min.js', array('jquery'), $this->get_version());
                    wp_enqueue_script('jquery.jeditable', CUAR_PLUGIN_URL . 'libs/jeditable/jquery.jeditable.min.js', array('jquery'), $this->get_version());
                    wp_enqueue_script('jquery.jeditable.autogrow', CUAR_PLUGIN_URL . 'libs/jeditable/jquery.jeditable.autogrow.min.js',
                        array('jquery', 'jquery.jeditable', 'jquery.autogrow'), $this->get_version());
                    wp_enqueue_script('jquery.jeditable.datepicker', CUAR_PLUGIN_URL . 'libs/jeditable/jquery.jeditable.datepicker.js',
                        array('jquery', 'jquery.jeditable', 'jquery-ui-datepicker'), $this->get_version());
                }
                    break;

                case 'jquery.fileupload':
                {
                    wp_enqueue_script('jquery.ui.widget', CUAR_PLUGIN_URL . 'libs/fileupload/vendor/jquery.ui.widget.js', array('jquery'),
                        $this->get_version());
                    wp_enqueue_script('jquery.iframe-transport', CUAR_PLUGIN_URL . 'libs/fileupload/jquery.iframe-transport.js', array('jquery'),
                        $this->get_version());
                    wp_enqueue_script('jquery.fileupload', CUAR_PLUGIN_URL . 'libs/fileupload/jquery.fileupload.js', array('jquery'), $this->get_version());
                }
                    break;

                default:
                    do_action('cuar/core/libraries/enable?id=' . $library_id);
            }

            do_action('cuar/core/libraries/after-enable?id=' . $library_id);
        }

        /*------- TEMPLATES ---------------------------------------------------------------------------------------------*/

        /**
         * Check all template files and log a warning if there are any outdated templates
         */
        public function check_templates()
        {
            $dirs_to_scan = apply_filters('cuar/core/status/directories-to-scan', array(CUAR_PLUGIN_DIR => __('WP Customer Area', 'cuar')));

            $outdated_templates = $this->template_engine->check_templates($dirs_to_scan);

            if ( !empty($outdated_templates))
            {
                $this->set_attention_needed('outdated-templates', __('Some template files you have overridden seem to be outdated.', 'cuar'), 100);
            }
            else
            {
                $this->clear_attention_needed('outdated-templates');
            }
        }

        /**
         * Delegate function for the template engine
         */
        public function get_template_file_path($default_root, $filenames, $relative_path = 'templates', $fallback_filename = '')
        {
            if ( !is_array($filenames)) $filenames = array($filenames);
            if ( !empty($fallback_filename)) $filenames[] = $fallback_filename;

            return $this->template_engine->get_template_file_path($default_root, $filenames, $relative_path);
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

        /**
         * Tell if the post type is managed by the plugin or not (used to build the menu, etc.)
         *
         * @param string $post_type     The post type to check
         * @param array  $private_types The private types of the plugin (null if you simply want the plugin to fetch them
         *                              dynamically
         *
         * @return bool
         */
        public function is_type_managed($post_type, $private_types = null)
        {
            if ($private_types == null)
            {
                $private_types = $this->get_private_types();
            }

            return apply_filters('cuar/core/types/is-type-managed',
                isset($private_types[$post_type]), $post_type, $private_types);
        }

        /**
         * Get both private content and container types
         * @return array
         */
        public function get_private_types()
        {
            return array_merge(
                $this->get_content_types(),
                $this->get_container_types()
            );
        }

        /**
         * Get both private content and container types
         * @return array
         */
        public function get_private_post_types()
        {
            return array_merge(
                $this->get_content_post_types(),
                $this->get_container_post_types()
            );
        }

        /**
         * Tells which post types are private (shown on the customer area page)
         * @return array
         */
        public function get_content_post_types()
        {
            return apply_filters('cuar/core/post-types/content', array());
        }

        /**
         * Get the content types descriptors. Each descriptor is an array with:
         * - 'label-plural'                - plural label
         * - 'label-singular'            - singular label
         * - 'content-page-addon'        - content page addon associated to this type
         * - 'type'                     - 'content'
         *
         * @return array keys are post_type and values are arrays as described above
         */
        public function get_content_types()
        {
            return apply_filters('cuar/core/types/content', array());
        }

        /**
         * Tells which container post types are available
         * @return array
         */
        public function get_container_post_types()
        {
            return apply_filters('cuar/core/post-types/container', array());
        }

        /**
         * Get the post type descriptors. Each descriptor is an array with:
         * - 'label-plural'                - plural label
         * - 'label-singular'            - singular label
         * - 'container-page-slug'        - main page slug associated to this type
         * - 'type'                     - 'container'
         * @return array
         */
        public function get_container_types()
        {
            return apply_filters('cuar/core/types/container', array());
        }

        public function get_default_wp_editor_settings()
        {
            return apply_filters('cuar/ui/default-wp-editor-settings', array(
                'textarea_rows' => 5,
                'editor_class'  => 'form-control',
                'quicktags'     => false,
                'media_buttons' => false,
                'teeny'         => true,
                'dfw'           => true
            ));
        }
    }

endif; // if (!class_exists('CUAR_Plugin')) :
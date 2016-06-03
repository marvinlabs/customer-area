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
include_once(CUAR_INCLUDES_DIR . '/core-addons/admin-area/private-container-table.class.php');
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
        $this->admin_menu_helper = new CUAR_AdminMenuHelper($plugin, $this);

        if (is_admin())
        {
            add_action('cuar/core/on-plugin-update', array(&$this, 'plugin_version_upgrade'), 10, 2);
            add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'), 5);
            add_action('init', array(&$this, 'restrict_admin_access'), 1);
            add_action('admin_footer', array(&$this, 'highlight_menu_item'));

            // Block the list pages we already handle
            add_action("load-edit.php", array(&$this, 'block_default_admin_pages'));

            // Settings
            add_action('cuar/core/settings/print-settings?tab=cuar_core', array(&$this, 'print_core_settings'), 20, 2);
            add_filter('cuar/core/settings/validate-settings?tab=cuar_core', array(&$this, 'validate_core_options'), 20, 3);

            // Ajax
            add_action('wp_ajax_cuar_folder_action', array(&$this, 'ajax_folder_action'));
        }
        else
        {
            add_action('init', array(&$this, 'hide_admin_bar'));
        }
    }

    /*------- AJAX --------------------------------------------------------------------------------------------------*/

    public function ajax_folder_action()
    {
        if ( !current_user_can('manage_options'))
        {
            wp_send_json_error(__('You are not allowed to do that', 'cuar'));
        }

        $action = isset($_POST['folder_action']) ? $_POST['folder_action'] : '';
        $path = isset($_POST['path']) ? $_POST['path'] : '';
        $extra = isset($_POST['extra']) ? $_POST['extra'] : '';
        if (empty($action) || empty($path))
        {
            wp_send_json_error(__('Missing parameters', 'cuar'));
        }

        switch ($action)
        {
            case 'chmod':
                if (empty($extra))
                {
                    wp_send_json_error(__('Missing parameters', 'cuar'));
                }

                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $item)
                {
                    if (false === chmod($item, octdec($extra)))
                    {
                        wp_send_json_error(__('Error while changing permissions. PHP probably does not have the required permissions. Please do it manually using FTP or SSH.',
                            'cuar'));
                    }
                }

                $current_perms = @fileperms($path) & 0777;
                if ($current_perms !== octdec($extra))
                {
                    wp_send_json_error(__('Permissions could not be changed. PHP probably does not have the required permissions. Please do it manually using FTP or SSH.',
                        'cuar'));
                }
                break;

            case 'mkdir':
                if (empty($extra)) $extra = 0700;
                if ( !is_int($extra)) $extra = octdec($extra);
                @mkdir($path, $extra, true);
                if ( !file_exists($path))
                {
                    wp_send_json_error(__('Folder could not be created', 'cuar'));
                }
                break;

            case 'secure-htaccess':
                $htaccess_template_path = trailingslashit(CUAR_PLUGIN_DIR) . 'extras/protect-folder.htaccess';
                $dest_htaccess_path = trailingslashit($path) . '.htaccess';

                @copy($htaccess_template_path, $dest_htaccess_path);
                if ( !file_exists($dest_htaccess_path))
                {
                    wp_send_json_error(__('htaccess file could not be copied. Please do it manually using FTP or SSH.', 'cuar'));
                }

                break;
        }

        wp_send_json_success();
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
                        admin_url('admin.php?page=wpca-settings&tab=cuar_capabilities'))
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
     * Hide the admin bar to users who should not see it
     */
    function hide_admin_bar()
    {
        if ( !is_admin()
            && $this->is_admin_area_access_restricted()
            && !current_user_can('cuar_view_top_bar')
        )
        {
            show_admin_bar(false);
        }
    }

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
        if ( !$this->current_user_can_access_admin() && !(defined('DOING_AJAX') && DOING_AJAX))
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
     * @return bool
     */
    public function current_user_can_access_admin()
    {
        return !$this->is_admin_area_access_restricted() || current_user_can('cuar_access_admin_panel');
    }

    /**
     * Protect the default edition and listing pages
     */
    public function block_default_admin_pages()
    {
        if (isset($_GET["post_type"]) && !isset($_GET['post']))
        {
            $post_type = $_GET["post_type"];
            $is_managed = $this->plugin->is_type_managed($post_type);
            if ( !$is_managed)
            {
                return;
            }

            $private_types = $this->plugin->get_private_types();
            if (isset($private_types[$post_type]))
            {
                $type = $private_types[$post_type]['type'];
                wp_redirect(admin_url("admin.php?page=wpca-list," . $type . "," . $post_type));
            }
        }
    }

    /**
     * The function that handles printing an admin page
     */
    public function print_admin_page()
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 'wpca-dashboard';
        $tokens = explode(',', $page);
        $page = str_replace('wpca-', '', $tokens[0]);

        switch ($page)
        {
            case 'wpca':
            case 'dashboard':
                $this->print_dashboard();
                break;

            case 'list':
                $type = $tokens[1];
                $post_type = $tokens[2];
                $this->print_private_post_list_page($post_type, $type);
                break;

            default:
                do_action('cuar/core/admin/print-admin-page?page=' . $page, $this, $_GET['page']);
        }
    }

    /**
     * Print a page that lists private content
     *
     * @param string $post_type
     * @param string $private_type_group (container|content)
     */
    protected function print_private_post_list_page($post_type, $private_type_group)
    {
        $post_type_object = get_post_type_object($post_type);
        $associated_taxonomies = get_object_taxonomies($post_type, 'object');

        $title_links = array();
        $title_links = $this->add_new_post_link($post_type_object, $post_type, $title_links);
        $title_links = $this->add_manage_taxonomy_links($post_type, $title_links);

        switch ($private_type_group)
        {
            case 'content':
                $title_links = apply_filters('cuar/core/admin/content-list-page/title-links?post_type=' . $post_type,
                    $title_links);
                $default_list_table_class = apply_filters(
                    'cuar/core/admin/content-list-page/list-table-class?post_type=' . $post_type,
                    'CUAR_PrivateContentTable');
                break;

            case 'container':
                $title_links = apply_filters('cuar/core/admin/container-list-page/title-links?post_type=' . $post_type,
                    $title_links);
                $default_list_table_class = apply_filters(
                    'cuar/core/admin/container-list-page/list-table-class?post_type=' . $post_type,
                    'CUAR_PrivateContainerTable');
                break;

            default:
                wp_die('Unknown private type. Must either be container or content');
                exit;
        }

        $list_table = new $default_list_table_class($this->plugin, array(
            'plural'   => $post_type_object->labels->name,
            'singular' => $post_type_object->labels->singular_name,
            'ajax'     => false
        ), $post_type_object, $associated_taxonomies);

        $list_table->initialize();

        $default_filter_template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/admin-area',
            'private-post-list-filters-' . $private_type_group . '.template.php',
            'templates',
            'private-post-list-filters.template.php');

        /** @noinspection PhpIncludeInspection */
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/admin-area',
            'private-post-list-page.template.php',
            'templates'));
    }

    /**
     * Print the dashboard/about page
     */
    protected function print_dashboard()
    {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'blog';
        $tabs = array(
            'whats-new' => array(
                'url'   => admin_url('admin.php?page=wpca&tab=whats-new'),
                'label' => __("What's new", 'cuar')
            ),
            'changelog' => array(
                'url'   => admin_url('admin.php?page=wpca&tab=changelog'),
                'label' => __("Change log", 'cuar')
            ),
            'blog'      => array(
                'url'   => admin_url('admin.php?page=wpca&tab=blog'),
                'label' => __("Live from the blog", 'cuar')
            ),
            'addons'    => array(
                'url'   => admin_url('admin.php?page=wpca&tab=addons'),
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

        // If upgrading from version prior to 6.4, update the default skin
        if (version_compare($from_version, '6.4.0', '<'))
        {
            $current_skin = $this->plugin->get_theme('frontend');

            if (count($current_skin) == 2 && $current_skin[0] == 'plugin'
                && ($current_skin[1] == 'default-v4' || $current_skin[1] == 'default')
            )
            {
                $this->plugin->update_option(CUAR_Settings::$OPTION_FRONTEND_SKIN, CUAR_FRONTEND_SKIN);
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
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        foreach ($taxonomies as $tax)
        {
            if (current_user_can($tax->cap->manage_terms))
            {
                $title_links[sprintf(__('Manage %1$s', 'cuar'),
                    $tax->labels->name)] = admin_url('edit-tags.php?taxonomy=' . $tax->name);
            }
        }

        return $title_links;
    }

    /**
     * Highlight the proper menu item in the customer area
     */
    public function highlight_menu_item()
    {
        global $post;

        $top_on = '';
        $top_off = '';
        $sub_on = '';
        $found = false;

        $private_types = $this->plugin->get_private_types();

        // New post & post edit pages
        if ( !$found && isset($post))
        {
            $post_type = get_post_type($post);
            $is_managed = $this->plugin->is_type_managed($post_type, $private_types);

            if ($is_managed && !$found)
            {
                $type = isset($private_types[$post_type]) ? $private_types[$post_type] : null;
                if ($type != null)
                {
                    $top_on = 'toplevel_page_wpca';
                    $top_off = 'menu-posts';
                    $sub_on = 'admin.php?page=wpca-list,' . $type['type'] . ',' . $post_type;
                    $found = true;
                }
            }
        }

        // Taxonomy pages
        if ( !$found && isset($_REQUEST['taxonomy']))
        {
            $tax = $_REQUEST['taxonomy'];

            if ( !$found)
            {
                foreach ($private_types as $post_type => $desc)
                {
                    $is_managed = $this->plugin->is_type_managed($post_type, $private_types);
                    if ( !$is_managed)
                    {
                        continue;
                    }

                    $taxonomies = get_object_taxonomies($post_type);
                    if (in_array($tax, $taxonomies))
                    {
                        $top_on = 'toplevel_page_wpca';
                        $top_off = 'menu-posts';
                        $sub_on = 'admin.php?page=wpca-list,' . $desc['type'] . ',' . $post_type;
                        $found = true;
                        break;
                    }
                }
            }
        }

        // Bail if nothing to do
        if ( !$found)
        {
            return;
        }

        // TODO MOVE THOSE SCRIPTS TO THE JS FILE
        ?>
        <script type="text/javascript">
            function cuar_unsetCurrentSubmenu(menu, href) {
                menu.find('a[href="' + href + '"]').parent('li').removeClass('current');
            }

            function cuar_setCurrentSubmenu(menu, href) {
                menu.find('a[href="' + href + '"]').parent('li').addClass('current');
            }

            function cuar_unsetCurrentMenu(menu, id) {
                menu.find('#' + id + ',#' + id + '>a:first-child')
                    .removeClass('wp-has-current-submenu')
                    .removeClass('wp-menu-open')
                    .addClass('wp-not-current-submenu');
            }

            function cuar_setCurrentMenu(menu, id) {
                menu.find('#' + id + ',#' + id + '>a:first-child')
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-menu-open')
                    .addClass('wp-has-current-submenu current');
            }

            jQuery(document).ready(function ($) {
                var menu = $('#adminmenu');

                <?php if (!empty($sub_on)) : ?>
                cuar_unsetCurrentSubmenu(menu, 'admin.php?page=wpca');
                cuar_setCurrentSubmenu(menu, '<?php echo $sub_on; ?>');
                <?php endif; ?>

                <?php if (!empty($top_on)) : ?>
                cuar_setCurrentMenu(menu, '<?php echo $top_on; ?>');
                <?php endif; ?>

                <?php if (!empty($top_off)) : ?>
                cuar_unsetCurrentMenu(menu, '<?php echo $top_off; ?>');
                <?php endif; ?>
            });
        </script>
        <?php
    }
}

// Make sure the addon is loaded
new CUAR_AdminAreaAddOn();


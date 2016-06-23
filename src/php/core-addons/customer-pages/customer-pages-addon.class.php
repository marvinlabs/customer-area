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


if ( !class_exists('CUAR_CustomerPagesAddOn')) :

    /**
     * Add-on to show the customer page
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_CustomerPagesAddOn extends CUAR_AddOn
    {

        public function __construct()
        {
            parent::__construct('customer-pages', '4.0.0');
        }

        public function get_addon_name()
        {
            return __('Customer Pages', 'cuar');
        }

        public function run_addon($plugin)
        {
            // Add a WordPress menu
            register_nav_menus(array(
                'cuar_main_menu' => 'WP Customer Area Navigation Menu'
            ));

            add_filter('wp_nav_menu_objects', array(&$this, 'fix_menu_item_classes'));
            add_filter('wp_get_nav_menu_items', array(&$this, 'filter_nav_menu_items'), 10, 3);

            if ($this->is_auto_menu_on_single_private_content_pages_enabled()) {
                add_filter('cuar/core/the_content', array(&$this, 'get_main_menu_for_single_private_content'), 99);
            }

            if ($this->is_auto_menu_on_customer_area_pages_enabled()) {
                add_filter('cuar/core/the_content', array(&$this, 'get_main_menu_for_customer_area_pages'), 99);
            }

            add_filter('wp_page_menu_args', array(&$this, 'exclude_pages_from_wp_page_menu'));

            add_action('pre_get_posts', array(&$this, 'exclude_pages_from_search_results'));

            if (is_admin()) {
                add_filter('cuar/core/status/sections', array(&$this, 'add_status_sections'));

                // Settings
                add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_settings_tab'), 300, 1);

                add_action('cuar/core/settings/print-settings?tab=cuar_frontend', array(&$this, 'print_frontend_settings'), 50, 2);
                add_filter('cuar/core/settings/validate-settings?tab=cuar_frontend', array(&$this, 'validate_frontend_settings'), 50, 3);

                add_action('cuar/core/settings/print-settings?tab=cuar_customer_pages', array(&$this, 'print_pages_settings'), 50, 2);
                add_filter('cuar/core/settings/validate-settings?tab=cuar_customer_pages', array(&$this, 'validate_pages_settings'), 50, 3);
            } else {
                add_filter('body_class', array(&$this, 'add_body_class'));
                add_filter('cuar/core/page/toolbar', array(&$this, 'add_subpages_contextual_toolbar_group'), 100);

                add_filter('the_content', array(&$this, 'define_main_content_filter'), 9998);
                add_filter('cuar/core/the_content', array(&$this, 'get_contextual_toolbar_for_pages'), 80);
                add_filter('cuar/core/the_content', array(&$this, 'wrap_content_into_container'), 100);
                add_filter('cuar/core/the_content', array(&$this, 'wrap_content_into_entry_container'), 1);
            }
        }

        public function set_default_options($defaults)
        {
            $defaults = parent::set_default_options($defaults);

            $defaults [self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT] = true;
            $defaults [self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES] = true;
            $defaults [self::$OPTION_CATEGORY_ARCHIVE_SLUG] = _x('category', 'Private content category archive slug',
                'cuar');
            $defaults [self::$OPTION_DATE_ARCHIVE_SLUG] = _x('archive', 'Private content date archive slug', 'cuar');
            $defaults [self::$OPTION_AUTHOR_ARCHIVE_SLUG] = _x('created-by', 'Private content author archive slug',
                'cuar');
            $defaults [self::$OPTION_UPDATE_CONTENT_SLUG] = _x('update', 'Private content update slug', 'cuar');
            $defaults [self::$OPTION_DELETE_CONTENT_SLUG] = _x('delete', 'Private content delete slug', 'cuar');

            return $defaults;
        }

        public function check_attention_needed()
        {
            parent::check_attention_needed();

            // Check the pages to detect pages without ID
            $needs_attention = false;
            $all_pages = $this->get_customer_area_pages();
            foreach ($all_pages as $slug => $page) {
                $page_id = $this->get_page_id($slug);
                if ($page_id <= 0 || null == get_post($page_id)) {
                    $needs_attention = true;
                    break;
                }
            }
            if ($needs_attention) {
                $this->plugin->set_attention_needed('pages-without-id',
                    __('Some pages of the customer area have not yet been created.', 'cuar'),
                    20);
            } else {
                $this->plugin->clear_attention_needed('pages-without-id');
            }

            // Check that we currently have a navigation menu
            $needs_attention = false;
            $menu_name = 'cuar_main_menu';
            $menu = null;
            if (($locations = get_nav_menu_locations()) && isset($locations[$menu_name])) {
                $menu = wp_get_nav_menu_object($locations[$menu_name]);
            }

            if ($menu == null) {
                $this->plugin->clear_attention_needed('nav-menu-needs-sync');
                $this->plugin->set_attention_needed('missing-nav-menu',
                    __('The navigation menu for the customer area has not been created.', 'cuar'),
                    50);
            } else {
                $this->plugin->clear_attention_needed('missing-nav-menu');
            }
        }

        public function add_status_sections($sections)
        {
            $sections['customer-pages'] = array(
                'id'            => 'customer-pages',
                'label'         => __('Pages', 'cuar'),
                'title'         => __('Pages of the Customer Area', 'cuar'),
                'template_path' => CUAR_INCLUDES_DIR . '/core-addons/customer-pages',
                'linked-checks' => array('pages-without-id', 'missing-nav-menu', 'nav-menu-needs-sync', 'orphan-pages'),
                'actions'       => array(
                    'cuar-create-all-missing-pages' => array(&$this, 'create_all_missing_pages'),
                    'cuar-synchronize-menu'         => array(&$this, 'recreate_default_navigation_menu'),
                    'cuar-clear-sync-nav-warning'   => array(&$this, 'ignore_nav_menu_needs_sync_warning'),
                    'cuar-remove-orphan-pages'      => array(&$this, 'delete_orphan_pages'),
                )
            );

            return $sections;
        }

        public function ignore_nav_menu_needs_sync_warning()
        {
            $this->plugin->clear_attention_needed('nav-menu-needs-sync');
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        /**
         * Get the WordPress page ID corresponding to a given customer area page slug
         *
         * @param string $slug The customer area identifier of the page we are looking for
         *
         * @return mixed|boolean
         */
        public function get_page_id($slug, $settings_array = null)
        {
            if (empty($slug)) {
                return false;
            }

            $option_name = $this->get_page_option_name($slug);

            if ($settings_array == null) {
                $page_id = $this->plugin->get_option($option_name, -1);
            } else {
                $page_id = isset($settings_array[$option_name]) ? $settings_array[$option_name] : -1;
            }

            return $page_id <= 0 ? false : $page_id;
        }

        private function set_page_id($slug, $post_id)
        {
            if (empty($slug)) {
                return;
            }

            $option_name = $this->get_page_option_name($slug);
            $this->plugin->update_option($option_name, $post_id);
        }

        public function get_page_url($slug)
        {
            if (empty($slug)) {
                return false;
            }

            $page_id = $this->plugin->get_option($this->get_page_option_name($slug), -1);

            return $page_id < 0 ? false : get_permalink($page_id);
        }

        public function is_auto_menu_on_single_private_content_pages_enabled()
        {
            return $this->plugin->get_option(self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT);
        }

        public function is_auto_menu_on_customer_area_pages_enabled()
        {
            return $this->plugin->get_option(self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES);
        }

        public function get_category_archive_slug()
        {
            return $this->plugin->get_option(self::$OPTION_CATEGORY_ARCHIVE_SLUG);
        }

        public function get_date_archive_slug()
        {
            return $this->plugin->get_option(self::$OPTION_DATE_ARCHIVE_SLUG);
        }

        public function get_author_archive_slug()
        {
            return $this->plugin->get_option(self::$OPTION_AUTHOR_ARCHIVE_SLUG);
        }

        public function get_update_content_slug()
        {
            return $this->plugin->get_option(self::$OPTION_UPDATE_CONTENT_SLUG);
        }

        public function get_delete_content_slug()
        {
            return $this->plugin->get_option(self::$OPTION_DELETE_CONTENT_SLUG);
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        /**
         * List all the pages expected by Customer Area and its add-ons.
         *
         * @return array see structure for each item in function get_customer_area_page
         */
        public function get_customer_area_pages()
        {
            if ($this->pages == null) {
                $this->pages = apply_filters('cuar/core/page/customer-pages', array());
            }

            return $this->pages;
        }

        /**
         * List all the pages expected by Customer Area and its add-ons.
         *
         * @param string $parent_slug The slug of the parent page
         *
         * @return array see structure for each item in function get_customer_area_page
         */
        public function get_customer_area_child_pages($parent_slug)
        {
            $child_pages = array();

            $pages = $this->get_customer_area_pages();
            foreach ($pages as $slug => $page) {
                $cur_parent_slug = $page->get_parent_slug();
                if ( !isset($cur_parent_slug) && $parent_slug == $cur_parent_slug) {
                    $child_pages[$slug] = $page;
                }
            }

            return $child_pages;
        }

        /**
         * Get the customer area page corresponding to the given slug
         */
        public function get_customer_area_page($slug)
        {
            $customer_area_pages = $this->get_customer_area_pages();

            return isset($customer_area_pages[$slug]) ? $customer_area_pages[$slug] : false;
        }

        /**
         * Get the customer area page corresponding to the given ID
         */
        public function get_customer_area_page_from_id($page_id = 0)
        {
            if ($page_id <= 0) {
                $page_id = get_queried_object_id();
            }

            // We expect a page. You should not make customer area pages in posts or any other custom post type.
            if ( !is_page($page_id)) {
                return false;
            }

            // Test if the current page is one of the root pages
            $customer_area_pages = $this->get_customer_area_pages();
            foreach ($customer_area_pages as $slug => $page) {
                if ($page->get_page_id() == $page_id) {
                    return $page;
                }
            }

            // Not found
            return false;
        }

        /**
         * Are we currently viewing a customer area page?
         * @return boolean
         */
        public function is_customer_area_page($page_id = 0)
        {
            if ($page_id <= 0) {
                $page_id = get_queried_object_id();
            }

            // We expect a page. You should not make customer area pages in posts or any other custom post type.
            if ( !is_page($page_id)) {
                return false;
            }

            // Test if the current page is one of the root pages
            $customer_area_pages = $this->get_customer_area_pages();
            foreach ($customer_area_pages as $slug => $page) {
                if ($page->get_page_id() == $page_id) {
                    return true;
                }
            }

            // Not found
            return false;
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

        /**
         * Do not include the customer area pages in the search results
         *
         * @param WP_Query $query
         */
        public function exclude_pages_from_search_results($query)
        {
            if ( !is_admin() && $query->is_main_query()) {
                if ($query->is_search) {
                    $customer_area_pages = $this->get_customer_area_pages();
                    $pages_to_exclude = array();
                    foreach ($customer_area_pages as $slug => $page) {
                        $pages_to_exclude[] = $page->get_page_id();
                    }
                    $query->set('post__not_in', $pages_to_exclude);
                }
            }
        }

        /**
         * Print the pagination links
         *
         * @param CUAR_AddOn $current_page_addon
         * @param WP_Query   $query
         * @param string     $pagination_base
         * @param int        $current_page
         */
        public function print_pagination($current_page_addon, $query, $pagination_base, $current_page)
        {
            $total = $query->max_num_pages;

            // Don't do anything if only a single page
            if ($total == 1) {
                return;
            }

            // Don't print all pages if there are too many of them
            $skip_middle_pages = ($total > 10);
            $left_spacer_added = false;
            $right_spacer_added = false;

            // Build the array of page links
            $pagination_param_name = _x('page-num', 'pagination_parameter_name (should not be "page")', 'cuar');
            $page_links = array();

            for ($i = 1; $i <= $total; ++$i) {
                if ($skip_middle_pages) {
                    if ($i > 2 && $i < $current_page - 1) {
                        if ( !$left_spacer_added) {
                            $left_spacer_added = true;
                            $page_links[$i] = array(
                                'link'       => false,
                                'is_current' => false
                            );
                        }
                        continue;
                    }
                    if ($i > $current_page + 1 && $i < $total - 1) {
                        if ( !$right_spacer_added) {
                            $right_spacer_added = true;
                            $page_links[$i] = array(
                                'link'       => false,
                                'is_current' => false
                            );
                        }
                        continue;
                    }
                }
                $link = add_query_arg($pagination_param_name, $i, $pagination_base);
                $page_links[$i] = array(
                    'link'       => $link,
                    'is_current' => ($i == $current_page)
                );
            }

            $page_links = apply_filters('cuar/core/page/pagination-items', $page_links, $current_page, $total);

            include($this->plugin->get_template_file_path(
                array(
                    $current_page_addon->get_page_addon_path(),
                    CUAR_INCLUDES_DIR . '/core-addons/customer-pages'
                ),
                $current_page_addon->get_slug() . "-pagination.template.php",
                'templates',
                "pagination.template.php"));
        }

        public function add_body_class($classes)
        {
            if (cuar_is_customer_area_page() || cuar_is_customer_area_private_content()) {
                $classes[] = 'customer-area';
            }

            return $classes;
        }

        /*------- NAV MENU ----------------------------------------------------------------------------------------------*/

        public function filter_nav_menu_items($items, $menu, $args)
        {
            // Don't filter anything on admin side
            if (is_admin()) return $items;

            // Augment the pages list with their page IDs
            $pages = $this->get_customer_area_pages();
            $page_ids = array();
            foreach ($pages as $slug => $page) {
                $page_id = $page->get_page_id();
                if ($page_id > 0) {
                    $page_ids[$page_id] = $page;
                }
            }

            $excluded_item_ids = array();
            $new_items = array();
            foreach ($items as $item) {
                $exclude = false;

                // Only filter the items corresponding to a customer page
                $menu_item_page = isset($page_ids[$item->object_id]) ? $page_ids[$item->object_id] : null;
                if ($menu_item_page != null) {
                    if ( !$menu_item_page->is_accessible_to_current_user()) {
                        $exclude = true;
                    }
                }

                // Filter all items which are marked as private
                if ( !empty($item->classes)) {
                    $is_user_logged_in = is_user_logged_in();
                    if ($is_user_logged_in && false !== array_search("wpca-guest-only", $item->classes)) {
                        $exclude = true;
                    } else if ( !$is_user_logged_in && false !== array_search("wpca-logged-only", $item->classes)) {
                        $exclude = true;
                    }
                }

                // Proceed to exclusion
                if ( !$exclude) {
                    if (in_array($item->menu_item_parent, $excluded_item_ids)) {
                        $item->menu_item_parent = 0;
                    }
                    $new_items[] = $item;
                } else {
                    $excluded_item_ids[] = $item->ID;
                }
            }

            return $new_items;
        }

        public function fix_menu_item_classes($sorted_menu_items)
        {
            // Get menu at our location to bail early if not in the CUAR main nav menu
            $theme_locations = get_nav_menu_locations();
            if ( !isset($theme_locations['cuar_main_menu'])) {
                return;
            }

            $menu = wp_get_nav_menu_object($theme_locations['cuar_main_menu']);
            if ( !isset($menu) || !$menu) {
                return;
            }

            // Augment the pages list with their page IDs
            $pages = $this->get_customer_area_pages();
            $page_ids = array();
            foreach ($pages as $slug => $page) {
                $page_id = $page->get_page_id();
                $page_ids[$page_id] = $page;
            }

            $post_id = get_queried_object_id();
            $post_type = get_post_type($post_id);

            // If we are showing a single post, look for menu items with the same friendly post type
            if (is_singular() && in_array($post_type, $this->plugin->get_content_post_types())) {
                $highlighted_menu_item = null;

                foreach ($sorted_menu_items as $menu_item) {
                    $menus = get_the_terms($menu_item->ID, 'nav_menu');
                    if ($menus != null && $menus != false && !is_wp_error($menus)) {
                        foreach ($menus as $m) {
                            if ($m->term_id != $menu->term_id) {
                                return $sorted_menu_items;
                            }
                        }
                    } else {
                        return $sorted_menu_items;
                    }

                    if ($menu_item->type == 'post_type' && $menu_item->object == 'page') {
                        /** @var CUAR_AbstractPageAddOn $menu_item_page */
                        $menu_item_page = isset($page_ids[$menu_item->object_id]) ? $page_ids[$menu_item->object_id]
                            : null;

                        if ($menu_item_page == null) {
                            continue;
                        }

                        if ($menu_item_page->get_friendly_post_type() == $post_type
                            && in_array($menu_item_page->get_type(), array('list-content', 'redirect'))
                        ) {
                            if ($highlighted_menu_item == null) {
                                $highlighted_menu_item = $menu_item;
                            } else if ($menu_item->menu_item_parent == 0 && $highlighted_menu_item->menu_item_parent != 0) {
                                $highlighted_menu_item = $menu_item;
                            }
                        }
                    }
                }

                if ($highlighted_menu_item != null) {
                    $highlighted_menu_item->classes[] = 'current-menu-item';
                    $highlighted_menu_item->classes[] = 'current_page_item';
                    $highlighted_menu_item->current = true;

                    $this->set_current_menu_item_id($highlighted_menu_item->ID);
                }
            } else {
                foreach ($sorted_menu_items as $menu_item) {
                    $menus = get_the_terms($menu_item->ID, 'nav_menu');
                    if ($menus != null && $menus != false && !is_wp_error($menus)) {
                        foreach ($menus as $m) {
                            if ($m->term_id != $menu->term_id) {
                                return $sorted_menu_items;
                            }
                        }
                    } else {
                        return $sorted_menu_items;
                    }

                    if ($menu_item->current) {
                        $this->set_current_menu_item_id($menu_item->ID);
                        break;
                    }
                }
            }

            return $sorted_menu_items;
        }

        public function exclude_pages_from_wp_page_menu($args)
        {
            $new_args = $args;

            if ( !isset($new_args['exclude'])) {
                $new_args['exclude'] = '';
            }
            if ( !empty($new_args['exclude'])) {
                $new_args['exclude'] .= ',';
            }

            $customer_area_pages = $this->get_customer_area_pages();
            $pages_to_exclude = array();
            foreach ($customer_area_pages as $slug => $page) {
                $exclude = false;

                if ( !is_user_logged_in() && $page->requires_login()) {
                    $exclude = true;
                } else if (is_user_logged_in() && $page->hide_if_logged_in()) {
                    $exclude = true;
                } else if ($page->hide_in_menu()) {
                    $exclude = true;
                }

                if ( !$page->is_accessible_to_current_user()) {
                    $exclude = true;
                }

                if ($page->always_include_in_menu()) {
                    $exclude = false;
                }

                if ($exclude) {
                    $pages_to_exclude[] = $page->get_page_id();
                }
            }

            if ( !empty($pages_to_exclude)) {
                $new_args['exclude'] .= implode(',', $pages_to_exclude);
            }

            return $new_args;
        }

        public function recreate_default_navigation_menu()
        {
            $menu_name = 'cuar_main_menu';
            $menu = null;
            $locations = get_nav_menu_locations();

            if (isset($locations[$menu_name]) && $locations[$menu_name] > 0) {
                $menu = wp_get_nav_menu_object($locations[$menu_name]);
                if ($menu != false) {
                    $menu_items = wp_get_nav_menu_items($menu->term_id);

                    // Delete existing menu items
                    foreach ($menu_items as $item) {
                        wp_delete_post($item->ID, true);
                    }
                }
            }

            // Create new menu if not existing already
            if ($menu == null) {
                wp_delete_nav_menu(_x('wp-customer-area-menu',
                    'Localised slug for the main navigation menu (small caps version of the "WP Customer Area Menu" translation)',
                    'cuar'));
                $menu = wp_create_nav_menu(__('WP Customer Area Menu', 'cuar'));
            }

            if (is_wp_error($menu)) {
                $this->plugin->add_admin_notice(sprintf(__('Could not create the menu. %s', 'cuar'),
                    $menu->get_error_message()));

                return;
            } else {
                $menu = wp_get_nav_menu_object($menu);
            }

            // Place the menu at the right location
            $locations[$menu_name] = $menu->term_id;
            set_theme_mod('nav_menu_locations', $locations);

            // Now add all default menu items
            $pages = $this->get_customer_area_pages();
            $menu_items = array();
            foreach ($pages as $slug => $page) {
                // Ignore home on purpose
                if ($slug == 'customer-home') {
                    continue;
                }

                // Exclude pages that are made to be seen when not logged-in
                $exclude = false;
                if ($page->hide_if_logged_in() || $page->hide_in_menu()) {
                    $exclude = true;
                }

                if ($page->always_include_in_menu()) {
                    $exclude = false;
                }

                if ($exclude) {
                    continue;
                }

                $args = array(
                    'menu-item-object-id' => $page->get_page_id(),
                    'menu-item-object'    => 'page',
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                );


                // Find parent if any
                $parent_slug = $page->get_parent_slug();
                if ( !empty($parent_slug) && isset($menu_items[$parent_slug])) {
                    $args['menu-item-parent-id'] = $menu_items[$parent_slug];
                }

                $item_id = wp_update_nav_menu_item($menu->term_id, 0, $args);
                if ( !is_wp_error($item_id)) {
                    // Remember the slug for parent ownership
                    $menu_items[$slug] = $item_id;
                }
            }

            $this->plugin->clear_attention_needed('nav-menu-needs-sync');
            $this->plugin->add_admin_notice(sprintf(__('The menu has been created: <a href="%s">view menu</a>', 'cuar'),
                admin_url('nav-menus.php?menu=') . $menu->term_id), 'updated');
        }

        /**
         * Get the main navigation menu as HTML
         *
         * @param bool $echo
         *
         * @return string
         */
        public function get_main_navigation_menu($echo = false)
        {
            $out = '';

            if ( !is_user_logged_in()) {
                return $out;
            }

            /** @noinspection PhpUnusedLocalVariableInspection */
            $nav_menu_args = apply_filters('cuar/core/page/nav-menu-args', array(
                'theme_location' => 'cuar_main_menu',
                'menu_class'     => 'cuar-menu'
            ));

            ob_start();

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/customer-pages',
                'customer-pages-navigation-menu.template.php',
                'templates'));

            $out = ob_get_contents();
            ob_end_clean();

            if ($echo) {
                echo $out;
            }

            return $out;
        }

        /**
         * Output the customer area navigation menu
         *
         * @param string $content
         *
         * @return string
         */
        public function get_main_menu_for_single_private_content($content)
        {
            // Only if the theme does not already support this
            if (current_theme_supports('customer-area.navigation-menu')) {
                return $content;
            }

            // Only on single private content pages
            $post_id = get_the_ID();
            if ( !cuar_is_customer_area_private_content($post_id)) return $content;

            return $this->get_main_navigation_menu() . $content;
        }

        /**
         * Output the customer area navigation menu
         *
         * @param string $content
         *
         * @return string
         */
        public function get_main_menu_for_customer_area_pages($content)
        {
            // Only if the theme does not already support this
            if (current_theme_supports('customer-area.navigation-menu')) {
                return $content;
            }

            $post_id = get_the_ID();
            if ( !cuar_is_customer_area_page($post_id)) {
                return $content;
            }

            $content = $this->get_main_navigation_menu() . $content;

            return $content;
        }

        /**
         * Get the contextual toolbar as HTML
         *
         * @param bool $echo
         *
         * @return string
         */
        public function get_contextual_toolbar($echo = false)
        {
            $out = '';

            if ( !is_user_logged_in()) {
                return $out;
            }

            $post_id = get_the_ID();
            if ( !cuar_is_customer_area_page($post_id) && !cuar_is_customer_area_private_content($post_id)) {
                return $out;
            }

            /** @noinspection PhpUnusedLocalVariableInspection */
            $toolbar_groups = apply_filters('cuar/core/page/toolbar', array());
            if ( !empty($toolbar_groups)) {
                ob_start();

                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/customer-pages',
                    'customer-pages-contextual-toolbar.template.php',
                    'templates'));

                $out = ob_get_contents();
                ob_end_clean();
            } else {
                $out = '';
            }

            if ($echo) {
                echo $out;
            }

            return $out;
        }

        /**
         * Main WPCA content filter
         *
         * Never use the default WP filter called `the_content`,
         * use `cuar/core/the_content` instead !
         *
         * @param $content
         *
         * @return mixed|void
         */
        public function define_main_content_filter($content)
        {
            $post_id = get_the_ID();
            if ( !$this->is_customer_area_page($post_id)
                && !cuar_is_customer_area_private_content($post_id)
            ) {
                return $content;
            }

            return apply_filters('cuar/core/the_content', $content);
        }

        /**
         * Automatically print the contextual toolbar if the theme does not support it natively
         *
         * @param string $content
         *
         * @return string
         */
        public function get_contextual_toolbar_for_pages($content)
        {
            // Only if the theme does not already support this
            if (current_theme_supports('customer-area.contextual-toolbar')) {
                return $content;
            }

            $out = $this->get_contextual_toolbar();

            return $out . $content;
        }

        /**
         * Add to the toolbar a group of buttons that allow to click the child pages even on mobile
         *
         * @param array $groups The toolbar groups
         *
         * @return array
         */
        public function add_subpages_contextual_toolbar_group($groups)
        {
            $theme_locations = get_nav_menu_locations();
            if ( !isset($theme_locations['cuar_main_menu'])) {
                return $groups;
            }

            $menu_items = wp_get_nav_menu_items($theme_locations['cuar_main_menu']);
            if (empty($menu_items)) {
                return $groups;
            }

            $current_item_id = $this->get_current_menu_item_id();

            $group_items = array();
            foreach ($menu_items as $item) {
                if ($item->menu_item_parent == $current_item_id) {
                    $group_items[] = array(
                        'title'       => $item->title,
                        'url'         => $item->url,
                        'tooltip'     => '',
                        'extra_class' => ''
                    );
                }
            }

            if ( !empty($group_items)) {
                $groups['subpages'] = array(
                    'items'       => $group_items,
                    'extra_class' => ''
                );
            }

            return $groups;
        }

        /**
         * Wrap the WP Customer Area generated content into a container, always
         *
         * @param string $content
         *
         * @return string
         */
        public function wrap_content_into_container($content)
        {
            return '<div id="cuar-js-content-container" class="cuar-content-container cuar-css-wrapper">' . $content . '</div>';
        }

        /**
         * Wrap the WP Customer Area generated content into an entry container, for singular view content
         *
         * @param string $content
         *
         * @return string
         */
        public function wrap_content_into_entry_container($content)
        {
            $post_id = get_the_ID();
            if ( !cuar_is_customer_area_private_content($post_id)) return $content;

            return '<div class="cuar-single-entry">' . $content . '</div>';
        }

        public function get_current_menu_item_id()
        {
            return $this->current_menu_item_id;
        }

        protected function set_current_menu_item_id($item_id)
        {
            $this->current_menu_item_id = $item_id;
        }

        protected $current_menu_item_id = null;

        /*------- SETTINGS ----------------------------------------------------------------------------------------------*/

        /**
         * Get the WordPress page ID corresponding to a given customer area page slug
         *
         * @param string $slug The customer area identifier of the page we are looking for
         *
         * @return mixed|boolean
         */
        public function get_page_option_name($slug)
        {
            return self::$OPTION_CUSTOMER_PAGE . $slug;
        }

        public function add_settings_tab($tabs)
        {
            $tabs['cuar_customer_pages'] = __('Site Pages', 'cuar');

            return $tabs;
        }

        public function print_pages_settings($cuar_settings, $options_group)
        {
            add_settings_section(
                'cuar_core_pages',
                __('Customer Pages', 'cuar'),
                array(&$this, 'print_page_settings_section_info'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG
            );

            $customer_area_pages = $this->get_customer_area_pages();
            foreach ($customer_area_pages as $slug => $page) {
                $hint = $page->get_hint();

                add_settings_field(
                    $this->get_page_option_name($page->get_slug()),
                    $page->get_label(),
                    array(&$cuar_settings, 'print_post_select_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    'cuar_core_pages',
                    array(
                        'option_id'          => $this->get_page_option_name($page->get_slug()),
                        'post_type'          => 'page',
                        'show_create_button' => true,
                        'after'              => !empty($hint) ? '<p class="description">' . $hint . '</p>' : ''
                    )
                );
            }

            add_settings_field(
                'cuar_recreate_all_pages',
                __('Reset', 'cuar'),
                array(&$cuar_settings, 'print_submit_button'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_pages',
                array(
                    'option_id'       => 'cuar_recreate_all_pages',
                    'label'           => __('Reset all pages &raquo;', 'cuar'),
                    'nonce_action'    => 'recreate_all_pages',
                    'nonce_name'      => 'cuar_recreate_all_pages_nonce',
                    'before'          => '<p>' . __('Delete all existing pages and recreate them.', 'cuar') . '</p>',
                    'confirm_message' => __('Are you sure that you want to delete all existing pages and recreate them (this operation cannot be undone)?',
                        'cuar')
                )
            );
        }

        public function print_frontend_settings($cuar_settings, $options_group)
        {
            add_settings_section(
                'cuar_core_nav_menu',
                __('Main Navigation Menu', 'cuar'),
                array(&$this, 'print_nav_menu_settings_section_info'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG
            );

            if ( !current_theme_supports('customer-area.navigation-menu')) {
                add_settings_field(
                    self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES,
                    __('WP Customer Area pages', 'cuar'),
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    'cuar_core_nav_menu',
                    array(
                        'option_id' => self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES,
                        'type'      => 'checkbox',
                        'after'     =>
                            __('Automatically print the Customer Area navigation menu on the Customer Area pages.',
                                'cuar')
                            . '<p class="description">'
                            . __('By checking this box, the menu will automatically be shown automatically on the Customer Area pages (the ones defined in the tab named "Site Pages"). '
                                . 'It may however not appear at the place you would want it. If that is the case, you can refer to our documentation to see how to edit your theme.',
                                'cuar')
                            . '</p>'
                    )
                );

                add_settings_field(
                    self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT,
                    __('Private content single pages', 'cuar'),
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    'cuar_core_nav_menu',
                    array(
                        'option_id' => self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT,
                        'type'      => 'checkbox',
                        'after'     =>
                            __('Automatically print the Customer Area navigation menu on private content single pages.',
                                'cuar')
                            . '<p class="description">'
                            . __('By checking this box, the menu will automatically be shown automatically on the pages displaying a single private content (a private page or a private file for example). '
                                . 'It may however not appear at the place you would want it. If that is the case, you can refer to our documentation to see how to edit your theme.',
                                'cuar')
                            . '</p>'
                    )
                );
            }

            add_settings_field(
                'cuar_recreate_navigation_menu',
                __('Reset', 'cuar'),
                array(&$cuar_settings, 'print_submit_button'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_nav_menu',
                array(
                    'option_id'       => 'cuar_recreate_navigation_menu',
                    'label'           => __('Recreate menu', 'cuar'),
                    'nonce_action'    => 'recreate_navigation_menu',
                    'nonce_name'      => 'cuar_recreate_navigation_menu_nonce',
                    'before'          => '<p>' . __('Delete and recreate the main navigation menu.', 'cuar') . '</p>',
                    'confirm_message' => __('Are you sure that you want to recreate the main navigation menu (this operation cannot be undone)?',
                        'cuar')
                )
            );

            add_settings_section(
                'cuar_core_permalinks',
                __('Permalinks', 'cuar'),
                array(&$this, 'print_empty_settings_section_info'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG
            );

            add_settings_field(
                self::$OPTION_CATEGORY_ARCHIVE_SLUG,
                __('Category Archive', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_permalinks',
                array(
                    'option_id' => self::$OPTION_CATEGORY_ARCHIVE_SLUG,
                    'type'      => 'text',
                    'is_large'  => false,
                    'after'     => '<p class="description">'
                        . __('Slug that is used in the URL for category archives of private content. For example, the list of files in the "my-awesome-category" category would look '
                            . 'like:<br/>http://example.com/customer-area/files/<b>my-slug</b>/my-awesome-category',
                            'cuar')
                        . '</p>'
                )
            );

            add_settings_field(
                self::$OPTION_DATE_ARCHIVE_SLUG,
                __('Date Archive', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_permalinks',
                array(
                    'option_id' => self::$OPTION_DATE_ARCHIVE_SLUG,
                    'type'      => 'text',
                    'is_large'  => false,
                    'after'     => '<p class="description">'
                        . __('Slug that is used in the URL for date archives of private content. For example, the list of files for 2014 would look '
                            . 'like:<br/>http://example.com/customer-area/files/<b>my-slug</b>/2014', 'cuar')
                        . '</p>'
                )
            );

            add_settings_field(
                self::$OPTION_AUTHOR_ARCHIVE_SLUG,
                __('Author Archive', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_permalinks',
                array(
                    'option_id' => self::$OPTION_AUTHOR_ARCHIVE_SLUG,
                    'type'      => 'text',
                    'is_large'  => false,
                    'after'     => '<p class="description">'
                        . __('Slug that is used in the URL for author archives of private content. For example, the list of files created by user with ID 2 would look '
                            . 'like:<br/>http://example.com/customer-area/files/<b>my-slug</b>/2', 'cuar')
                        . '</p>'
                )
            );

            add_settings_field(
                self::$OPTION_UPDATE_CONTENT_SLUG,
                __('Update Content', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_permalinks',
                array(
                    'option_id' => self::$OPTION_UPDATE_CONTENT_SLUG,
                    'type'      => 'text',
                    'is_large'  => false,
                    'after'     => '<p class="description">'
                        . __('Slug that is used in the URL to update existing private content', 'cuar')
                        . '</p>'
                )
            );

            add_settings_field(
                self::$OPTION_DELETE_CONTENT_SLUG,
                __('Delete Content', 'cuar'),
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_core_permalinks',
                array(
                    'option_id' => self::$OPTION_DELETE_CONTENT_SLUG,
                    'type'      => 'text',
                    'is_large'  => false,
                    'after'     => '<p class="description">'
                        . __('Slug that is used in the URL to delete existing private content', 'cuar')
                        . '</p>'
                )
            );

        }

        public function print_empty_settings_section_info()
        {
        }

        public function print_nav_menu_settings_section_info()
        {
            echo '<p class="cuar-section-info">'
                . __('Since version 4.0.0, Customer Area handles navigation using menus.', 'cuar');
            echo ' '
                . sprintf(__('You can customize the Customer Area menu in the <a href="%1$s">Appearance &raquo; Menus</a> panel. '
                    . 'If you do not define any custom menu there, Customer Area will generate a default menu for you with all the pages '
                    . 'you have set below.', 'cuar'),
                    admin_url('nav-menus.php'))
                . '</p>';
        }

        public function print_page_settings_section_info()
        {
            echo '<p class="cuar-section-info">'
                . __('Since version 4.0.0, Customer Area is using various pages to show the content for your customers. Create those pages from here or simply indicate existing ones. ',
                    'cuar')
                . '</p>';
        }

        public function validate_frontend_settings($validated, $cuar_settings, $input)
        {
            if ( !current_theme_supports('customer-area.navigation-menu')) {
                $cuar_settings->validate_boolean($input, $validated, self::$OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT);
                $cuar_settings->validate_boolean($input, $validated, self::$OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES);
            }

            $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_CATEGORY_ARCHIVE_SLUG);
            $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_DATE_ARCHIVE_SLUG);
            $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_AUTHOR_ARCHIVE_SLUG);
            $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_UPDATE_CONTENT_SLUG);
            $cuar_settings->validate_not_empty($input, $validated, self::$OPTION_DELETE_CONTENT_SLUG);

            if (isset($_POST['cuar_recreate_navigation_menu'])
                && check_admin_referer('recreate_navigation_menu', 'cuar_recreate_navigation_menu_nonce')
            ) {
                $this->recreate_default_navigation_menu();
            }

            $cuar_settings->flush_rewrite_rules();

            return $validated;
        }

        public function validate_pages_settings($validated, $cuar_settings, $input)
        {
            $customer_area_pages = $this->get_customer_area_pages();
            uasort($customer_area_pages, 'cuar_sort_pages_by_priority');

            $has_created_pages = false;

            // If we are requested to create the page, do it now
            foreach ($customer_area_pages as $slug => $page) {
                $option_id = $this->get_page_option_name($page->get_slug());
                $create_button_name = $cuar_settings->get_submit_create_post_button_name($option_id);

                if ((isset($_POST['cuar_recreate_all_pages'])
                        && check_admin_referer('recreate_all_pages', 'cuar_recreate_all_pages_nonce'))
                    || isset($_POST[$create_button_name])
                ) {

                    $existing_page_id = $page->get_page_id();

                    if ($existing_page_id > 0) {
                        wp_delete_post($existing_page_id, true);
                    }

                    $page_id = apply_filters('cuar/core/page/on-page-created?slug=' . $page->get_slug(), 0, $input);
                    if ($page_id > 0) {
                        $input[$option_id] = $page_id;
                        $has_created_pages = true;
                    }
                }

                $cuar_settings->validate_post_id($input, $validated, $option_id);
            }

            $cuar_settings->flush_rewrite_rules();

            if ($has_created_pages) {
                $this->plugin->set_attention_needed('nav-menu-needs-sync',
                    __('Some pages of the customer area have been created or deleted. The customer area navigation menu needs to be updated.',
                        'cuar'), 30);
            }

            return $validated;
        }

        public function create_all_missing_pages()
        {
            $customer_area_pages = $this->get_customer_area_pages();
            uasort($customer_area_pages, 'cuar_sort_pages_by_priority');

            $created_pages = array();

            foreach ($customer_area_pages as $slug => $page) {
                $existing_page_id = $page->get_page_id();

                if ($existing_page_id > 0 && get_post($existing_page_id) != null) {
                    continue;
                }

                $page_id = apply_filters('cuar/core/page/on-page-created?slug=' . $page->get_slug(), 0);

                if ($page_id > 0) {
                    $this->set_page_id($page->get_slug(), $page_id);
                    $created_pages[] = $page->get_title();
                }
            }

            if ( !empty($created_pages)) {
                $this->plugin->add_admin_notice(sprintf(__('The following pages have been created: %s', 'cuar'),
                    implode(', ', $created_pages)), 'updated');

                $this->plugin->set_attention_needed('nav-menu-needs-sync',
                    __('Some pages of the customer area have been created or deleted. The customer area navigation menu needs to be updated.',
                        'cuar'), 30);
            } else {
                $this->plugin->add_admin_notice(__('There was no missing page that could be created.', 'cuar'),
                    'error');
            }
        }

        /**
         * Try to detect pages that contain customer area shortcodes but which are not set in our settings
         */
        public function get_potential_orphan_pages()
        {
            $all_pages = get_pages(array('numposts' => -1));
            $cp_pages = $this->get_customer_area_pages();

            $orphans = array();

            foreach ($all_pages as $suspect) {
                $has_known_id = false;
                $contains_shortcode = false;

                foreach ($cp_pages as $verified) {
                    // If we have a known ID, clear the suspect
                    if ($suspect->ID == $verified->get_page_id()) {
                        $has_known_id = true;
                        break;
                    }

                    // If we contain the shortcode, that's potentially bad news
                    if (has_shortcode($suspect->post_content, $verified->get_page_shortcode())) {
                        $contains_shortcode = true;
                    }
                }

                if ( !$has_known_id && $contains_shortcode) {
                    $orphans[] = $suspect;
                }
            }

            if ( !empty($orphans)) {
                $this->plugin->set_attention_needed('orphan-pages',
                    __('Some pages in your site seem to contain Customer Area shortcodes but are not registered in the Customer Area pages settings.',
                        'cuar'), 20);
            } else {
                $this->plugin->clear_attention_needed('orphan-pages');
            }

            return $orphans;
        }

        public function delete_orphan_pages()
        {
            $orphans = $this->get_potential_orphan_pages();

            $delete_count = 0;
            foreach ($orphans as $o) {
                if (false != wp_delete_post($o->ID, true)) {
                    ++$delete_count;
                }
            }

            $this->plugin->add_admin_notice(sprintf(_n('%s page has been deleted', '%s pages have been deleted', 'cuar',
                'cuar'), $delete_count), 'updated');

            $this->plugin->clear_attention_needed('orphan-pages');
            $this->plugin->set_attention_needed('nav-menu-needs-sync',
                __('Some pages of the customer area have been created or deleted. The customer area navigation menu needs to be updated.',
                    'cuar'), 30);

            flush_rewrite_rules();
        }

        // Options
        public static $OPTION_CUSTOMER_PAGE = 'customer_page_';
        public static $OPTION_AUTO_MENU_ON_SINGLE_PRIVATE_CONTENT = 'customer_page_auto_menu_on_single_content';
        public static $OPTION_AUTO_MENU_ON_CUSTOMER_AREA_PAGES = 'customer_page_auto_menu_on_pages';
        public static $OPTION_CATEGORY_ARCHIVE_SLUG = 'cuar_permalink_category_archive_slug';
        public static $OPTION_DATE_ARCHIVE_SLUG = 'cuar_permalink_date_archive_slug';
        public static $OPTION_AUTHOR_ARCHIVE_SLUG = 'cuar_permalink_author_archive_slug';
        public static $OPTION_UPDATE_CONTENT_SLUG = 'cuar_permalink_update_content_slug';
        public static $OPTION_DELETE_CONTENT_SLUG = 'cuar_permalink_delete_content_slug';

        /** @var array */
        private $pages = null;
    }

// Make sure the addon is loaded
    new CUAR_CustomerPagesAddOn();

    function cuar_sort_pages_by_priority($a, $b)
    {
        return $b->get_priority() < $a->get_priority();
    }

endif; // if (!class_exists('CUAR_CustomerPagesAddOn')) :/ if (!class_exists('CUAR_CustomerPagesAddOn')) :/ if (!class_exists('CUAR_CustomerPagesAddOn')) :/ if (!class_exists('CUAR_CustomerPagesAddOn')) :

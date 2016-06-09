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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-page.class.php');

if ( !class_exists('CUAR_AbstractContainerPageAddOn')) :

    /**
     * The base class for addons that should render a page containing content from custom private posts
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_AbstractContainerPageAddOn extends CUAR_AbstractPageAddOn
    {

        public function __construct($addon_id = null, $min_cuar_version = null)
        {
            parent::__construct($addon_id, $min_cuar_version);
        }

        protected function set_page_parameters($priority, $description)
        {
            parent::set_page_parameters($priority, $description);

            if ( !isset($this->page_description['friendly_post_type']))
            {
                $this->page_description['friendly_post_type'] = null;
            }

            if ( !isset($this->page_description['friendly_taxonomies']))
            {
                $this->page_description['friendly_taxonomies'] = null;
            }
        }

        public function get_type()
        {
            return 'list-container';
        }

        public function get_friendly_post_type()
        {
            return $this->page_description['friendly_post_type'];
        }

        public function get_friendly_taxonomies()
        {
            return $this->page_description['friendly_taxonomies'];
        }

        protected abstract function get_taxonomy_archive_page_subtitle($taxonomy, $term);

        protected abstract function get_default_page_subtitle();

        protected abstract function get_default_dashboard_block_title();

        protected abstract function get_container_owner_type();

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

            $slug = $this->get_slug();

            $defaults[$slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER] = true;
            $defaults[$slug . self::$OPTION_SHOW_IN_DASHBOARD] = true;
            $defaults[$slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD] = 5;
            $defaults[$slug . self::$OPTION_MAX_ITEM_NUMBER_ON_LISTING] = 10;

            return $defaults;
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function is_show_in_dashboard_enabled()
        {
            $value = $this->plugin->get_option($this->get_slug() . self::$OPTION_SHOW_IN_DASHBOARD);

            return $value==true ? true : $value;
        }

        public function is_show_in_single_post_footer_enabled()
        {
            $value = $this->plugin->get_option($this->get_slug() . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER);

            return $value==true ? true : $value;
        }

        public function get_max_item_number_on_dashboard()
        {
            return $this->plugin->get_option($this->get_slug() . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD, 5);
        }

        public function get_taxonomy_archive_slug($tax)
        {
            return $this->plugin->get_option($this->get_slug() . self::$OPTION_TAXONOMY_SLUG . $tax);
        }

        public function get_max_item_number_in_listing()
        {
            return $this->plugin->get_option($this->get_slug() . self::$OPTION_MAX_ITEM_NUMBER_ON_LISTING, 10);
        }

        /*------- ARCHIVES ----------------------------------------------------------------------------------------------*/

        /**
         * The path of the page (slug + parent slugs)
         */
        protected function get_full_page_path($page_id = 0)
        {
            if ($page_id == 0)
            {
                /** @var CUAR_CustomerPagesAddOn $cp_addon */
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $page_id = $cp_addon->get_page_id($this->get_slug());
            }

            $page_url = get_permalink($page_id);
            $page_url = str_replace('http://', '', $page_url);
            $page_url = str_replace('https://', '', $page_url);

            $home_url = trailingslashit(home_url());
            $home_url = str_replace('http://', '', $home_url);
            $home_url = str_replace('https://', '', $home_url);

            return untrailingslashit(str_replace($home_url, '', $page_url));
        }

        /**
         * Allow this page to get URLs for content archives
         */
        protected function enable_container_archives_permalinks()
        {
            add_filter('rewrite_rules_array', array(&$this, 'insert_archive_rewrite_rules'));
            add_filter('query_vars', array(&$this, 'insert_archive_query_vars'));
        }

        /**
         * Add rewrite rules for the archive subpages.
         *
         * @param unknown $rules
         *
         * @return array
         */
        public function insert_archive_rewrite_rules($rules)
        {
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $page_id = $cp_addon->get_page_id($this->get_slug());
            $page_slug = untrailingslashit(str_replace(trailingslashit(home_url()), '', get_permalink($page_id)));

            $newrules = array();

            // Category archives
            $friendly_tax = $this->get_friendly_taxonomies();
            if ($friendly_tax != null)
            {
                foreach ($friendly_tax as $tax)
                {
                    $rewrite_rule = 'index.php?page_id=' . $page_id . '&' . $tax . '=$matches[1]';
                    $rewrite_regex = $page_slug . '/' . $this->get_taxonomy_archive_slug($tax) . '/([^/]+)/?$';
                    $newrules[$rewrite_regex] = $rewrite_rule;
                }
            }

            return $newrules + $rules;
        }

        /**
         * Add query variables for the archive subpages.
         *
         * @param unknown $vars
         *
         * @return array
         */
        public function insert_archive_query_vars($vars)
        {
            $friendly_tax = $this->get_friendly_taxonomies();
            if ($friendly_tax != null)
            {
                foreach ($friendly_tax as $tax)
                {
                    array_push($vars, $tax);
                }
            }

            return $vars;
        }

        /**
         * Get the URL for the archive corresponding to a given category.
         *
         * @param unknown $term
         *
         * @return string|unknown
         */
        public function get_taxonomy_term_archive_url($tax, $term)
        {
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $page_id = $cp_addon->get_page_id($this->get_slug());
            if ($page_id == false) return '';

            $url = trailingslashit(get_permalink($page_id));
            $url .= $this->get_taxonomy_archive_slug($tax) . '/';
            $url .= $term->slug;

            return $url;
        }

        public function add_listing_contextual_toolbar_group($groups)
        {
            $current_page_id = get_queried_object_id();
            if ($current_page_id!=$this->get_page_id()) return $groups;

            ob_start();
            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-classes',
                array(
                    'collections-button-group-' . $this->get_slug() . '.template.php',
                    'collections-button-group.template.php'
                ),
                'templates'
            ));
            $group_html = ob_get_contents();
            ob_end_clean();

            $groups['collection-switcher'] = array(
                'type' => 'raw',
                'html' => $group_html
            );

            return $groups;
        }

        /*------- SINGLE PRIVATE CONTENT --------------------------------------------------------------------------------*/

        /**
         * Allow this page to get URLs for single private content pages
         */
        protected function enable_single_container_permalinks()
        {
            if ( !isset($this->page_description['friendly_post_type']))
            {
                warn('Cannot enable single content permalinks for page without declaring its friendly_post_type');

                return;
            }

            add_filter('rewrite_rules_array', array(&$this, 'insert_single_post_rewrite_rules'));
            add_filter('query_vars', array(&$this, 'insert_single_post_query_vars'));
            add_filter('post_type_link', array(&$this, 'filter_single_container_link'), 10, 2);
        }

        /**
         * Add rewrite rules for single private content pages.
         *
         * @param unknown $rules
         *
         * @return array
         */
        public function insert_single_post_rewrite_rules($rules)
        {
            $page_slug = $this->get_full_page_path();

            $newrules = array();

            // Single post rule
            $rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type'] . '=$matches[4]';
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/?$';
            $newrules[$rewrite_regex] = $rewrite_rule;

            // Single post rule with action
            $rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type']
                . '=$matches[4]&cuar_action=$matches[5]';
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/([^/]+)/?$';
            $newrules[$rewrite_regex] = $rewrite_rule;

            return $newrules + $rules;
        }

        /**
         * Add query variables for single private content pages.
         *
         * @param unknown $vars
         *
         * @return array
         */
        public function insert_single_post_query_vars($vars)
        {
            array_push($vars, 'cuar_action');
            array_push($vars, $this->page_description['friendly_post_type']);

            return $vars;
        }

        /**
         * Output the correct permalink for our single private content pages
         *
         * @param unknown $permalink
         * @param unknown $post
         *
         * @return Ambigous <string, mixed>
         */
        function filter_single_container_link($permalink, $post)
        {
            $post_type = $this->page_description['friendly_post_type'];

            if ($post_type == $post->post_type
                && '' != $permalink
                && !in_array($post->post_status, array('draft', 'pending', 'auto-draft'))
            )
            {
                $permalink = $this->get_single_container_url($post);
            }

            return $permalink;
        }

        /**
         * Get the URL to view a given post
         *
         * @param unknown $post_id
         */
        public function get_single_container_url($post)
        {
            $cp_addon = $this->plugin->get_addon('customer-pages');
            $page_id = $cp_addon->get_page_id($this->get_slug());
            if ($page_id == false) return '';

            $post = get_post($post);

            $date = mysql2date('Y m d', $post->post_date);
            $date = explode(" ", $date);

            $url = trailingslashit(get_permalink($page_id));
            $url .= sprintf('%04d/%02d/%02d/%s', $date[0], $date[1], $date[2], $post->post_name);

            if ( !empty($action))
            {
                $url .= '/' . $action;
            }

            return $url;
        }

        public function get_single_container_action_url($post, $action = '')
        {
            $url = $this->get_single_container_url($post);

            if ( !empty($action))
            {
                $url .= '/' . $action;
            }

            return $url;
        }

        /**
         * Disable the navigation on the single page templates for private files
         */
        // TODO improve this by getting the proper previous/next file for the same owner
        public function disable_single_post_navigation($where, $in_same_cat, $excluded_categories)
        {
            if (get_post_type() == $this->get_friendly_post_type()) return "WHERE 1=0";

            return $where;
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        public function print_page_content($args = array(), $shortcode_content = '')
        {
            $co_addon = $this->plugin->get_addon('container-owner');
            $current_user_id = get_current_user_id();
            $page_slug = $this->get_slug();

            // Display mode
            $display_mode = 'default';

            // Texts
            $page_subtitle = '';

            // Paging
            $pagination_param = _x('page-num', 'pagination_parameter_name (should not be "page")', 'cuar');
            $current_page = isset($_GET[$pagination_param]) ? $_GET[$pagination_param] : 1;
            $posts_per_page = $this->get_max_item_number_in_listing();
            $pagination_base = '';

            // See if we are in the case of a taxonomy archive
            $friendly_tax = $this->get_friendly_taxonomies();
            if ($friendly_tax != null)
            {
                foreach ($friendly_tax as $tax)
                {
                    $term = get_query_var($tax);

                    if ( !empty($term))
                    {
                        $taxonomy = $tax;
                        $term = get_term_by('slug', $term, $tax);

                        $display_mode = 'taxonomy_archive';
                        $page_subtitle = $this->get_taxonomy_archive_page_subtitle($tax, $term);

                        $args = array(
                            'post_type'      => $this->get_friendly_post_type(),
                            'posts_per_page' => $posts_per_page,
                            'paged'          => $current_page,
                            'orderby'        => 'title',
                            'order'          => 'ASC',
                            'meta_query'     => $co_addon->get_meta_query_containers_owned_by($current_user_id),
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => $tax,
                                    'field'    => 'slug',
                                    'terms'    => $term->slug
                                )
                            )
                        );

                        $pagination_base = $this->get_taxonomy_term_archive_url($tax, $term);
                        break;
                    }
                }
            }

            // Handle the default case if we have not changed the display mode
            if ($display_mode == 'default')
            {
                // Default view
                $args = array(
                    'post_type'      => $this->get_friendly_post_type(),
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'meta_query'     => $co_addon->get_meta_query_containers_owned_by($current_user_id)
                );

                $page_subtitle = $this->get_default_page_subtitle();
                $pagination_base = $this->get_page_url();
            }

            $args = apply_filters('cuar/core/page/query-args?slug=' . $page_slug, $args);
            $args = apply_filters('cuar/core/page/query-args?slug=' . $page_slug . '&display-mode=' . $display_mode, $args);
            $content_query = new WP_Query($args);

            $page_subtitle = apply_filters('cuar/core/page/subtitle?slug=' . $page_slug, $page_subtitle);
            $page_subtitle = apply_filters('cuar/core/page/subtitle?slug=' . $page_slug . '&display-mode=' . $display_mode, $page_subtitle);

            if ($content_query->have_posts())
            {
                $item_template = $this->plugin->get_template_file_path(
                    array(
                        $this->get_page_addon_path(),
                        CUAR_INCLUDES_DIR . '/core-classes'
                    ),
                    array(
                        $this->get_slug() . "-content-item-{$display_mode}.template.php",
                        $this->get_slug() . "-content-item.template.php",
                        "content-page-content-item-{$display_mode}.template.php"
                    ),
                    'templates',
                    "content-page-content-item.template.php");

                include($this->plugin->get_template_file_path(
                    array(
                        $this->get_page_addon_path(),
                        CUAR_INCLUDES_DIR . '/core-classes'
                    ),
                    array(
                        $this->get_slug() . "-content-{$display_mode}.template.php",
                        $this->get_slug() . "-content.template.php",
                        "content-page-content-{$display_mode}.template.php"
                    ),
                    'templates',
                    "content-page-content.template.php"));

                // Include paging navigation if necessary
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $cp_addon->print_pagination($this, $content_query, $pagination_base, $current_page);

                wp_reset_query();
            }
            else
            {
                include($this->plugin->get_template_file_path(
                    array(
                        $this->get_page_addon_path(),
                        CUAR_INCLUDES_DIR . '/core-classes'
                    ),
                    array(
                        $this->get_slug() . "-content-empty-{$display_mode}.template.php",
                        $this->get_slug() . "-content-empty.template.php",
                        "content-page-content-empty-{$display_mode}.template.php"
                    ),
                    'templates',
                    "content-page-content-empty.template.php"));
            }
        }

        /*------- SINGLE POST PAGES -------------------------------------------------------------------------------------*/

        public function add_single_private_content_contextual_toolbar_group($groups)
        {
            // If not on a matching post type, we do nothing
            if ( !is_singular($this->get_friendly_post_type())) return $groups;
            if (get_post_type() != $this->get_friendly_post_type()) return $groups;

            $links = apply_filters('cuar/private-container/view/single-post-action-links', array());
            $links = apply_filters('cuar/private-container/view/single-post-action-links?post-type=' . $this->get_friendly_post_type(), $links);

            if ( !empty($links))
            {
                $groups['singular-container'] = array(
                    'items'       => $links,
                    'extra_class' => 'pull-right'
                );
            }

            return $groups;
        }

        public function print_single_private_container_header()
        {
            do_action('cuar/private-container/view/before_header', $this);
            do_action('cuar/private-container/view/before_header?post-type=' . $this->get_friendly_post_type(), $this);

            $template_file_path = $this->plugin->get_template_file_path(
                $this->get_page_addon_path(),
                $this->get_slug() . '-single-post-header.template.php',
                'templates');
            if ( !empty($template_file_path))
            {
                include($template_file_path);
            }

            do_action('cuar/private-container/view/before_additional_header', $this);
            do_action('cuar/private-container/view/before_additional_header?post-type=' . $this->get_friendly_post_type(), $this);

            $this->print_additional_private_container_header();

            do_action('cuar/private-container/view/after_header', $this);
            do_action('cuar/private-container/view/after_header?post-type=' . $this->get_friendly_post_type(), $this);
        }

        public function print_single_private_container_footer()
        {
            do_action('cuar/private-container/view/before_footer', $this);
            do_action('cuar/private-container/view/before_footer?post-type=' . $this->get_friendly_post_type(), $this);

            $template_file_path = $this->plugin->get_template_file_path(
                $this->get_page_addon_path(),
                $this->get_slug() . '-single-post-footer.template.php',
                'templates');
            if ( !empty($template_file_path))
            {
                include($template_file_path);
            }

            do_action('cuar/private-container/view/before_additional_footer', $this);
            do_action('cuar/private-container/view/before_additional_footer?post-type=' . $this->get_friendly_post_type(), $this);

            $this->print_additional_private_container_footer();

            $this->print_associated_content();

            do_action('cuar/private-container/view/after_footer', $this);
            do_action('cuar/private-container/view/after_footer?post-type=' . $this->get_friendly_post_type(), $this);
        }

        public function print_single_private_container_meta_filter($content)
        {
            // If theme is taking care of it, don't do anything
            $theme_support = get_theme_support('customer-area.single-post-templates');
            if (is_array($theme_support) && in_array($this->get_friendly_post_type(), $theme_support[0])) return $content;

            // If not on a matching post type, we do nothing
            if ( !is_singular($this->get_friendly_post_type())) return $content;
            if (get_post_type() != $this->get_friendly_post_type()) return $content;

            ob_start();
            $this->print_single_private_container_header();
            $before = ob_get_contents();
            ob_end_clean();

            ob_start();
            $this->print_single_private_container_footer();
            $after = ob_get_contents();
            ob_end_clean();

            return $before . $content . $after;
        }

        /**
         * Retrieve all the private content associated to this project and display it here
         *
         */
        public function print_associated_content()
        {
            do_action('cuar/core/container/view/before_associated_content', $this);

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');

            $container_id = get_queried_object_id();
            $page_slug = $this->get_slug();
            $display_mode = 'container';
            $content_types = $this->plugin->get_content_types();

            foreach ($content_types as $post_type => $desc)
            {
                $args = array(
                    'post_type'      => $post_type,
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'meta_query'     => array(
                        $po_addon->get_owner_meta_query_component($this->get_container_owner_type(), $container_id)
                    )
                );
                $args = apply_filters('cuar/core/page/query-args/associated-content?slug=' . $page_slug, $args);
                $args = apply_filters('cuar/core/page/query-args/associated-content?type=' . $post_type, $args);
                $args = apply_filters('cuar/core/page/query-args/associated-content?slug=' . $page_slug . '&type=' . $post_type, $args);

                $content_query = new WP_Query($args);

                $page_subtitle = apply_filters('cuar/core/page/container-content-subtitle?type=' . $post_type, $desc['label-plural']);

                if ($content_query->have_posts())
                {
                    $content_page_addon = $this->plugin->get_addon($desc['content-page-addon']);
                    if ($content_page_addon != null)
                    {
                        $template_root = $content_page_addon->get_page_addon_path();
                        $template_prefix = $content_page_addon->get_slug();
                    }
                    else
                    {
                        $template_root = CUAR_PLUGIN_DIR . '/src/php/core-classes';
                        $template_prefix = $post_type;
                    }

                    $item_template = $this->plugin->get_template_file_path(
                        $template_root,
                        array(
                            $template_prefix . "-content-item-{$display_mode}.template.php",
                            $template_prefix . "-content-item.template.php",
                            "default-container-content-item.template.php"
                        ),
                        'templates');

                    include($this->plugin->get_template_file_path(
                        $template_root,
                        array(
                            $template_prefix . "-content-{$display_mode}.template.php",
                            $template_prefix . "-content.template.php",
                            "default-container-content.template.php"
                        ),
                        'templates'));

                    wp_reset_query();
                }
            }

            do_action('cuar/core/container/view/after_associated_content', $this);
        }

        protected function print_additional_private_container_header() { }

        protected function print_additional_private_container_footer() { }

        /*------- DASHBOARD BLOCK ---------------------------------------------------------------------------------------*/

        public function print_dashboard_content()
        {
            if ( !$this->is_accessible_to_current_user()) return;

            $co_addon = $this->plugin->get_addon('container-owner');
            $current_user_id = get_current_user_id();
            $page_slug = $this->get_slug();

            $args = array(
                'post_type'      => $this->get_friendly_post_type(),
                'posts_per_page' => $this->get_max_item_number_on_dashboard(),
                'orderby'        => 'modified',
                'order'          => 'DESC',
                'meta_query'     => $co_addon->get_meta_query_containers_owned_by($current_user_id)
            );

            $page_subtitle = $this->get_default_dashboard_block_title();
            $page_subtitle = apply_filters('cuar/core/dashboard/block-title?slug=' . $page_slug, $page_subtitle);

            $args = apply_filters('cuar/core/dashboard/block-query-args?slug=' . $page_slug, $args);

            $content_query = new WP_Query($args);

            if ($content_query->have_posts())
            {
                $item_template = $this->plugin->get_template_file_path(
                    array(
                        $this->get_page_addon_path(),
                        CUAR_INCLUDES_DIR . '/core-classes'
                    ),
                    array(
                        $this->get_slug() . "-content-item-dashboard.template.php",
                        $this->get_slug() . "-content-item.template.php",
                        "content-page-content-item-dashboard.template.php"
                    ),
                    'templates',
                    "content-page-content-item.template.php");

                include($this->plugin->get_template_file_path(
                    array(
                        $this->get_page_addon_path(),
                        CUAR_INCLUDES_DIR . '/core-classes'
                    ),
                    array(
                        $this->get_slug() . "-content-dashboard.template.php",
                        $this->get_slug() . "-content.template.php",
                        "content-page-content-dashboard.template.php"
                    ),
                    'templates',
                    "content-page-content.template.php"));

                wp_reset_query();
            }
            else
            {
                include($this->plugin->get_template_file_path(
                    array(
                        $this->get_page_addon_path(),
                        CUAR_INCLUDES_DIR . '/core-classes'
                    ),
                    array(
                        $this->get_slug() . "-content-empty-dashboard.template.php",
                        $this->get_slug() . "-content-empty.template.php",
                        "content-page-content-empty-dashboard.template.php"
                    ),
                    'templates',
                    "content-page-content-empty.template.php"));
            }
        }

        /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

        public function enable_settings($target_tab, $enabled_settings = array('dashboard', 'single-post-footer', 'taxonomy'))
        {
            $this->enabled_settings = $enabled_settings;

            if (is_admin() && !empty($this->enabled_settings))
            {
                // Settings
                add_action('cuar/core/settings/print-settings?tab=' . $target_tab, array(&$this, 'print_settings'), 10, 2);
                add_filter('cuar/core/settings/validate-settings?tab=' . $target_tab, array(&$this, 'validate_options'), 10, 3);

                if (in_array('taxonomy', $this->enabled_settings))
                {
                    add_action('cuar/core/settings/print-settings?tab=cuar_frontend', array(&$this, 'print_frontend_settings'), 60, 2);
                    add_filter('cuar/core/settings/validate-settings?tab=cuar_frontend', array(&$this, 'validate_frontend_settings'), 60, 3);
                }
            }
        }

        protected function get_settings_section()
        {
            return $this->get_slug() . '_frontend';
        }

        /**
         * Add our fields to the settings page
         *
         * @param CUAR_Settings $cuar_settings The settings class
         */
        public function print_settings($cuar_settings, $options_group)
        {
            if (empty($this->enabled_settings)) return;

            $slug = $this->get_slug();

            add_settings_section(
                $this->get_settings_section(),
                __('Frontend Integration', 'cuar'),
                array(&$this, 'print_empty_section_info'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG
            );

            if (in_array('single-post-footer', $this->enabled_settings))
            {
                $theme_support = get_theme_support('customer-area.single-post-templates');
                if ( !is_array($theme_support) || !in_array($this->get_friendly_post_type(), $theme_support[0]))
                {
                    add_settings_field(
                        $slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER,
                        __('Show after post', 'cuar'),
                        array(&$cuar_settings, 'print_input_field'),
                        CUAR_Settings::$OPTIONS_PAGE_SLUG,
                        $this->get_settings_section(),
                        array(
                            'option_id'     => $slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER,
                            'type'          => 'checkbox',
                            'default_value' => 1,
                            'after'         =>
                                __('Show additional information after the post in the single post view.', 'cuar')
                                . '<p class="description">'
                                . sprintf(__('You can disable this if you have your own theme template file for single posts. The theme file to look for or create should be called: %s.',
                                    'cuar'),
                                    '<code>single-' . $this->get_friendly_post_type() . '.php</code>')
                                . '</p>'
                        )
                    );
                }
            }

            if (in_array('dashboard', $this->enabled_settings))
            {
                add_settings_field(
                    $slug . self::$OPTION_SHOW_IN_DASHBOARD,
                    __('Dashboard', 'cuar'),
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    $this->get_settings_section(),
                    array(
                        'option_id'     => $slug . self::$OPTION_SHOW_IN_DASHBOARD,
                        'type'          => 'checkbox',
                        'default_value' => 1,
                        'after'         => __('Show recent content on the dashboard.', 'cuar')
                    )
                );

                add_settings_field(
                    $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD,
                    '',
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    $this->get_settings_section(),
                    array(
                        'option_id'     => $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD,
                        'type'          => 'text',
                        'default_value' => 1,
                        'after'         => '<p class="description">' . __('Define how many items to allow on the dashboard page. -1 will show all items.',
                                'cuar')
                    )
                );
            }

            add_settings_field(
                $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_LISTING,
                '',
                array(&$cuar_settings, 'print_input_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                $this->get_settings_section(),
                array(
                    'option_id'     => $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_LISTING,
                    'type'          => 'text',
                    'default_value' => 1,
                    'after'         => '<p class="description">'
                        . __('Define how many items to allow on the listing pages (main listing page, archive pages, etc.). -1 will show all items but this '
                            . 'can slow down the page display if the user has a lot of private items.', 'cuar')
                        . '</p>'
                )
            );

            $this->print_additional_settings($cuar_settings, $options_group);
        }

        /**
         * Validate our options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         * @param array         $validated
         */
        public function validate_options($validated, $cuar_settings, $input)
        {
            $slug = $this->get_slug();

            $cuar_settings->validate_boolean($input, $validated, $slug . self::$OPTION_SHOW_IN_SINGLE_POST_FOOTER);
            $cuar_settings->validate_boolean($input, $validated, $slug . self::$OPTION_SHOW_IN_DASHBOARD);
            $cuar_settings->validate_int($input, $validated, $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD);
            $cuar_settings->validate_int($input, $validated, $slug . self::$OPTION_MAX_ITEM_NUMBER_ON_LISTING);

            $validated = $this->validate_additional_settings($validated, $cuar_settings, $input);

            return $validated;
        }

        public function print_frontend_settings($cuar_settings, $options_group)
        {
            if (empty($this->enabled_settings)) return;

            $slug = $this->get_slug();

            $friendly_tax = $this->get_friendly_taxonomies();
            if ($friendly_tax == null) return;

            foreach ($friendly_tax as $tax)
            {
                $option_id = $slug . self::$OPTION_TAXONOMY_SLUG . $tax;

                $tax_object = get_taxonomy($tax);
                $current_slug = $this->plugin->get_option($option_id);
                if ( !isset($current_slug) || empty($current_slug))
                {
                    $tax_slug = sanitize_title_with_dashes($tax_object->labels->name);

                    $cuar_settings->update_option_default($option_id, $tax_slug);
                    $this->plugin->update_option($option_id, $tax_slug);
                }

                add_settings_field(
                    $option_id,
                    $tax_object->labels->name,
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    'cuar_core_permalinks',
                    array(
                        'option_id' => $option_id,
                        'type'      => 'text',
                        'is_large'  => false,
                        'after'     => '<p class="description">'
                            . __('Slug that is used in the URL for archives of the taxonomy: ', 'cuar')
                            . $tax_object->labels->name
                            . '</p>'
                    )
                );
            }
        }

        /**
         * Validate our options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         * @param array         $validated
         */
        public function validate_frontend_settings($validated, $cuar_settings, $input)
        {
            $slug = $this->get_slug();

            $friendly_tax = $this->get_friendly_taxonomies();
            if ($friendly_tax == null) return;

            foreach ($friendly_tax as $tax)
            {
                $option_id = $slug . self::$OPTION_TAXONOMY_SLUG . $tax;

                $cuar_settings->validate_not_empty($input, $validated, $option_id);
            }

            return $validated;
        }

        protected function print_additional_settings($cuar_settings, $options_group)
        {
        }

        protected function validate_additional_settings(&$validated, $cuar_settings, $input)
        {
            return $validated;
        }

        public function print_empty_section_info()
        {
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);

            if ($this->get_friendly_post_type() != null)
            {
                add_filter("get_previous_post_where", array(&$this, 'disable_single_post_navigation'), 1, 3);
                add_filter("get_next_post_where", array(&$this, 'disable_single_post_navigation'), 1, 3);
            }

            if ( !is_admin())
            {
                add_filter('cuar/core/page/toolbar', array(&$this, 'add_single_private_content_contextual_toolbar_group'), 1400);
                add_filter('cuar/core/page/toolbar', array(&$this, 'add_listing_contextual_toolbar_group'), 2000);

                // Optionally output the file links in the post footer area
                if ($this->is_show_in_single_post_footer_enabled())
                {
                    add_filter('cuar/core/the_content', array(&$this, 'print_single_private_container_meta_filter'), 20);
                }

                // Optionally output the latest files on the dashboard
                if ($this->is_show_in_dashboard_enabled())
                {
                    $priority = apply_filters('cuar/core/page/dashboard-block-priority', 9, $this->get_slug());
                    add_action('cuar/core/page/before-content?slug=customer-dashboard', array(&$this, 'print_dashboard_content'), $priority);
                }
            }
        }

        // Settings
        public static $OPTION_SHOW_IN_SINGLE_POST_FOOTER = '-show_in_single_post_footer';
        public static $OPTION_SHOW_IN_DASHBOARD = '-show_in_dashboard';
        public static $OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD = '-max_items_on_dashboard';
        public static $OPTION_MAX_ITEM_NUMBER_ON_LISTING = '-max_items_on_listing';
        public static $OPTION_TAXONOMY_SLUG = '-taxonomy_slug-';

        protected $enabled_settings = array();
    }

endif; // CUAR_AbstractContainerPageAddOn/ CUAR_AbstractContainerPageAddOn
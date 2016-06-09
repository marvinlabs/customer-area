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

if ( !class_exists('CUAR_AbstractContentPageAddOn')) :

    /**
     * The base class for addons that should render a page containing content from custom private posts
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_AbstractContentPageAddOn extends CUAR_AbstractPageAddOn
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

            if ( !isset($this->page_description['friendly_taxonomy']))
            {
                $this->page_description['friendly_taxonomy'] = null;
            }
        }

        public function get_type()
        {
            return 'list-content';
        }

        public function get_friendly_post_type()
        {
            return $this->page_description['friendly_post_type'];
        }

        public function get_friendly_taxonomy()
        {
            return $this->page_description['friendly_taxonomy'];
        }

        protected abstract function get_author_archive_page_subtitle($author_id);

        protected abstract function get_category_archive_page_subtitle($category);

        protected abstract function get_date_archive_page_subtitle($year, $month = 0);

        protected abstract function get_default_page_subtitle();

        protected abstract function get_default_dashboard_block_title();

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

        /*------- SETTINGS ACCESSOR -------------------------------------------------------------------------------------*/

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
            $value = $this->plugin->get_option($this->get_slug() . self::$OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD);

            return empty($value) ? 5 : $value;
        }

        public function get_max_item_number_in_listing()
        {
            $value = $this->plugin->get_option($this->get_slug() . self::$OPTION_MAX_ITEM_NUMBER_ON_LISTING);

            return empty($value) ? 10 : $value;
        }

        /*------- ARCHIVES ----------------------------------------------------------------------------------------------*/

        /**
         * Allow this page to get URLs for content archives
         */
        protected function enable_content_archives_permalinks()
        {
            add_filter('rewrite_rules_array', array(&$this, 'insert_archive_rewrite_rules'));
            add_filter('query_vars', array(&$this, 'insert_archive_query_vars'));
            add_filter('term_link', array(&$this, 'get_friendly_taxonomy_term_link'), 10, 3);
        }

        /**
         * Build the link for a given term
         *
         * @param $term_link
         * @param $term
         * @param $taxonomy
         *
         * @return string
         */
        public function get_friendly_taxonomy_term_link($term_link, $term, $taxonomy)
        {
            if ($taxonomy != $this->get_friendly_taxonomy()) return $term_link;

            return $this->get_category_archive_url($term);
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
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $page_id = $cp_addon->get_page_id($this->get_slug());
            $page_slug = untrailingslashit(str_replace(trailingslashit(home_url()), '', get_permalink($page_id)));

            $new_rules = array();

            // Category archives
            if ($this->get_friendly_taxonomy() != null)
            {
                $rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_category=$matches[1]';
                $rewrite_regex = $page_slug . '/' . $cp_addon->get_category_archive_slug() . '/([^/]+)/?$';
                $new_rules[$rewrite_regex] = $rewrite_rule;
            }

            // Author archives
            $rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_author=$matches[1]';
            $rewrite_regex = $page_slug . '/' . $cp_addon->get_author_archive_slug() . '/([0-9]+)/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            // Year archives
            $rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_year=$matches[1]';
            $rewrite_regex = $page_slug . '/' . $cp_addon->get_date_archive_slug() . '/([0-9]{4})/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            // Month archives
            $rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_year=$matches[1]&cuar_month=$matches[2]';
            $rewrite_regex = $page_slug . '/' . $cp_addon->get_date_archive_slug() . '/([0-9]{4})/([0-9]{2})/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            return $new_rules + $rules;
        }

        /**
         * Add query variables for the archive subpages.
         *
         * @param array $vars
         *
         * @return array
         */
        public function insert_archive_query_vars($vars)
        {
            if ($this->get_friendly_taxonomy() != null)
            {
                array_push($vars, 'cuar_category');
            }

            array_push($vars, 'cuar_year');
            array_push($vars, 'cuar_month');
            array_push($vars, 'cuar_author');

            return $vars;
        }

        /**
         * Get the URL for the archive corresponding to a given date.
         *
         * @param int $year
         * @param int $month optional, pass a negative number to get year archive
         *
         * @return string
         */
        public function get_date_archive_url($year, $month = 0)
        {
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $page_id = $cp_addon->get_page_id($this->get_slug());
            if ($page_id == false) return '';

            $url = trailingslashit(get_permalink($page_id));
            $url .= $cp_addon->get_date_archive_slug() . '/';
            $url .= $year . '/';

            if ($month > 0)
            {
                $url .= sprintf('%02d', $month);
            }

            return $url;
        }

        /**
         * Get the URL for the archive corresponding to a given category.
         *
         * @param WP_Term $term
         *
         * @return string
         */
        public function get_category_archive_url($term)
        {
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $page_id = $cp_addon->get_page_id($this->get_slug());
            if ($page_id == false) return '';

            $url = trailingslashit(get_permalink($page_id));
            $url .= $cp_addon->get_category_archive_slug() . '/';
            $url .= $term->slug;

            return $url;
        }

        /**
         * Get the URL for the archive corresponding to a given author.
         *
         * @param int $author_id
         *
         * @return string
         */
        public function get_author_archive_url($author_id)
        {
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');

            $page_id = $cp_addon->get_page_id($this->get_slug());
            if ($page_id == false) return '';

            $url = trailingslashit(get_permalink($page_id));
            $url .= $cp_addon->get_author_archive_slug() . '/';
            $url .= $author_id;

            return $url;
        }

        /*------- SINGLE PRIVATE CONTENT --------------------------------------------------------------------------------*/

        /**
         * Allow this page to get URLs for single private content pages
         */
        protected function enable_single_private_content_permalinks()
        {
            if ( !isset($this->page_description['friendly_post_type']))
            {
                die('Cannot enable single content permalinks for page without declaring its friendly_post_type');
            }

            add_filter('rewrite_rules_array', array(&$this, 'insert_single_post_rewrite_rules'));
            add_filter('query_vars', array(&$this, 'insert_single_post_query_vars'));
            add_filter('post_type_link', array(&$this, 'filter_single_private_content_link'), 10, 2);
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

            $new_rules = array();

            // Single post rule
            $rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type'] . '=$matches[4]';
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            // Single post rule with action
            $rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type']
                . '=$matches[4]&cuar_action=$matches[5]';
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/([^/]+)/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            // Single post rule with action & action param
            $rewrite_rule = 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&' . $this->page_description['friendly_post_type']
                . '=$matches[4]&cuar_action=$matches[5]&cuar_action_param=$matches[6]';
            $rewrite_regex = $page_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/([^/]+)/([^/]+)/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            return $new_rules + $rules;
        }

        /**
         * Add query variables for single private content pages.
         *
         * @param array $vars
         *
         * @return array
         */
        public function insert_single_post_query_vars($vars)
        {
            array_push($vars, 'cuar_action');
            array_push($vars, 'cuar_action_param');
            array_push($vars, $this->page_description['friendly_post_type']);

            return $vars;
        }

        /**
         * Output the correct permalink for our single private content pages
         *
         * @param string $permalink
         * @param WP_Post $post
         *
         * @return string
         */
        function filter_single_private_content_link($permalink, $post)
        {
            $post_type = $this->page_description['friendly_post_type'];

            if ($post_type == $post->post_type
                && '' != $permalink
                && !in_array($post->post_status, array('draft', 'pending', 'auto-draft'))
            )
            {
                $permalink = $this->get_single_private_content_url($post);
            }

            return $permalink;
        }

        /**
         * Get the URL to view a given post
         *
         * @param WP_Post $post
         *
         * @return string
         */
        public function get_single_private_content_url($post)
        {
            /** @var CUAR_CustomerPagesAddOn $cp_addon */
            $cp_addon = $this->plugin->get_addon('customer-pages');
            $page_id = $cp_addon->get_page_id($this->get_slug());
            if ($page_id == false) return '';

            $post = get_post($post);

            $date = mysql2date('Y m d', $post->post_date);
            $date = explode(" ", $date);

            $url = trailingslashit(get_permalink($page_id));
            $url .= sprintf('%04d/%02d/%02d/%s', $date[0], $date[1], $date[2], $post->post_name);

            return $url;
        }

        public function get_single_private_content_action_url($post, $action = '', $action_param = '')
        {
            $url = $this->get_single_private_content_url($post);
            if ( !empty($action))
            {
                $url .= '/' . $action;

                if ( !empty($action_param))
                {
                    $url .= '/' . $action_param;
                }
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
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $current_user_id = get_current_user_id();
            $page_slug = $this->get_slug();

            // Display mode
            $year = get_query_var('cuar_year');
            $month = get_query_var('cuar_month');
            $category = get_query_var('cuar_category');
            $author_id = get_query_var('cuar_author');
            $display_mode = 'default';

            // Texts
            $page_subtitle = '';

            // Paging
            $pagination_param = _x('page-num', 'pagination_parameter_name (should not be "page")', 'cuar');
            $current_page = isset($_GET[$pagination_param]) ? $_GET[$pagination_param] : 1;
            $posts_per_page = $this->get_max_item_number_in_listing();
            $pagination_base = '';

            if ( !empty($category) && $this->get_friendly_taxonomy() != null)
            {
                $cat = get_term_by('slug', $category, $this->get_friendly_taxonomy());

                // Category archive, only show the files from that category
                $display_mode = 'category_archive';
                $page_subtitle = $this->get_category_archive_page_subtitle($cat);
                $pagination_base = $this->get_category_archive_url($cat);

                $args = array(
                    'post_type'      => $this->get_friendly_post_type(),
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'meta_query'     => $po_addon->get_meta_query_post_owned_by($current_user_id),
                    'tax_query'      => array(
                        array(
                            'taxonomy' => $this->get_friendly_taxonomy(),
                            'field'    => 'slug',
                            'terms'    => $category
                        )
                    )
                );
            }
            else if ( !empty($year))
            {
                // Date archive, only show the files from that year/month
                $display_mode = 'date_archive';

                $args = array(
                    'post_type'      => $this->get_friendly_post_type(),
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'meta_query'     => $po_addon->get_meta_query_post_owned_by($current_user_id),
                    'year'           => $year
                );

                if ( !empty($month))
                {
                    $args['monthnum'] = (int)$month;
                    $pagination_base = $this->get_date_archive_url($year, $month);
                }
                else
                {
                    $pagination_base = $this->get_date_archive_url($year);
                }

                $page_subtitle = $this->get_date_archive_page_subtitle($year, $month);
            }
            else if ( !empty($author_id))
            {
                // Author archive, only show the files created by that user
                $display_mode = 'author_archive';

                $args = array(
                    'post_type'      => $this->get_friendly_post_type(),
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'author'         => $author_id
                );

                // If the content authored by someone else is requested, don't show anyone else's content
                if ($author_id != get_current_user_id())
                {
                    $args['meta_query'] = $po_addon->get_meta_query_post_owned_by($current_user_id);
                }

                $pagination_base = $this->get_author_archive_url($author_id);

                $page_subtitle = $this->get_author_archive_page_subtitle($author_id);
            }
            else
            {
                // Default view
                $args = array(
                    'post_type'      => $this->get_friendly_post_type(),
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'meta_query'     => $po_addon->get_meta_query_post_owned_by($current_user_id)
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
                /** @var CUAR_CustomerPagesAddOn $cp_addon */
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

        /*------- SINGLE POST PAGES -------------------------------------------------------------------------------------*/

        public function add_single_private_content_contextual_toolbar_group($groups)
        {
            // If not on a matching post type, we do nothing
            if ( !is_singular($this->get_friendly_post_type())) return $groups;
            if (get_post_type() != $this->get_friendly_post_type()) return $groups;

            $links = apply_filters('cuar/private-content/view/single-post-action-links', array());
            $links = apply_filters('cuar/private-content/view/single-post-action-links?post-type=' . $this->get_friendly_post_type(), $links);

            if ( !empty($links))
            {
                $groups['singular-content'] = array(
                    'items'       => $links,
                    'extra_class' => 'pull-right'
                );
            }

            return $groups;
        }

        public function print_single_private_content_header()
        {
            do_action('cuar/private-content/view/before_header', $this);
            do_action('cuar/private-content/view/before_header?post-type=' . $this->get_friendly_post_type(), $this);

            $template_file_path = $this->plugin->get_template_file_path(
                $this->get_page_addon_path(),
                $this->get_slug() . '-single-post-header.template.php',
                'templates');
            if ( !empty($template_file_path))
            {
                include($template_file_path);
            }

            do_action('cuar/private-content/view/before_additional_header', $this);
            do_action('cuar/private-content/view/before_additional_header?post-type=' . $this->get_friendly_post_type(), $this);

            $this->print_additional_private_content_header();

            do_action('cuar/private-content/view/after_header', $this);
            do_action('cuar/private-content/view/after_header?post-type=' . $this->get_friendly_post_type(), $this);
        }

        public function print_single_private_content_footer()
        {
            do_action('cuar/private-content/view/before_footer', $this);
            do_action('cuar/private-content/view/before_footer?post-type=' . $this->get_friendly_post_type(), $this);

            $template_file_path = $this->plugin->get_template_file_path(
                $this->get_page_addon_path(),
                $this->get_slug() . '-single-post-footer.template.php',
                'templates');
            if ( !empty($template_file_path))
            {
                include($template_file_path);
            }

            do_action('cuar/private-content/view/before_additional_footer', $this);
            do_action('cuar/private-content/view/before_additional_footer?post-type=' . $this->get_friendly_post_type(), $this);

            $this->print_additional_private_content_footer();

            do_action('cuar/private-content/view/after_footer', $this);
            do_action('cuar/private-content/view/after_footer?post-type=' . $this->get_friendly_post_type(), $this);
        }

        public function print_single_private_content_meta_filter($content)
        {
            // If theme is taking care of it, don't do anything
            $theme_support = get_theme_support('customer-area.single-post-templates');
            if (is_array($theme_support) && in_array($this->get_friendly_post_type(), $theme_support[0])) return $content;

            // If not on a matching post type, we do nothing
            if ( !is_singular($this->get_friendly_post_type())) return $content;
            if (get_post_type() != $this->get_friendly_post_type()) return $content;

            ob_start();
            $this->print_single_private_content_header();
            $before = ob_get_contents();
            ob_end_clean();

            ob_start();
            $this->print_single_private_content_footer();
            $after = ob_get_contents();
            ob_end_clean();

            return $before . $content . $after;
        }

        protected function print_additional_private_content_header() { }

        protected function print_additional_private_content_footer() { }

        /*------- DASHBOARD BLOCK ---------------------------------------------------------------------------------------*/

        public function print_dashboard_content()
        {
            if ( !$this->is_accessible_to_current_user()) return;

            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $current_user_id = get_current_user_id();
            $page_slug = $this->get_slug();

            $args = array(
                'post_type'      => $this->get_friendly_post_type(),
                'posts_per_page' => $this->get_max_item_number_on_dashboard(),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => $po_addon->get_meta_query_post_owned_by($current_user_id)
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

        public function enable_settings($target_tab, $enabled_settings = array('dashboard', 'single-post-footer'))
        {
            $this->enabled_settings = $enabled_settings;

            if (is_admin() && !empty($this->enabled_settings))
            {
                // Settings
                add_action('cuar/core/settings/print-settings?tab=' . $target_tab, array(&$this, 'print_settings'), 10, 2);
                add_filter('cuar/core/settings/validate-settings?tab=' . $target_tab, array(&$this, 'validate_options'), 10, 3);
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
                        'after'         =>
                            '<p class="description">' . __('Define how many items to allow on the dashboard page. -1 will show all items.', 'cuar') . '</p>'
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
         *
         * @return array
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

            if ($this->get_friendly_post_type() != null) {
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
                    add_filter('cuar/core/the_content', array(&$this, 'print_single_private_content_meta_filter'), 20);
                }

                // Optionally output the latest files on the dashboard
                if ($this->is_show_in_dashboard_enabled())
                {
                    $priority = apply_filters('cuar/core/page/dashboard-block-priority', 10, $this->get_slug());
                    add_action('cuar/core/page/before-content?slug=customer-dashboard', array(&$this, 'print_dashboard_content'), $priority);
                }
            }
        }

        // Settings
        public static $OPTION_SHOW_IN_SINGLE_POST_FOOTER = '-show_in_single_post_footer';
        public static $OPTION_SHOW_IN_DASHBOARD = '-show_in_dashboard';
        public static $OPTION_MAX_ITEM_NUMBER_ON_DASHBOARD = '-max_items_on_dashboard';
        public static $OPTION_MAX_ITEM_NUMBER_ON_LISTING = '-max_items_on_listing';

        protected $enabled_settings = array();
    }

endif; // CUAR_AbstractContentPageAddOn/ CUAR_AbstractContentPageAddOn
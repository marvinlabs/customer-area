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
require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-page-shortcode.class.php');

if ( !class_exists('CUAR_AbstractPageAddOn')) :

    /**
     * The base class for addons that should render a page
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_AbstractPageAddOn extends CUAR_AddOn
    {

        public function __construct($addon_id = null, $min_cuar_version = null)
        {
            parent::__construct($addon_id, $min_cuar_version);
        }

        public function run_addon($plugin)
        {
            add_filter('cuar/core/page/customer-pages', array(&$this, 'register_page'), $this->page_priority);
            add_filter('cuar/core/page/on-page-created?slug=' . $this->get_slug(), array(&$this, 'create_default_page'), 10, 2);
            add_filter('body_class', array(&$this, 'add_body_class'));

            add_action('template_redirect', array(&$this, 'redirect_guests_if_required'), 1000);
        }

        public function get_addon_name()
        {
            return sprintf(__('Customer Page - %s', 'cuar'), $this->get_label());
        }

        public abstract function get_title();

        public abstract function get_label();

        public abstract function get_hint();

        /*------- PAGE PARAMETERS ---------------------------------------------------------------------------------------*/

        public function get_permalink()
        {
            return null;
        }

        public function get_priority()
        {
            return $this->page_priority;
        }

        public function get_slug()
        {
            return $this->page_description['slug'];
        }

        public function get_parent_slug()
        {
            return $this->page_description['parent_slug'];
        }

        public function get_menu_order()
        {
            return $this->page_description['menu_order'];
        }

        public function requires_login()
        {
            return $this->page_description['requires_login'];
        }

        public function hide_if_logged_in()
        {
            return $this->page_description['hide_if_logged_in'];
        }

        public function hide_in_menu()
        {
            return $this->page_description['hide_in_menu'];
        }

        public function always_include_in_menu()
        {
            return $this->page_description['always_include_in_menu'];
        }

        public function get_friendly_post_type()
        {
            return null;
        }

        public function get_friendly_taxonomy()
        {
            return null;
        }

        public function get_required_capability()
        {
            return $this->page_description['required_capability'];
        }

        public function is_accessible_to_current_user()
        {
            $cap = $this->get_required_capability();

            return apply_filters('cuar/core/page/check-access-granted', empty($cap) || current_user_can($cap), $this);
        }

        protected function set_page_parameters($priority, $description)
        {
            $this->page_priority = $priority;
            $this->page_description = $description;

            if ( !isset($this->page_description['requires_login']))
            {
                $this->page_description['requires_login'] = true;
            }

            if ( !isset($this->page_description['parent_slug']))
            {
                $this->page_description['parent_slug'] = '';
            }

            if ( !isset($this->page_description['menu_order']))
            {
                $this->page_description['menu_order'] = $priority;
            }

            if ( !isset($this->page_description['required_capability']))
            {
                $this->page_description['required_capability'] = '';
            }

            if ( !isset($this->page_description['hide_if_logged_in']))
            {
                $this->page_description['hide_if_logged_in'] = false;
            }

            if ( !isset($this->page_description['hide_in_menu']))
            {
                $this->page_description['hide_in_menu'] = false;
            }

            if ( !isset($this->page_description['always_include_in_menu']))
            {
                $this->page_description['always_include_in_menu'] = false;
            }
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        public function get_type()
        {
            return 'base';
        }

        public abstract function get_page_addon_path();

        public function register_page($pages)
        {
            if ($this->page_description != null)
            {
                $pages[$this->get_slug()] = $this;
            }

            return $pages;
        }

        public function get_child_pages()
        {
            if ($this->child_pages == null)
            {
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $this->child_pages = $cp_addon->get_customer_area_child_pages($this->get_slug());
            }

            return $this->child_pages;
        }

        public function get_page_id()
        {
            if ($this->page_id <= 0)
            {
                /** @var CUAR_CustomerPagesAddOn $cp_addon */
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $this->page_id = $cp_addon->get_page_id($this->get_slug());
            }

            return $this->page_id;
        }

        public function get_page_url()
        {
            if ($this->page_url <= 0)
            {
                /** @var CUAR_CustomerPagesAddOn $cp_addon */
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $this->page_url = $cp_addon->get_page_url($this->get_slug());
            }

            return $this->page_url;
        }

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
         * Create the corresponding WordPress page.
         */
        public function create_default_page($existing_page_id, $options_array = null)
        {
            $page_data = array(
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
                'menu_order'     => $this->page_priority
            );

            // If a permalink is specified, we'll use it
            if ($this->get_permalink() != null)
            {
                $page_data['post_name'] = $this->get_permalink();
            }

            // If a slug is specified, we will try to find that page from the options
            $parent_slug = $this->get_parent_slug();
            if ( !empty($parent_slug))
            {
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $parent_id = $cp_addon->get_page_id($parent_slug, $options_array);

                if ($parent_id > 0)
                {
                    $page_data['post_parent'] = $parent_id;
                }
            }

            // If title or descriptions are not specified, we'll give some defaults
            $page_data['post_title'] = $this->get_title();
            if (empty($page_data['post_title'])) $page_data['post_title'] = $this->get_label();
            if (empty($page_data['post_title'])) $page_data['post_title'] = $this->get_slug();

            if ($this->shortcode != null)
            {
                $page_data['post_content'] = $this->shortcode->get_sample_shortcode();
            }
            else
            {
                $page_data['post_content'] = '';
            }

            // Create the page
            $page_id = wp_insert_post($page_data);

            if ( !is_wp_error($page_id))
            {
                $this->page_id = $page_id;
            }

            return $page_id;
        }

        public function redirect_guests_if_required()
        {
            if ($this->requires_login() && !is_user_logged_in() && get_queried_object_id() == $this->get_page_id())
            {
                $this->plugin->login_then_redirect_to_page($this->get_slug());
            }
        }

        public function print_page($args = array(), $shortcode_content = '')
        {
            if ($this->requires_login() && !is_user_logged_in())
            {
                _e('This page requires login, you should not be here', 'cuar');
            }
            else if ($this->is_accessible_to_current_user())
            {
                $template_path = $this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-classes',
                    'customer-page.template.php',
                    'templates');
                include($template_path);
            }
            else
            {
                echo '<p>' . __('You are not allowed to view this page', 'cuar') . '</p>';
            }
        }

        public function print_page_header($args = array(), $shortcode_content = '')
        {
            $this->print_page_part('header');
        }

        public function print_page_sidebar($args = array(), $shortcode_content = '')
        {
            if ($this->has_page_sidebar())
            {
                if ( !$this->print_page_part('sidebar'))
                {
                    if ( !dynamic_sidebar($this->get_sidebar_id()))
                    {
                        $this->print_default_widgets();
                    }
                }
            }
        }

        public function print_page_content($args = array(), $shortcode_content = '')
        {
            $this->print_page_part('content');
        }

        public function print_page_footer($args = array(), $shortcode_content = '')
        {
            $this->print_page_part('footer');
        }

        protected function print_page_part($part)
        {
            $slug = $this->get_slug();

            $template = $this->plugin->get_template_file_path(
                $this->get_page_addon_path(),
                $slug . "-" . $part . ".template.php",
                'templates');

            do_action('cuar/core/page/before-' . $part);
            do_action('cuar/core/page/before-' . $part . '?slug=' . $slug);

            if ( !empty($template))
            {
                include($template);
            }

            do_action('cuar/core/page/after-' . $part . '?slug=' . $slug);
            do_action('cuar/core/page/after-' . $part);

            return !empty($template);
        }

        /*------- SIDEBAR HANDLING --------------------------------------------------------------------------------------*/

        public function has_page_sidebar()
        {
            if ($this->is_sidebar_enabled)
            {
                if ( !is_active_sidebar($this->get_sidebar_id()) && !$this->has_default_sidebar)
                {
                    return false;
                }

                return true;
            }

            return false;
        }

        public function get_default_widget_args($id)
        {
            $fake_id = $id . '-' . rand();
            $fake_class = 'widget_' . $id;
            return array(
                'before_widget' => sprintf('<aside id="%1$s" class="cuar-widget cuar-%2$s panel">', $fake_id, $fake_class),
                'after_widget'  => "</aside>",
                'before_title'  => '<div class="cuar-widget-title panel-heading">',
                'after_title'   => '</div>',
            );
        }

        public function get_sidebar_id()
        {
            return $this->get_slug() . '-sidebar';
        }

        // Override this function to output some default widgets when sidebar is empty
        protected function print_default_widgets()
        {
        }

        protected function enable_sidebar($widget_classes = array(), $has_default_sidebar = false)
        {
            $page_slug = $this->get_slug();
            $this->is_sidebar_enabled = apply_filters('cuar/core/page/enable-sidebar?slug=' . $page_slug, true);
            $this->has_default_sidebar = apply_filters('cuar/core/page/enable-default-sidebar?slug=' . $page_slug, $has_default_sidebar);

            // Register widget classes
            foreach ($widget_classes as $w)
            {
                add_action('widgets_init', create_function('', 'return register_widget("' . $w . '");'));
            }

            // Register the sidebar
            $this->register_sidebar($this->get_sidebar_id(), sprintf(__('WPCA - %s', 'cuar'), $this->get_title()));
        }

        protected function register_sidebar($id, $name)
        {
            register_sidebar(array(
                'id'            => $id,
                'name'          => $name,
                'before_widget' => '<aside id="%1$s" class="cuar-widget cuar-%2$s panel">',
                'after_widget'  => "</aside>",
                'before_title'  => '<div class="cuar-widget-title panel-heading">',
                'after_title'   => '</div>',
            ));
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

        public function add_body_class($classes = array())
        {
            $cp_addon = $this->plugin->get_addon('customer-pages');
            if ($cp_addon->is_customer_area_page() && !in_array('customer-area', $classes))
            {
                $classes[] = 'customer-area';
            }

            return $classes;
        }

        protected function set_page_shortcode($shortcode_name, $shortcode_params = array())
        {
            $this->shortcode = new CUAR_AddOnPageShortcode($this, $shortcode_name, $shortcode_params);
        }

        public function get_page_shortcode()
        {
            return $this->shortcode == null ? '' : $this->shortcode->get_shortcode_name();
        }

        protected $child_pages = null;

        /** @var int order for the page */
        protected $page_priority = 10;

        /** @var array describes the page */
        protected $page_description = null;

        /** @var CUAR_AddOnPageShortcode shortcode that displays the page */
        protected $shortcode = null;

        /** @var boolean did we enable a sidebar for this page? */
        protected $is_sidebar_enabled = false;
        protected $has_default_sidebar = false;

        protected $page_id = -1;
        protected $page_url = '';
    }

endif; // CUAR_AbstractPageAddOn
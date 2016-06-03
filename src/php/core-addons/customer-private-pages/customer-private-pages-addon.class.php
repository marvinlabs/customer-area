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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-content-page.class.php');

require_once(dirname(__FILE__) . '/widget-private-page-authors.class.php');
require_once(dirname(__FILE__) . '/widget-private-page-categories.class.php');
require_once(dirname(__FILE__) . '/widget-private-page-dates.class.php');
require_once(dirname(__FILE__) . '/widget-private-pages.class.php');

if ( !class_exists('CUAR_CustomerPrivatePagesAddOn')) :

    /**
     * Add-on to put private pages in the customer area
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_CustomerPrivatePagesAddOn extends CUAR_AbstractContentPageAddOn
    {

        public function __construct()
        {
            parent::__construct('customer-private-pages', '4.0.0');

            $this->set_page_parameters(610, array(
                    'slug'                => 'customer-private-pages',
                    'parent_slug'         => 'customer-private-pages-home',
                    'friendly_post_type'  => 'cuar_private_page',
                    'friendly_taxonomy'   => 'cuar_private_page_category',
                    'required_capability' => 'cuar_view_pages'
                )
            );

            $this->set_page_shortcode('customer-area-private-pages');
        }

        public function get_label()
        {
            return __('Private Pages - Owned', 'cuar');
        }

        public function get_title()
        {
            return __('My pages', 'cuar');
        }

        public function get_hint()
        {
            return __('Page to list the pages a customer owns.', 'cuar');
        }

        public function run_addon($plugin)
        {
            $this->pp_addon = $plugin->get_addon('private-pages');

            parent::run_addon($plugin);

            // This page can also list archive for private content
            $this->enable_content_archives_permalinks();
            $this->enable_single_private_content_permalinks();

            // Widget area for our sidebar
            if ($this->pp_addon->is_enabled())
            {
                $this->enable_sidebar(array(
                    'CUAR_PrivatePageCategoriesWidget', 'CUAR_PrivatePageDatesWidget', 'CUAR_PrivatePagesWidget', 'CUAR_PrivatePageAuthorsWidget'
                ), true);
            }

            if (is_admin())
            {
                $this->enable_settings('cuar_private_pages');
            }
        }

        public function get_page_addon_path()
        {
            return CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages';
        }

        protected function get_author_archive_page_subtitle($author_id)
        {
            if ($author_id == get_current_user_id())
            {
                return __('Pages you created', 'cuar');
            }

            $author = get_userdata($author_id);

            return sprintf(__('Pages created by %1$s', 'cuar'), $author->display_name);
        }

        protected function get_category_archive_page_subtitle($category)
        {
            return sprintf(__('Pages under %1$s', 'cuar'), $category->name);
        }

        protected function get_date_archive_page_subtitle($year, $month = 0)
        {
            if (isset($month) && ((int)($month) > 0))
            {
                $month_name = date_i18n("F", mktime(0, 0, 0, (int)$month, 10));
                $page_subtitle = sprintf(__('Pages published in %2$s %1$s', 'cuar'), $year, $month_name);
            }
            else
            {
                $page_subtitle = sprintf(__('Pages published in %1$s', 'cuar'), $year);
            }

            return $page_subtitle;
        }

        protected function get_default_page_subtitle()
        {
            return __('Recent Pages', 'cuar');
        }

        protected function get_default_dashboard_block_title()
        {
            return __('Recent Pages', 'cuar');
        }

        protected function print_default_widgets()
        {
            $w = new CUAR_PrivatePageCategoriesWidget();
            $w->widget($this->get_default_widget_args($w->id_base), array(
                'title' => __('Categories', 'cuar'),
            ));

            $w = new CUAR_PrivatePageDatesWidget();
            $w->widget($this->get_default_widget_args($w->id_base), array(
                'title' => __('Archives', 'cuar'),
            ));

            $w = new CUAR_PrivatePageAuthorsWidget();
            $w->widget($this->get_default_widget_args($w->id_base), array(
                'title' => __('Created By', 'cuar'),
            ));
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function is_show_in_dashboard_enabled()
        {
            return $this->pp_addon->is_enabled() && parent::is_show_in_dashboard_enabled();
        }

        public function is_show_in_single_post_footer_enabled()
        {
            return $this->pp_addon->is_enabled() && parent::is_show_in_single_post_footer_enabled();
        }

        /** @var CUAR_PrivatePageAddOn */
        private $pp_addon;
    }

// Make sure the addon is loaded - THIS IS DONE IN THE PRIVATE PAGE ADD-ON ITSELF
    new CUAR_CustomerPrivatePagesAddOn();

endif; // if (!class_exists('CUAR_CustomerPrivatePagesAddOn')) 

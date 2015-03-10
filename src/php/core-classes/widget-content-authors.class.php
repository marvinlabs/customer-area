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

if ( !class_exists('CUAR_ContentAuthorsWidget')) :

    /**
     * Widget to show the authors of content
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_ContentAuthorsWidget extends WP_Widget
    {

        /**
         * Register widget with WordPress.
         */
        function __construct($id_base, $name, $widget_options = array(), $control_options = array())
        {
            parent::__construct($id_base, $name, $widget_options, $control_options);
        }

        protected abstract function get_link($author_id);

        protected abstract function get_post_type();

        protected abstract function get_default_title();

        /**
         * Front-end display of widget.
         *
         * @see WP_Widget::widget()
         *
         * @param array $args     Widget arguments.
         * @param array $instance Saved values from database.
         */
        public function widget($args, $instance)
        {
            // Don't output anything if we don't have any content or if the user is a guest
            if ( !is_user_logged_in())
            {
                return;
            }

            $authors = $this->get_authors();
            if (count($authors) <= 0)
            {
                return;
            }

            echo $args['before_widget'];

            $title = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '');
            if ( !empty($title))
            {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            $this->print_author_list($authors, true);

            echo $args['after_widget'];
        }

        protected function get_authors()
        {
            global $wpdb;

            $current_user_id = get_current_user_id();

            // TODO SETUP SOME CACHING MECHANISM


            // Get the content current user owns
            $cuar_plugin = CUAR_Plugin::get_instance();
            $po_addon = $cuar_plugin->get_addon('post-owner');

            $args = array(
                'post_type'      => $this->get_post_type(),
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => $po_addon->get_meta_query_post_owned_by(get_current_user_id())
            );
            $args = apply_filters('cuar/core/widget/query-args?widget-id=' . $this->id_base, $args);
            $posts = get_posts($args);

            $out = array();
            foreach ($posts as $p)
            {
                $author = get_userdata($p->post_author);
                $out[$p->post_author] = $author->display_name;
            }

            // Get the content current user has created
            $args = array(
                'post_type'      => $this->get_post_type(),
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'author'         => $current_user_id
            );
            $args = apply_filters('cuar/core/widget/query-args?widget-id=' . $this->id_base, $args);
            $posts = get_posts($args);

            if (count($posts) > 0)
            {
                $out[$current_user_id] = __('You', 'cuar');
            }

            // Sort by value (name)
            natcasesort($out);

            // If current user if in that array, we place him in first position
            $key = array_search($current_user_id, $out);
            if (isset($out[$current_user_id]))
            {
                unset($out[$current_user_id]);
                $out = array($current_user_id => __('You', 'cuar')) + $out;
            }

            return $out;
        }

        protected function print_author_list($authors)
        {
            echo '<ul>';

            foreach ($authors as $id => $display_name)
            {
                echo '<li>';

                $link = $this->get_link($id);

                printf('<a href="%1$s" title="%3$s">%2$s</a>',
                    $link,
                    $display_name,
                    sprintf(esc_attr__('Show all content published by %s', 'cuar'), $display_name)
                );

                echo '</li>';
            }

            echo '</ul>';
        }

        /**
         * Back-end widget form.
         *
         * @see WP_Widget::form()
         *
         * @param array $instance Previously saved values from database.
         */
        public function form($instance)
        {
            if (isset($instance['title']))
            {
                $title = $instance['title'];
            }
            else
            {
                $title = $this->get_default_title();
            }
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cuar'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" type="text"
                       value="<?php echo esc_attr($title); ?>">
            </p>
        <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         *
         * @see WP_Widget::update()
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values from database.
         *
         * @return array Updated safe values to be saved.
         */
        public function update($new_instance, $old_instance)
        {
            $instance = array();
            $instance['title'] = ( !empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

            return $instance;
        }
    }

endif; // if (!class_exists('CUAR_ContentAuthorsWidget')) 

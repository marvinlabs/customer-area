<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com)

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

if ( !class_exists('CUAR_CustomPost')) :

    /**
     * A wrapper to augment the WP_Post object.
     */
    class CUAR_CustomPost
    {

        /** @var int The custom post ID. */
        public $ID;

        /** @var WP_Post The actual post object. */
        public $post;

        /**
         * Constructor
         *
         * @param WP_Post|int $custom_post
         * @param boolean     $load_post If we supply an int as the first argument, shall we load the post object?
         */
        public function __construct($custom_post, $load_post = true)
        {
            if ($custom_post instanceof WP_Post)
            {
                $this->ID = absint($custom_post->ID);
                $this->post = $custom_post;
            }
            else
            {
                $this->ID = absint($custom_post);
                $this->post = null;
                if ($load_post)
                {
                    $this->get_post();
                }
            }
        }

        /**
         * __isset function.
         *
         * @param mixed $key
         *
         * @return bool
         */
        public function __isset($key)
        {
            $meta_key = 'cuar_' . $key;

            return metadata_exists('post', $this->ID, $meta_key);
        }

        /**
         * __get function.
         *
         * @param mixed $key
         *
         * @return mixed
         */
        public function __get($key)
        {
            $meta_key = 'cuar_' . $key;
            $value = get_post_meta($this->ID, $meta_key, true);

            return $value ? $value : $this->get_default_meta_value($key);
        }

        /**
         * Get the default value for a metadata key
         */
        protected function get_default_meta_value($key)
        {
            return null;
        }

        /**
         * Get the post data.
         *
         * @return object
         */
        public function get_post()
        {
            if ($this->post == null)
            {
                $this->post = get_post($this->ID);
            }

            return $this->post;
        }
    }

endif; // if (!class_exists('CUAR_CustomPost'))
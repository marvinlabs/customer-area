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

if ( !class_exists('CUAR_CustomTaxonomy')) :

    /**
     * A base class for our custom taxonomies
     */
    class CUAR_CustomTaxonomy
    {

        protected $tax_id;

        /**
         * __construct function.
         */
        public function __construct($tax_id)
        {
            $this->tax_id = $tax_id;
        }

        /**
         * @param       $post_id
         * @param array $args
         *
         * @return array
         */
        public function get_post_terms($post_id, $args = array('fields' => 'all'))
        {
            $terms = wp_get_post_terms($post_id, $this->tax_id, $args);

            return $terms;
        }

        /**
         * @param       $term_id
         *
         * @return mixed|null|WP_Error
         */
        public function get_term($term_id)
        {
            $term = get_term($term_id, $this->tax_id);

            return $term;
        }
    }

endif; // if (!class_exists('CUAR_CustomTaxonomy'))
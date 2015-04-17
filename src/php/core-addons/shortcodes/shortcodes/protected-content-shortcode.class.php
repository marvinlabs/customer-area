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

/**
 * A shortcode to display protected content assigned or authored by the current user
 */
class CUAR_ProtectedContentShortcode extends CUAR_Shortcode
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('customer-area-protected-content');
    }

    /**
     * @return array An associative array with all the default parameter values
     */
    protected function get_default_param_values()
    {
        return array(
            // Required
            'type'      => '',

            // Optional
            'mode'      => 'owned',
            'max_items' => -1,
            'layout'    => 'list'
        );
    }

    /**
     * @return array A list of parameter keys that are required
     */
    protected function get_required_params()
    {
        return array('type');
    }

    /**
     * Actually process the shortcode (output stuff, ...)
     *
     * @param array  $params  The parameters
     * @param string $content The content between the shortcode tags
     *
     * @return string The shortcode final output
     */
    public function process_shortcode($params, $content)
    {
        $plugin = CUAR_Plugin::get_instance();
        $current_user_id = get_current_user_id();
        $layout = $params['layout'];

        // Build the query
        $args = array(
            'post_type'      => $params['type'],
            'posts_per_page' => $params['max_items'],
        );

        // Ownership
        /** @var CUAR_PostOwnerAddOn $po_addon */
        $po_addon =$plugin->get_addon('post-owner');
        $args['meta_query'] =  $po_addon->get_meta_query_post_owned_by($current_user_id);

        $query = new WP_Query(apply_filters('cuar/shortcodes/protected-content/query-args', $args, $params));

        // Determine the template files to use
        $template_root = CUAR_INCLUDES_DIR . '/core-addons/shortcodes';
        $loop_template = $plugin->get_template_file_path(
            $template_root,
            'protected-content-shortcode-loop-' . $layout . '.template.php',
            'templates',
            'protected-content-shortcode-loop-default.template.php');
        $before_items_template = $plugin->get_template_file_path(
            $template_root,
            'protected-content-shortcode-layout-' . $layout . '-before.template.php',
            'templates',
            'protected-content-shortcode-layout-default-before.template.php');
        $after_items_template = $plugin->get_template_file_path(
            $template_root,
            'protected-content-shortcode-layout-' . $layout . '-after.template.php',
            'templates',
            'protected-content-shortcode-layout-default-after.template.php');
        $item_template = $plugin->get_template_file_path(
            $template_root,
            'protected-content-shortcode-layout-' . $layout . '-item.template.php',
            'templates',
            'protected-content-shortcode-layout-default-item.template.php');

        ob_start();

        include($loop_template);

        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }
}

new CUAR_ProtectedContentShortcode();
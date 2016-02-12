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
            'type'           => '',

            // Optional
            'title'          => '',
            'show'           => 'owned',
            'max_items'      => -1,
            'layout'         => 'default',
            'taxonomy'       => '',
            'terms'          => '',
            'terms_operator' => 'IN',
            'terms_field'    => 'term_id'
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
        // Do not consider guests
        if ( !is_user_logged_in())
        {
            return '';
        }

        $plugin = CUAR_Plugin::get_instance();
        $current_user_id = get_current_user_id();

        $layout = $params['layout'];
        $type = $params['type'];
        $title = $params['title'];

        $content_types = $plugin->get_content_post_types();
        $container_types = $plugin->get_container_post_types();

        if (empty($type) || !(in_array($type, $content_types) || in_array($type, $container_types)))
        {
            return sprintf(__('The parameter %2$s of shortcode <code>[%1$s]</code> has an invalid value: <code>%3$s</code>', 'cuar'),
                $this->name, 'type', $type);
        }

        // Build the query
        $args = array(
            'post_type'      => $type,
            'posts_per_page' => $params['max_items'],
        );

        // Ownership / authorship
        switch ($params['show'])
        {
            case 'owned':
                if (in_array($type, $content_types))
                {
                    /** @var CUAR_PostOwnerAddOn $po_addon */
                    $po_addon = $plugin->get_addon('post-owner');
                    $args['meta_query'] = $po_addon->get_meta_query_post_owned_by($current_user_id);
                }
                else if (in_array($type, $container_types))
                {
                    /** @var CUAR_ContainerOwnerAddOn $co_addon */
                    $co_addon = $plugin->get_addon('container-owner');
                    $args['meta_query'] = $co_addon->get_meta_query_containers_owned_by($current_user_id);
                }

                break;

            case 'authored':
                $args['author'] = $current_user_id;
                break;

            default:
                return sprintf(__('The parameter %2$s of shortcode <code>[%1$s]</code> has an invalid value: <code>%3$s</code>', 'cuar'),
                    $this->name, 'show', $params['mode']);
        }

        // Taxonomy
        $terms = explode(',', $params['terms']);
        if ( !empty($params['taxonomy']) && !empty($terms))
        {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $params['taxonomy'],
                    'field'    => $params['terms_field'],
                    'terms'    => $terms,
                    'operator' => $params['terms_operator'],
                )
            );
        }

        $query = new WP_Query(apply_filters('cuar/shortcodes/protected-content/query-args', $args, $params));

        // Determine the template files to use
        $template_root = CUAR_INCLUDES_DIR . '/core-addons/shortcodes';
        if ($query->have_posts())
        {
            $loop_template = $plugin->get_template_file_path(
                $template_root,
                array(
                    'protected-content-shortcode-loop-' . $layout . '-' . $type . '.template.php',
                    'protected-content-shortcode-loop-' . $layout . '.template.php',
                    'protected-content-shortcode-loop-' . $type . '.template.php',
                    'protected-content-shortcode-loop-default.template.php'
                ));
        }
        else
        {
            $loop_template = $plugin->get_template_file_path(
                $template_root,
                array(
                    'protected-content-shortcode-empty-' . $layout . '-' . $type . '.template.php',
                    'protected-content-shortcode-empty-' . $layout . '.template.php',
                    'protected-content-shortcode-empty-' . $type . '.template.php',
                    'protected-content-shortcode-empty-default.template.php'
                ));
        }
        $before_items_template = $plugin->get_template_file_path(
            $template_root,
            array(
                'protected-content-shortcode-layout-' . $layout . '-' . $type . '-before.template.php',
                'protected-content-shortcode-layout-' . $layout . '-before.template.php',
                'protected-content-shortcode-layout-' . $type . '-before.template.php',
                'protected-content-shortcode-layout-default-before.template.php'
            ));
        $after_items_template = $plugin->get_template_file_path(
            $template_root,
            array(
                'protected-content-shortcode-layout-' . $layout . '-' . $type . '-after.template.php',
                'protected-content-shortcode-layout-' . $layout . '-after.template.php',
                'protected-content-shortcode-layout-' . $type . '-after.template.php',
                'protected-content-shortcode-layout-default-after.template.php'
            ));
        $item_template = $plugin->get_template_file_path(
            $template_root,
            array(
                'protected-content-shortcode-layout-' . $layout . '-' . $type . '-item.template.php',
                'protected-content-shortcode-layout-' . $layout . '-item.template.php',
                'protected-content-shortcode-layout-' . $type . '-item.template.php',
                'protected-content-shortcode-layout-default-item.template.php'
            ));

        ob_start();

        include($loop_template);

        $out = ob_get_contents();
        ob_end_clean();

        wp_reset_query();

        return $out;
    }
}

new CUAR_ProtectedContentShortcode();
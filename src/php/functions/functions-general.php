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
 * Get the plugin
 *
 * @return CUAR_Plugin The unique instance of the WP Customer Area plugin
 */
function cuar()
{
    return CUAR_Plugin::get_instance();
}

/**
 * @param string $id The ID of the add-on to find
 *
 * @return CUAR_AddOn The addon you are looking for
 */
function cuar_addon($id)
{
    return cuar()->get_addon($id);
}

/**
 * Print the customer area menu
 *
 * @param bool   $wrap Wrap the menu inside a div element
 * @param string $wrapper_class
 */
function cuar_the_customer_area_menu($wrap = true, $wrapper_class = "cuar-css-wrapper")
{
    if ($wrap) echo '<div class="' . esc_attr($wrapper_class) . '">';

    /** @var CUAR_CustomerPagesAddOn $cp_addon */
    $cp_addon = cuar_addon('customer-pages');
    echo $cp_addon->get_main_navigation_menu();

    if ($wrap) echo '</div>';
}

/**
 * Print the customer area menu
 */
function cuar_the_contextual_toolbar()
{
    /** @var CUAR_CustomerPagesAddOn $cp_addon */
    $cp_addon = cuar_addon('customer-pages');

    echo $cp_addon->get_contextual_toolbar();
}

/**
 * Returns true if we are currently viewing one of the Customer Area pages
 *
 * @param int         $post_id   The page ID to test
 * @param string|null $page_slug The slug of the page to check (null to only check if we are on a WP Customer Area page)
 *
 * @return bool
 */
function cuar_is_customer_area_page($post_id = 0, $page_slug = null)
{
    /** @var CUAR_CustomerPagesAddOn $cp_addon */
    $cp_addon = cuar_addon('customer-pages');

    if ($page_slug == null) {
        return $cp_addon->is_customer_area_page($post_id);
    }

    /** @var CUAR_AbstractPageAddOn $page */
    $page = $cp_addon->get_customer_area_page_from_id($post_id);

    // Not found/not a page
    if ($page == false) return false;

    // Found, no need to match slug
    if ($page_slug == null) return true;

    // Found, check slug too
    return $page->get_slug() == $page_slug;
}

/**
 * Returns true if the type of the given post is a private post type of Customer Area
 *
 * @param WP_Post|int $post The post/post ID to test
 *
 * @return boolean
 */
function cuar_is_customer_area_private_content($post = null)
{
    $cuar_plugin = cuar();
    $private_types = $cuar_plugin->get_private_post_types();

    return in_array(get_post_type($post), $private_types);
}

/**
 * Outputs a loading indicator
 *
 * @param bool $is_visible
 */
function cuar_ajax_loading($is_visible = false)
{
    ?>
    <div class="ajax-loading" style="display: <?php echo $is_visible == false ? 'none' : 'block'; ?>;">
        <div class="circle left"></div>
        <div class="circle middle"></div>
        <div class="circle right"></div>
        <div class="clearfix"></div>
    </div>
    <?php
}

/**
 * Print an address
 *
 * @param        $address
 * @param        $address_id
 * @param string $address_label
 * @param string $template_prefix
 */
function cuar_print_address($address, $address_id, $address_label = '', $template_prefix = '')
{
    /** @var CUAR_AddressesAddOn $ad_addon */
    $ad_addon = cuar_addon('address-manager');
    $ad_addon->print_address($address, $address_id, $address_label, $template_prefix);
}

/**
 * Get the WP editor settings allowing to override them
 *
 * @param array $extra_settings
 *
 * @return array
 *
 * @deprecated Since we use summernote now
 */
function cuar_wp_editor_settings($extra_settings = array())
{
    $defaults = cuar()->get_default_wp_editor_settings();

    return array_merge($defaults, $extra_settings);
}
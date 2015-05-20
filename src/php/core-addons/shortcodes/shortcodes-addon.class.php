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

/**
 * Add-on to load additional shortcodes useful in Customer Area
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_ShortcodesAddOn extends CUAR_AddOn
{

    public function __construct()
    {
        parent::__construct('shortcodes', '6.1.0');
    }

    public function get_addon_name()
    {
        return __('Shortcodes', 'cuar');
    }

    public function run_addon($plugin)
    {
        include(CUAR_INCLUDES_DIR . '/core-addons/shortcodes/shortcodes/menu-shortcode.class.php');
        include(CUAR_INCLUDES_DIR . '/core-addons/shortcodes/shortcodes/protected-content-shortcode.class.php');
    }
}

// Make sure the addon is loaded
new CUAR_ShortcodesAddOn();

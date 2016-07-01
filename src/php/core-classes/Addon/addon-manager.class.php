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
 * The class that will handle addon version management
 */
class CUAR_AddonManager
{
    /** @var CUAR_MessageCenter */
    private $message_center;

    /** @var array */
    private $registered_addons = array();

    /** @var array */
    private $commercial_addons = array();

    /**
     * CUAR_AddonManager constructor.
     *
     * @param CUAR_MessageCenter $messageCenter
     */
    public function __construct(CUAR_MessageCenter $messageCenter)
    {
        $this->message_center = $messageCenter;
    }

    /**
     * Register the hooks to output the messages in the admin area
     */
    public function register_hooks()
    {
        if (is_admin()) {
            add_action('cuar/core/activation/run-deferred-action?action_id=check-addon-versions', array(&$this, 'check_addons_recommended_versions'));
        }
    }

    /**
     * Get add-on
     */
    public function get_addon($id)
    {
        return isset($this->registered_addons[$id]) ? $this->registered_addons[$id] : null;
    }

    /**
     * Register an add-on in the plugin
     *
     * @param CUAR_AddOn $addon
     */
    public function register_addon($addon)
    {
        $this->registered_addons[$addon->addon_id] = $addon;
    }

    /**
     * Get registered add-ons
     */
    public function get_registered_addons()
    {
        return $this->registered_addons;
    }

    public function get_commercial_addons()
    {
        return $this->commercial_addons;
    }

    public function tag_addon_as_commercial($addon_id)
    {
        $this->commercial_addons[$addon_id] = $this->get_addon($addon_id);
    }

    public function has_commercial_addons()
    {
        return !empty($this->commercial_addons);
    }

    public function get_version_mismatches()
    {
        $mismatches = array();

        $plugin_version = cuar()->get_version();
        $version_matrix = json_decode(file_get_contents(CUAR_PLUGIN_DIR . '/versions.json'), true);

        if ( !isset($version_matrix[$plugin_version])) return array();

        $recommended_versions = $version_matrix[$plugin_version];

        // List plugins
        $all_plugins = get_plugins();
        foreach ($all_plugins as $id => $plugin_info) {
            $plugin_name = explode('/', $id);
            $plugin_name = $plugin_name[0];

            if ( !isset($recommended_versions[$plugin_name])) continue;

            if (version_compare($plugin_info['Version'], $recommended_versions[$plugin_name], '<')) {
                $mismatches[] = array(
                    'name'        => $plugin_info['Name'],
                    'current'     => $plugin_info['Version'],
                    'recommended' => $recommended_versions[$plugin_name],
                );
            }
        }

        return $mismatches;
    }

    /**
     * Shows a compatibility warning
     */
    public function check_addons_recommended_versions()
    {
        $this->message_center->remove_warning('outdated-plugin-version');
        $this->message_center->remove_warning('version-mismatch');

        $mismatches = $this->get_version_mismatches();

        if (!empty($mismatches)) {
            $this->message_center->add_warning('version-mismatch',
                __('The plugin and the add-ons you have installed may be incompatible.', 'cuar'),
                10);
        }
    }

}
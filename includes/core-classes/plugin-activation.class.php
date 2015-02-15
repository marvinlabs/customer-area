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


class CUAR_PluginActivation implements CUAR_PluginActivationDelegate
{

    /**
     * Called when the plugin is activated. You should not do much work here. Instead, this is a place to
     * queue deferred actions that will be executed on next page refresh.
     */
    public function on_activate()
    {
        // Reset all warnings, they should all be started new
        CUAR_MessageCenter::reset_warnings();

        // Schedule checking for updating/setting up the plugin
        CUAR_PluginActivationManager::schedule_deferred_action('check-plugin-version', 10);

        // Schedule a check of the template files
        CUAR_PluginActivationManager::schedule_deferred_action('check-template-files', 20);

        // Schedule flushing all rewrite rules
        CUAR_PluginActivationManager::schedule_deferred_action('flush-rewrite-rules', 20);

        // Schedule a check of the permalinks setting in WordPress
        CUAR_PluginActivationManager::schedule_deferred_action('check-permalink-settings', 40);
    }

    /**
     * Called when the plugin is deactivated
     */
    public function on_deactivate()
    {
    }
}
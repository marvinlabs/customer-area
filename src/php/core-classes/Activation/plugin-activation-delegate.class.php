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
 * Place to implement the (de)activation logic specific to a plugin
 */
interface CUAR_PluginActivationDelegate
{
    /**
     * Called when the plugin is activated. You should not do much work here. Instead, this is a place to
     * queue deferred actions that will be executed on next page refresh.
     */
    public function on_activate();

    /**
     * Called when the plugin is deactivated
     */
    public function on_deactivate();
}
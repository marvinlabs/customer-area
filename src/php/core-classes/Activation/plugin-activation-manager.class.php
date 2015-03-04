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
 * Class CUAR_PluginActivationManager
 *
 * @since 6.0.0
 */
class CUAR_PluginActivationManager
{

    private static $OPTION_DEFERRED_ACTIONS = 'cuar_deferred_actions';

    /** @var CUAR_PluginActivationDelegate */
    private static $DELEGATE = null;

    /**
     * Constructor
     */
    function __construct()
    {
    }

    /**
     * Register the WP hooks to execute the deferred actions
     */
    public function register_hooks()
    {
        add_action('admin_init', array(&$this, 'run_deferred_actions'), 5);
    }

    /**
     * Run all the deferred actions. This in fact executes the hook named after the action ID:
     * 'cuar/core/activation/run-deferred-action?action_id=' . $action_id
     */
    public function run_deferred_actions()
    {
        $actions = $this->get_deferred_actions(true);
        foreach ($actions as $action_id => $action) {
            do_action('cuar/core/activation/run-deferred-action?action_id=' . $action_id);
        }
        self::reset_deferred_actions();

        do_action('cuar/core/activation/after-deferred-actions');
    }

    /**
     * @param CUAR_PluginActivationDelegate $delegate
     */
    public static function set_delegate($delegate)
    {
        self::$DELEGATE = $delegate;
    }

    /**
     * The activation callback for the plugin
     */
    public static function on_activate()
    {
        if (self::$DELEGATE!=null) {
            self::$DELEGATE->on_activate();
        }
    }

    /**
     * The deactivation callback for the plugin
     */
    public static function on_deactivate()
    {
        if (self::$DELEGATE!=null) {
            self::$DELEGATE->on_deactivate();
        }

        // Reset our list of scheduled actions
        self::reset_deferred_actions();
    }

    /**
     * Schedule an action to be executed once at next page load
     * @param string $action_id
     * @param int $priority
     */
    public static function schedule_deferred_action($action_id, $priority)
    {
        $actions = self::get_deferred_actions();
        $actions[$action_id] = array(
            'id' => $action_id,
            'priority' => $priority
        );
        self::save_deferred_actions($actions);
    }

    /**
     * Get the list of actions to be executed next time on admin_init
     * @param bool $sort Sort the actions by priority or not?
     * @return array the list of actions
     */
    protected static function get_deferred_actions($sort = false)
    {
        $actions = get_option(self::$OPTION_DEFERRED_ACTIONS, array());
        if ($sort) {
            uasort($actions, array('CUAR_PluginActivationManager', 'sort_actions_by_priority'));
        }
        return $actions;
    }

    /**
     * Save the list of actions to be executed next time on admin_init
     * @param array $actions the list of actions
     */
    protected static function save_deferred_actions($actions)
    {
        update_option(self::$OPTION_DEFERRED_ACTIONS, $actions);
    }

    /**
     * Clear the list of actions to be executed next time on admin_init
     */
    protected static function reset_deferred_actions()
    {
        update_option(self::$OPTION_DEFERRED_ACTIONS, array());
    }

    /**
     * Callback for the uasort function that sorts the messages according to their priority
     *
     * @param $a
     * @param $b
     * @return int
     */
    public static function sort_actions_by_priority($a, $b)
    {
        if (isset($a['priority']) && isset($b['priority'])) {
            if ($a['priority'] == $b['priority']) return 0;
            else if ($a['priority'] < $b['priority']) return -1;
            else return 1;
        } else if (isset($a['priority'])) {
            return 1;
        } else if (isset($b['priority'])) {
            return -1;
        }

        return 0;
    }
} 
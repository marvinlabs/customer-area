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
 * A class to handle messages and admin notices
 */
class CUAR_MessageCenter
{

    private static $OPTION_WARNINGS = 'cuar/core/status/warning-messages';
    private static $OPTION_IGNORED_WARNINGS = 'cuar/core/status/ignored-warnings';

    private $warning_free_pages;

    /**
     * Constructor
     * @param array $warning_free_pages
     */
    function __construct($warning_free_pages=array())
    {
        $this->warning_free_pages = $warning_free_pages;
    }

    /**
     * Register the hooks to output the messages in the admin area
     */
    public function register_hooks()
    {
        if (is_admin()) {
            add_action('admin_notices', array(&$this, 'print_warning_messages'));
        }
    }

    /**
     * Remove all warnings from database
     */
    public static function reset_warnings()
    {
        update_option(self::$OPTION_WARNINGS, array());
    }

    /**
     * Add a message to tell the user that the plugin may not be working properly
     *
     * @param string $message_id
     * @param string $message
     * @param int $priority
     */
    public function add_warning($message_id, $message, $priority)
    {
        $warnings = $this->get_warnings(false);

        if (!empty($message)) {
            $warnings[$message_id] = array(
                'message' => $message,
                'priority' => $priority
            );
        } else if (isset($warnings[$message_id])) {
            unset($warnings[$message_id]);
        }

        $this->save_warnings($warnings);
    }

    /**
     * Cancel a warning message
     * @param string $message_id
     */
    public function remove_warning($message_id)
    {
        $warnings = $this->get_warnings(false);
        unset($warnings[$message_id]);
        $this->save_warnings($warnings);
    }

    /**
     * Returns true if a warning message is queued
     *
     * @param string $message_id
     * @return bool
     */
    public function is_warning_registered($message_id)
    {
        $warnings = $this->get_warnings(false);
        return (isset($warnings[$message_id]) && !empty($warnings[$message_id]));
    }

    /**
     * Returns true if a warning message is supposed to be ignored
     *
     * @param string $message_id
     * @return bool
     */
    public function is_warning_ignored($message_id)
    {
        $warnings = $this->get_ignored_warnings();
        return isset($warnings[$message_id]) && $warnings[$message_id] == true;
    }

    /**
     * Ignore a warning message until the next plugin activation
     *
     * @param string $message_id
     */
    public function ignore_warning($message_id)
    {
        $warnings = $this->get_ignored_warnings();
        $warnings[$message_id] = true;
        $this->save_ignored_warnings($warnings);
    }

    /**
     * Clear the list of messages to ignore
     */
    public function reset_ignored_warnings()
    {
        $this->save_ignored_warnings(array());
    }

    /**
     * Prints the messages in the admin notices area
     */
    public function print_warning_messages()
    {
        if (isset($_GET['page']) && in_array($_GET['page'], $this->warning_free_pages)) return;

        $warnings = $this->get_warnings();
        if (empty($warnings)) return;

        echo '<div id="message" class="error cuar-error">';

        echo '<p>' . __('<strong>WP Customer Area</strong> has detected some potential issues: ', 'cuar') . '</p>';

        echo '<ol class="cuar-with-bullets">';
        foreach ($warnings as $id => $message) {
            echo '<li>' . $message['message'] . '</li>';
        }
        echo '</ol>';

        echo '<p><a href="' . admin_url('admin.php?page=wpca-status&tab=needs-attention') . '" class="button button-primary">' . __('Fix these potential issues', 'cuar') . ' &raquo;</a></p>';
        echo '</div>';
    }

    /**
     * Returns the list of warning messages
     *
     * @param bool $sort
     * @return array
     */
    public function get_warnings($sort = true)
    {
        $messages = get_option(self::$OPTION_WARNINGS, array());
        if ($sort) {
            uasort($messages, array('CUAR_MessageCenter', 'sort_attention_needed_messages'));
        }
        return $messages;
    }

    /**
     * Persist all warnings to database
     *
     * @param array $warnings
     */
    protected function save_warnings($warnings)
    {
        update_option(self::$OPTION_WARNINGS, $warnings);
    }

    /**
     * Returns the list of ignored warning messages
     *
     * @return array
     */
    protected function get_ignored_warnings()
    {
        $messages = get_option(self::$OPTION_IGNORED_WARNINGS, array());
        return $messages;
    }

    /**
     * Persist all warnings to database
     *
     * @param array $warnings
     */
    protected function save_ignored_warnings($warnings)
    {
        update_option(self::$OPTION_IGNORED_WARNINGS, $warnings);
    }

    /**
     * Callback for the uasort function that sorts the messages according to their priority
     *
     * @param $a
     * @param $b
     * @return int
     */
    public static function sort_attention_needed_messages($a, $b)
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
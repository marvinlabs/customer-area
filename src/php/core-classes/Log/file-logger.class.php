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
 * Allows log files to be written to for debugging purposes
 */
class CUAR_FileLogger
{
    /** @var array Stores open file _handles */
    private $_handles;

    /**
     * Constructor for the logger.
     */
    public function __construct()
    {
        $this->_handles = array();
    }


    /**
     * Destructor.
     */
    public function __destruct()
    {
        foreach ($this->_handles as $handle) {
            @fclose($handle);
        }
    }

    /**
     * Get a log file path.
     *
     * @param string $handle name.
     *
     * @return string the log file path.
     */
    public static function get_log_file_path($handle)
    {
        $dir = WP_CONTENT_DIR . '/customer-area/logs/';
        if ( !file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir . sanitize_file_name($handle . '-' . wp_hash($handle)) . '.log';
    }


    /**
     * Open log file for writing.
     *
     * @param mixed $handle
     *
     * @return bool success
     */
    private function open($handle)
    {
        if (isset($this->_handles[$handle])) return true;

        if ($this->_handles[$handle] = @fopen(self::get_log_file_path($handle), 'a')) return true;

        return false;
    }


    /**
     * Add a log entry to chosen file.
     *
     * @param string $handle
     * @param string $message
     */
    public function add($handle, $message)
    {
        if ($this->open($handle) && is_resource($this->_handles[$handle])) {
            $time = date_i18n('m-d-Y @ H:i:s -'); // Grab Time
            @fwrite($this->_handles[$handle], $time . " " . $message . "\n");
        }
    }


    /**
     * Clear entries from chosen file.
     *
     * @param mixed $handle
     */
    public function clear($handle)
    {
        if ($this->open($handle) && is_resource($this->_handles[$handle])) {
            @ftruncate($this->_handles[$handle], 0);
        }
    }

}

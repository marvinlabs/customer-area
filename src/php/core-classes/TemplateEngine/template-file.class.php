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
 * Information about a template file
 */
class CUAR_TemplateFile
{
    /** @var string Regular expression to find the template version within the template file */
    private static $VERSION_REGEX = '/Template version:\s*(\d+\.\d+.\d+)/';

    private $name = '';
    private $original_path = '';
    private $original_version = '';
    private $current_path = '';
    private $current_version = '';
    private $is_outdated = false;

    /**
     * Constructor
     * @param string $original_path The path of the template file as provided by the plugin
     * @param string $current_path The path of the template file as overridden by the user
     */
    public function __construct($original_path, $current_path)
    {
        $this->original_path = $original_path;
        $this->current_path = $current_path;

        $this->read_information();
    }

    /**
     * Read all the information about the template file
     */
    private function read_information()
    {
        // Get original version
        $original_version = $this->get_template_version($this->original_path);
        $this->set_original_version($original_version);

        // Get overloaded version number
        $current_version = empty($this->current_path) ? '' : $this->get_template_version($this->current_path);
        $this->set_current_version($current_version);

        // Outdated?
        if (empty($current_version) && !empty($original_version) && !empty($this->current_path)) {
            $this->set_outdated(true);
        } else if (empty($current_version)) {
            $this->set_outdated(false);
        } else {
            $this->set_outdated(version_compare($original_version, $current_version, '!='));
        }

        // Add template to our list
        $this->set_name(basename($this->original_path));
    }

    /**
     * Extract the version number from a template file
     * @param $file_path
     * @return string
     */
    private function get_template_version($file_path)
    {
        $input = file_get_contents($file_path, null, null, null, 256);
        if (preg_match(self::$VERSION_REGEX, $input, $matches) > 0) {
            return $matches[1];
        }
        return '';
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function get_current_path()
    {
        return $this->current_path;
    }

    /**
     * @param string $current_path
     */
    public function set_current_path($current_path)
    {
        $this->current_path = $current_path;
    }

    /**
     * @return string
     */
    public function get_current_version()
    {
        return $this->current_version;
    }

    /**
     * @param string $current_version
     */
    public function set_current_version($current_version)
    {
        $this->current_version = $current_version;
    }

    /**
     * @return string
     */
    public function get_original_path()
    {
        return $this->original_path;
    }

    /**
     * @param string $file_path
     */
    public function set_original_path($file_path)
    {
        $this->original_path = $file_path;
    }

    /**
     * @return boolean
     */
    public function is_outdated()
    {
        return $this->is_outdated;
    }

    /**
     * @param boolean $is_outdated
     */
    public function set_outdated($is_outdated)
    {
        $this->is_outdated = $is_outdated;
    }

    /**
     * @return string
     */
    public function get_original_version()
    {
        return $this->original_version;
    }

    /**
     * @param string $original_version
     */
    public function set_original_version($original_version)
    {
        $this->original_version = $original_version;
    }
}
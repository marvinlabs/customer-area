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
 * Helps to scan a directory to find templates and their current version
 */
class CUAR_TemplateFinder
{

    /** @var CUAR_TemplateEngine */
    private $template_engine;

    /** @var array The templates found when scanning a directory */
    private $templates;

    /**
     * Constructor
     * @param CUAR_TemplateEngine $template_engine
     */
    public function __construct($template_engine)
    {
        $this->template_engine = $template_engine;
        $this->templates = array();
    }

    /**
     * Recursively scan a directory to find templates in there
     * @param $dir
     */
    public function scan_directory($dir)
    {
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->scan_directory($path);
            } else if (false !== strpos($item, '.template.php')) {
                $this->add_template_file($path);
            }
        }
    }

    /**
     * Get all outdated templates we found
     * @return array
     */
    public function get_all_templates()
    {
        return $this->templates;
    }

    /**
     * Get all outdated templates we found
     * @return array
     */
    public function get_outdated_templates()
    {
        $out = array();
        foreach ($this->templates as $k => $t) {
            if ($t->is_outdated()) $out[$k] = $t;
        }
        ksort($out);
        return $out;
    }

    /**
     * Get the number of templates we found
     * @return int
     */
    public function get_template_count()
    {
        return count($this->templates);
    }

    /**
     * Add a file to the array of found templates
     * @param string $file_path
     */
    private function add_template_file($file_path)
    {
        $current_path = $this->template_engine->get_template_file_path(pathinfo($file_path, PATHINFO_DIRNAME), basename($file_path), 'templates');
        $t = new CUAR_TemplateFile($file_path, $current_path);

        $this->templates[$t->get_name()] = $t;
    }
}
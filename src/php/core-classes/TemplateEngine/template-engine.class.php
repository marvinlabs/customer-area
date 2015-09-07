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

class CUAR_TemplateEngine
{
    /** @var bool true to output debugging info */
    private $enable_debug = false;

    /** @var string The slug of the plugin (usually its folder name) */
    private $plugin_slug = 'define-me';

    /** @var array Default root folders for the template files */
    private $default_roots;

    /**
     * Constructor
     *
     * @param string $plugin_slug
     * @param bool   $enable_debug
     */
    function __construct($plugin_slug, $enable_debug = false)
    {
        $this->plugin_slug = $plugin_slug;
        $this->enable_debug = $enable_debug;

        $this->default_roots = array(
            untrailingslashit(WP_CONTENT_DIR) . '/' . $this->plugin_slug,
            untrailingslashit(get_stylesheet_directory()) . '/' . $this->plugin_slug,
            untrailingslashit(get_stylesheet_directory())
        );
    }


    /**
     * @param boolean $enable_debug
     */
    public function enable_debug($enable_debug = true)
    {
        $this->enable_debug = $enable_debug;
    }

    /**
     * Checks all templates overridden by the user to see if they need an update
     *
     * @param $dirs_to_scan The directories to scan
     *
     * @return array An array containing all the outdated template files found
     */
    public function check_templates($dirs_to_scan)
    {
        $outdated_templates = array();

        foreach ($dirs_to_scan as $dir => $title)
        {
            $template_finder = new CUAR_TemplateFinder($this);
            $template_finder->scan_directory($dir);

            $tmp = $template_finder->get_outdated_templates();
            if ( !empty($tmp))
            {
                $outdated_templates[$title] = $tmp;
            }

            unset($template_finder);
        }

        return $outdated_templates;
    }

    /**
     * Takes a default template file as parameter. It will look in the theme's directory to see if the user has
     * customized the template. If so, it returns the path to the customized file. Else, it returns the default
     * passed as parameter.
     *
     * Order of preference is:
     * 1. user-directory/filename
     * 2. user-directory/fallback-filename
     * 3. default-directory/filename
     * 4. default-directory/fallback-filename
     *
     * @param string|array $default_root
     * @param string|array $filenames
     * @param string       $relative_path
     *
     * @return string
     */
    public function get_template_file_path($template_roots, $filenames, $relative_path = '')
    {
        $enable_debug = $this->enable_debug && !is_admin();

        // Build the possible locations list
        if ( !is_array($template_roots)) $template_roots = array($template_roots);

        $possible_locations = array_merge($this->default_roots, $template_roots);
        $possible_locations = apply_filters('cuar/ui/template-directories', $possible_locations);

        // Handle cas when only a single filename is given
        if ( !is_array($filenames)) $filenames = array($filenames);

        // Make sure we have trailing slashes
        if ( !empty($relative_path)) $relative_path = trailingslashit($relative_path);

        // For each location, try to look for a file from the stack
        foreach ($possible_locations as $dir)
        {
            $dir = trailingslashit($dir);
            foreach ($filenames as $filename)
            {
                $path = $dir . $relative_path . $filename;
                if (file_exists($path))
                {
                    if ($enable_debug) $this->print_template_debug_info($filenames, $possible_locations, $filename, $dir . $relative_path);

                    return $path;
                }
            }
        }

        if ($enable_debug) $this->print_template_debug_info($filenames, $possible_locations);

        return '';
    }

    /**
     * Output some debugging information about a template we have included (or tried to)
     *
     * @param array  $filenames          The filenames which were provided
     * @param array  $possible_locations The locations we have been told to explore
     * @param string $filename           File that got chosen
     * @param string $path               The path where the file was found
     */
    private function print_template_debug_info($filenames, $possible_locations, $filename = null, $path = '')
    {
        echo "\n<!-- WPCA DEBUG - TEMPLATE REQUESTED \n";
        echo "       ## FOUND     : " . (($filename == null) ? 'NO' : 'YES') . "\n";

        if ( !empty($filename))
        {
            echo "       ## PICKED    : $filename \n";
        }

        echo "       ## FROM STACK: \n";
        foreach ($filenames as $f)
        {
            echo "           - " . $f . "\n";
        }

        if ( !empty($path))
        {
            echo "       ## IN PATH   : $path \n";
        }

        echo "       ## FROM ROOTS: \n";
        foreach ($possible_locations as $loc)
        {
            $to_remove = dirname(WP_CONTENT_DIR);
            $loc = str_replace($to_remove, '', $loc);
            echo "           - " . $loc . "\n";
        }

        echo "-->\n";
    }
}
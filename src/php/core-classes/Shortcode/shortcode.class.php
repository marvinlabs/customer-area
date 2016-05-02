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
 * A shortcode base class
 */
abstract class CUAR_Shortcode
{
    /** @var string The shortcode name */
    protected $name;

    /**
     * Constructor
     *
     * @param string $name The shortcode name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->register_hooks();
    }

    /**
     * Hook into WP
     */
    protected function register_hooks()
    {
        add_shortcode($this->name, array(&$this, 'do_shortcode'));
    }

    /**
     * @return array An associative array with all the default parameter values
     */
    protected function get_default_param_values()
    {
        return array();
    }

    /**
     * @return array A list of parameter keys that are required
     */
    protected function get_required_params()
    {
        return array();
    }

    /**
     * Actually process the shortcode (output stuff, ...)
     *
     * @param array  $params  The parameters
     * @param string $content The content between the shortcode tags
     *
     * @return string The shortcode final output
     */
    protected abstract function process_shortcode($params, $content);

    /**
     * The WordPress hook that gets called when the shortcode is detected. This basically checks parameters, provides default values, and calls the child
     * class' process_shortcode function.
     *
     * @param array  $params  The parameters
     * @param string $content The content between the shortcode tags
     *
     * @return string The shortcode final output
     */
    public function do_shortcode($params = array(), $content = null)
    {
        // default parameters
        $params = shortcode_atts($this->get_default_param_values(), $params);

        // Check required parameters
        foreach ($this->get_required_params() as $key)
        {
            if ( !isset($params[$key]) || empty($params[$key]))
            {
                return sprintf(__('The shortcode <code>[%1$s]</code> is missing required parameter: %2$s', 'cuar'),
                    $this->name, $key);
            }
        }

        do_action('cuar/core/shortcode/before-process', $this);

        // Run the shortcode
        $out = $this->process_shortcode($params, $content);

        do_action('cuar/core/shortcode/after-process', $this);

        return $out;
    }
}
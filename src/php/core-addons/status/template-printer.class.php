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
 * Print a table of template files
 */
class CUAR_TemplatePrinter
{

    public function __construct()
    {
    }

    public function print_template_section($title)
    {
        echo '<tr>' . "\n";
        echo '  <td colspan="4"><h3>' . $title . '</h3></td>' . "\n";
        echo '</tr>' . "\n";
    }

    public function print_template_headings()
    {
        echo '<tr>' . "\n";
        echo '  <th>' . __('File', 'cuar') . '</th>' . "\n";
        echo '  <th>' . __('Original', 'cuar') . '</th>' . "\n";
        echo '  <th>' . __('Current', 'cuar') . '</th>' . "\n";
        echo '</tr>' . "\n";
    }

    /**
     * @param array $templates
     */
    public function print_template_list($templates)
    {
        ksort($templates);
        foreach ($templates as $name => $template) {
            $is_outdated = $template->is_outdated();
            $original_path = $template->get_original_path();
            $original_version = $template->get_original_version();
            $current_path = $template->get_current_path();
            $current_version = $template->get_current_version();

            $is_overridden = !empty($current_path);

            $tr_class = $is_outdated ? 'cuar-outdated cuar-needs-attention' : '';
            $tr_class .= $is_overridden ? ' cuar-overridden' : '';

            echo '<tr class="' . $tr_class . '">' . "\n";
            echo '  <td class=""><code>' . basename($original_path) . '</code></td>' . "\n";
            echo '  <td class=""><strong>'
                . sprintf(__('Version: %s', 'cuar'), empty($original_version) ? __('unknown', 'cuar') : $original_version)
                . '</strong><br><em>'
                . $this->get_short_directory($original_path, true)
                . '</em></td>' . "\n";
            if (empty($current_path)) {
                echo '  <td class="">' . __('Not overridden', 'cuar') . '</td>' . "\n";
            } else {
                echo '  <td class=""><strong>'
                    . sprintf(__('Version: %s', 'cuar'), empty($current_version) ? __('unknown', 'cuar') : $current_version)
                    . '</strong><br><em>'
                    . $this->get_short_directory($current_path, false)
                    . '</em></td>' . "\n";
            }
            echo '</tr>' . "\n";
        }
    }

    private function get_short_directory($path, $is_original)
    {
        $dirname = dirname($path);

        if ($is_original) {
            $strip_from = strpos($path, 'customer-area');
            $dirname = $strip_from > 0 ? strstr($dirname, 'customer-area') : $dirname;
        } else {
            $strip_from = strpos($path, 'wp-content');
            $dirname = $strip_from > 0 ? strstr($dirname, 'wp-content') : $dirname;
        }

        return $dirname;
    }
}

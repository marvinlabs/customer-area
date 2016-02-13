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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon.class.php');

if ( !class_exists('CUAR_CapabilitiesAddOn')) :

    /**
     * Add-on to manage capabilities used in the customer area
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_CapabilitiesAddOn extends CUAR_AddOn
    {

        public function __construct()
        {
            parent::__construct('capabilities-manager', '4.0.0');
        }

        public function get_addon_name()
        {
            return __('Capabilities Manager', 'cuar');
        }

        public function run_addon($plugin)
        {
            // Init the admin interface if needed
            if (is_admin())
            {
                add_filter('cuar/core/status/sections', array(&$this, 'add_status_sections'));

                // Settings
                add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_settings_tab'), 400, 1);
                add_action('cuar/templates/settings/in-settings-form?tab=cuar_capabilities', array(&$this, 'print_settings'));
                add_filter('cuar/core/settings/validate-settings?tab=cuar_capabilities', array(&$this, 'validate_options'), 10, 3);
            }

            add_action('init', array(&$this, 'set_administrator_capabilities'), 100);
        }

        public function check_attention_needed()
        {
            parent::check_attention_needed();
            if ($this->plugin->is_warning_ignored('unconfigured-capabilities')) return;
            $this->plugin->set_attention_needed('unconfigured-capabilities', __('You have not yet configured the plugin permissions.', 'cuar'), 100);
        }

        public function ignore_unconfigured_capabilities_flag()
        {
            $this->plugin->ignore_warning('unconfigured-capabilities');
            $this->plugin->clear_attention_needed('unconfigured-capabilities');
        }

        public function add_status_sections($sections)
        {
            $sections['capabilities-manager'] = array(
                'id'            => 'capabilities',
                'template_path' => CUAR_INCLUDES_DIR . '/core-addons/capabilities',
                'linked-checks' => array('unconfigured-capabilities'),
                'actions'       => array(
                    'cuar-ignore-unconfigured-capabilities' => array(&$this, 'ignore_unconfigured_capabilities_flag'),
                )
            );

            return $sections;
        }

        /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

        public function add_settings_tab($tabs)
        {
            $tabs['cuar_capabilities'] = __('Capabilities', 'cuar');

            return $tabs;
        }

        /**
         * Give all caps to the admin
         */
        public function set_administrator_capabilities()
        {
            $admin_role = get_role('administrator');
            if ($admin_role)
            {
                $all_caps = $this->get_configurable_capability_groups();

                foreach ($all_caps as $section_id => $section)
                {
                    foreach ($section['groups'] as $group)
                    {
                        foreach ($group['capabilities'] as $key => $label)
                        {
                            $admin_role->add_cap($key);
                        }
                    }
                }
            }
        }

        /**
         * Add our fields to the settings page
         */
        public function print_settings()
        {
            global $wp_roles;
            if ( !isset($wp_roles)) $wp_roles = new WP_Roles();
            $all_roles = $wp_roles->role_objects;

            $all_capability_groups = $this->get_configurable_capability_groups();
            uasort($all_capability_groups, array(&$this, 'sort_capability_groups'));

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/capabilities',
                'capabilities-table.template.php',
                'templates'));
        }

        public function sort_capability_groups($a, $b)
        {
            return strcmp($a['label'], $b['label']);
        }

        /**
         * Validate our options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         * @param array         $validated
         *
         * @return array
         */
        public function validate_options($validated, $cuar_settings, $input)
        {
            global $wp_roles;
            if ( !isset($wp_roles)) $wp_roles = new WP_Roles();
            $roles = $wp_roles->role_objects;

            $all_capability_groups = $this->get_configurable_capability_groups();

            foreach ($all_capability_groups as $section_id => $section)
            {
                foreach ($section['groups'] as $group_id => $group)
                {
                    $group_name = $group['group_name'];
                    $group_caps = $group['capabilities'];

                    if (empty($group_caps)) continue;

                    foreach ($group_caps as $cap => $cap_name)
                    {
                        foreach ($roles as $role)
                        {
                            $name = str_replace(' ', '-', $role->name . '_' . $cap);

                            if (isset($_POST[$name]))
                            {
                                $role->add_cap($cap);
                            }
                            else
                            {
                                $role->remove_cap($cap);
                            }
                        }
                    }
                }
            }

            $this->ignore_unconfigured_capabilities_flag();

            return $validated;
        }

        private function get_configurable_capability_groups()
        {
            if ($this->all_capability_groups == null)
            {
                // each entry should be an array in the form:
                // 	'section_id' => array(
                //   	'label' => 'My Caps',
                //   	'groups' => array(
                //      	'group_id' => array(
                //     	  		'group_name' => 'My Add-on',
                //        		'capabilities' => array( 'my_cap' => 'My cap label' )
                //       	)
                //    	)
                //	);
                $this->all_capability_groups = apply_filters('cuar/core/permission-groups', array());
            }

            return $this->all_capability_groups;
        }

        /** @var array */
        private $all_capability_groups;
    }

    // Make sure the addon is loaded
    new CUAR_CapabilitiesAddOn();

endif; // if (!class_exists('CUAR_CapabilitiesAddOn')) 

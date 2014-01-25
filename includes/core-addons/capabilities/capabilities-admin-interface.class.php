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

if (!class_exists('CUAR_CapabilitiesAdminInterface')) :

/**
 * Administation area for private files
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_CapabilitiesAdminInterface {
	
	public function __construct( $plugin, $capabilities_addon ) {
		$this->plugin = $plugin;
		$this->capabilities_addon = $capabilities_addon;

		// Settings
		add_filter( 'cuar_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 10, 1 );
		add_action( 'cuar_in_settings_form_cuar_capabilities', array( &$this, 'print_settings' ) );
		add_filter( 'cuar_addon_validate_options_cuar_capabilities', array( &$this, 'validate_options' ), 10, 3 );
	}

	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/

	public function add_settings_tab( $tabs ) {
		$tabs[ 'cuar_capabilities' ] = __( 'Capabilities', 'cuar' );
		return $tabs;
	}
	
	/**
	 * Add our fields to the settings page
	 * 
	 * @param CUAR_Settings $cuar_settings The settings class
	 */
	public function print_settings() {
		global $wp_roles;
		$all_roles 	= $wp_roles->role_objects;
		
		$all_capability_groups = $this->get_configurable_capability_groups();
		
  		include( $this->plugin->get_template_file_path(
  				CUAR_INCLUDES_DIR . '/core-addons/capabilities',
  				'capabilities-table.template.php',
  				'templates' ));
	}
	
	/**
	 * Validate our options
	 * 
	 * @param CUAR_Settings $cuar_settings
	 * @param array $input
	 * @param array $validated
	 */
	public function validate_options( $validated, $cuar_settings, $input ) {
		global $wp_roles;
		$roles 	= $wp_roles->role_objects;
		$all_capability_groups = $this->get_configurable_capability_groups();
		
		foreach ( $all_capability_groups as $group ) {
			$group_name = $group['group_name'];
			$group_caps = $group['capabilities'];
		
			if ( empty( $group_caps ) ) continue;

			foreach ( $roles as $role ) {
				foreach ( $group_caps as $cap => $cap_name ) {
					$name = str_replace( ' ', '-', $role->name . '_' . $cap );
					
					if ( isset( $_POST[ $name ] ) ) {
						$role->add_cap( $cap );
					} else {
						$role->remove_cap( $cap );
					}
				}
			}
		} 
		
		return $validated;
	}
	
	private function get_configurable_capability_groups() {
		if ( $this->all_capability_groups==null ) {		
			// each entry should be an array in the form:
			// array( 
			//   'group_name' => 'My Add-on', 
			//   'capabilities' => array( 'my_cap' => 'My cap label' ) 
			// );
			$this->all_capability_groups = apply_filters( 'cuar_configurable_capability_groups', array() );
		}
		return $this->all_capability_groups;
	}
		
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_CapabilitiesAddOn */
	private $capabilities_addon;
	
	/** @var array */

	private $all_capability_groups;
	
}

endif; // if (!class_exists('CUAR_CapabilitiesAdminInterface')) :
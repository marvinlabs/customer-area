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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon.class.php' );

require_once( dirname(__FILE__) . '/capabilities-admin-interface.class.php' );

if (!class_exists('CUAR_CapabilitiesAddOn')) :

/**
 * Add-on to manage capabilities used in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_CapabilitiesAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'capabilities-manager', __( 'Capabilities Manager', 'cuar' ), '2.0.0' );
	}

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;
		
		// Init the admin interface if needed
		if ( is_admin() ) {
			$this->admin_interface = new CUAR_CapabilitiesAdminInterface( $plugin, $this );
		} 
	}	
	
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_CapabilitiesAdminInterface */
	private $admin_interface;
}

// Make sure the addon is loaded
new CUAR_CapabilitiesAddOn();

endif; // if (!class_exists('CUAR_CapabilitiesAddOn')) 

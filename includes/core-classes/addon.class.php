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


if (!class_exists('CUAR_AddOn')) :

/**
 * The base class for addons
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AddOn {

	public function __construct( $addon_id = null, $addon_name = null, $min_cuar_version = null ) {
		$this->addon_id = $addon_id;
		$this->addon_name = $addon_name;
		$this->min_cuar_version = $min_cuar_version;

		add_action( 'cuar_default_options', array( &$this, 'set_default_options' ) );
		add_action( 'cuar_addons_init', array( &$this, 'run' ) );
	}
	
	/** 
	 * Function that starts the add-on
	 */
	public function run( $cuar_plugin ) {	
		$cuar_plugin->register_addon( $this );
		
		$current_version = $cuar_plugin->get_option( CUAR_Settings::$OPTION_CURRENT_VERSION );
		
		if ( is_admin() ) {
			if ( $this->addon_name == null 
					|| $this->min_cuar_version==null 
					|| $current_version<$this->min_cuar_version ) {
				add_action('admin_notices', array( &$this, 'show_warnings' ) );
			}
		}
		
		$this->run_addon( $cuar_plugin );
	}
	
	/**
	 * Addons should implement this method to do their initialisation
	 * 
	 * @param CUAR_Plugin $cuar_plugin The plugin instance
	 */
	public abstract function run_addon( $cuar_plugin );
	
	/**
	 * Shows a compatibity warning
	 */
	public function show_warnings() {
		if ( $this->addon_name == null || $this->min_cuar_version==null ) {
			echo '<div id="message" class="error">A customer area add-on MUST call the CUAR_AddOn constructor!</div>';
			return;
		} 
					
		global $cuar_plugin;
		$current_version = $cuar_plugin->get_option( CUAR_Settings::$OPTION_CURRENT_VERSION );
		if ( $current_version<$this->min_cuar_version ) {
			printf( '<div id="message" class="error"><p>%s</p></div>', 
				sprintf( __( 'The "%1$s" add-on requires Customer Area %3$s. You are currently using version %2$s. '
						. 'Please update the Customer Area plugin.', 'cuar' ), 
					$this->addon_name, $current_version, $this->min_cuar_version ) );
		}
	}
	
	public function set_default_options( $defaults ) {
	}
	
	/** @var string Id of the add-on */
	public $addon_id;
	
	/** @var string Name of the add-on */
	public $addon_name;
	
	/** @var string min version of Customer Area */
	public $min_cuar_version;
}

endif; // CUAR_AddOn
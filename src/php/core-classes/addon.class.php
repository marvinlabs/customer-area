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

	public function __construct( $addon_id = null, $min_cuar_version = null ) {
		$this->addon_id = $addon_id;
		$this->min_cuar_version = $min_cuar_version;

		add_action( 'cuar/core/settings/default-options', array( &$this, 'set_default_options' ) );
		add_action( 'cuar/core/addons/init', array( &$this, 'run' ), 10 );
		
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'check_main_plugin_enabled' ), 10 );
			add_action( 'cuar/core/addons/after-init', array( &$this, 'check_attention_needed' ), 10 );
		}
	}

	public abstract function get_addon_name();
	
	public function get_id() {
		return $this->addon_id;
	}

    /**
     * Function that starts the add-on
     * @param CUAR_Plugin $cuar_plugin
     */
	public function run( $cuar_plugin ) {	
		$this->plugin = $cuar_plugin;
		$cuar_plugin->register_addon( $this );
		$this->run_addon( $cuar_plugin );
		
		if ( is_admin() && $cuar_plugin->has_commercial_addons() ) {
			// Settings
			add_filter( 'cuar/core/settings/settings-tabs', array( &$this, 'add_settings_tab' ), 900, 1 );
			add_action( 'cuar/core/settings/print-settings?tab=cuar_licenses', array( &$this, 'print_license_settings' ), 10, 2 );
			add_filter( 'cuar/core/settings/validate-settings?tab=cuar_licenses', array( &$this, 'validate_license_options' ), 10, 3 );
		}
	}
	
	/**
	 * Addons should implement this method to do their initialisation
	 * 
	 * @param CUAR_Plugin $cuar_plugin The plugin instance
	 */
	public abstract function run_addon( $cuar_plugin );

	/**
	 * Add-ons can check if something needs to be fixed at this point of time
	 */
	public function check_attention_needed() {
	}

	/**
	 * Check that the main plugin is properly enabled. Else output a notice
	 */
	public function check_main_plugin_enabled() {
		global $cuar_main_plugin_checked;
		if (!is_plugin_active('customer-area/customer-area.php') && $cuar_main_plugin_checked!==true)
		{
			$cuar_main_plugin_checked = true;
			add_action('admin_notices', array(&$this, 'add_main_plugin_disabled_notice'));
		}
	}

	/**
	 * Show a message to warn that the main plugin is either not installed or not activated
	 */
	public function add_main_plugin_disabled_notice()
	{
		echo '<div class="error"><p>';
		echo __('<strong>Error: </strong>WP Customer Area add-ons are active but the main plugin is not installed!', 'cuar');
		echo '</p></div>';
	}
	
	public function set_default_options( $defaults ) {
		$defaults[ $this->get_license_key_option_name() ] = '';
				
		return $defaults;
	}
	
	/** @var string Id of the add-on */
	public $addon_id;

    /** @var string ID of the add-on on the new wpca store */
    public $store_item_id;
	
	/** @var string Name of the add-on on the legacy marvinlabs store */
	public $store_item_name;
	
	/** @var string min version of Customer Area */
	public $min_cuar_version;
	
	/** @var CUAR_Plugin The plugin instance */ 
	protected $plugin;

	/*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/
	
	public function add_settings_tab( $tabs ) {
		$tabs[ 'cuar_licenses' ] = __( 'License Keys', 'cuar' );
		return $tabs;
	}
	
	/**
	 * Add our fields to the settings page
	 * 
	 * @param CUAR_Settings $cuar_settings The settings class
	 */
	public function print_license_settings( $cuar_settings, $options_group ) {
		add_settings_section(
				'cuar_license_keys_section',
				__('License keys for commercial add-ons', 'cuar'),
				array( &$this, 'print_license_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		$commercial_addons = $this->plugin->get_commercial_addons();				
		foreach ( $commercial_addons as $id => $addon ) {		
			add_settings_field(
					$this->get_license_key_option_name( $id ), 
					$addon->get_addon_name(),
					array( &$cuar_settings, 'print_license_key_field' ), 
					CUAR_Settings::$OPTIONS_PAGE_SLUG,
					'cuar_license_keys_section',
					array( 
						'option_id' 		=> $this->get_license_key_option_name( $id ), 
						'status_option_id'	=> $this->get_license_status_option_name( $id ),
						'check_option_id'	=> $this->get_license_check_option_name( $id ),
						'addon_id' 			=> $id, 
		    			'after'				=> '' )
				);	
		}	
	}

    /**
     * Validate our options
     *
     * @param CUAR_Settings $cuar_settings
     * @param array $input
     * @param array $validated
     * @return array
     */
	public function validate_license_options( $validated, $cuar_settings, $input ) {
		$commercial_addons = $this->plugin->get_commercial_addons();				
		foreach ( $commercial_addons as $id => $addon ) {		
			$cuar_settings->validate_license_key( $input, $validated, $this->get_license_key_option_name( $id ) );
		}
				
		return $validated;
	}
	
	public function print_license_section_info() {
		echo '<p>';
		_e( 'This page allows you to enter license key you have received when you purchased commercial addons.', 'cuar' );
		echo ' <strong>' . __( 'Do not activate your license on your development site. Only on the final site where you are running the plugin.', 'cuar' ) . '</strong>';
		echo '</p>';
	}	
		
	/*------- LICENSING FUNCTIONS ---------------------------------------------------------------------------------*/
	
	public function enable_licensing( $store_item_id, $store_item_name, $plugin_file, $add_on_version ) {
		$this->store_item_id = $store_item_id;
        $this->store_item_name = $store_item_name;
		
		$license_key = $this->get_license_key();		 
		if ( !empty( $license_key ) ) {
            require_once( CUAR_PLUGIN_DIR . '/libs/php/edd-licensing/EDD_SL_Plugin_Updater.php' );
	
			new CUAR_Plugin_Updater(
					$this->plugin->get_licensing()->get_store()->get_store_url(),
					$plugin_file, 
					array(
							'item_id'   => $this->store_item_id,
							'license' 	=> $license_key,
							'version' 	=> $add_on_version,
							'author' 	=> 'MarvinLabs'
						) 
				);
		}
		
		$this->plugin->tag_addon_as_commercial( $this->addon_id );
	}
	
	public function get_license_key() {
		return trim( $this->plugin->get_option( $this->get_license_key_option_name(), '' ) );		
	}
	
	public function get_license_check() {
		return trim( $this->plugin->get_option( $this->get_license_check_option_name(), '' ) );		
	}
	
	public function get_license_status() {
		return trim( $this->plugin->get_option( $this->get_license_status_option_name(), '' ) );		
	}
	
	public function get_license_key_option_name( $addon_id='' ) {
		if ( empty( $addon_id ) ) $addon_id = $this->addon_id;
		return self::$OPTION_LICENSE_KEY . $addon_id;		
	}
	
	public function get_license_status_option_name( $addon_id='' ) {
		if ( empty( $addon_id ) ) $addon_id = $this->addon_id;
		return self::$OPTION_LICENSE_STATUS . $addon_id;		
	}
	
	public function get_license_check_option_name( $addon_id='' ) {
		if ( empty( $addon_id ) ) $addon_id = $this->addon_id;
		return self::$OPTION_LICENSE_CHECK . $addon_id;		
	}
	
	private static $OPTION_LICENSE_KEY = 'cuar_license_key_';
	private static $OPTION_LICENSE_CHECK = 'cuar_license_check_';
	private static $OPTION_LICENSE_STATUS = 'cuar_license_status_';
	
}

function cuar_sort_addons_by_name_callback( $a, $b ) {
	return strcmp( $a->get_addon_name(), $b->get_addon_name() );
}

endif; // CUAR_AddOn
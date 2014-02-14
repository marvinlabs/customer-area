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

if (!class_exists('CUAR_StatusAddOn')) :

/**
 * Add-on to output the status of the Customer Area plugin
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_StatusAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'status', __( 'Status', 'cuar' ), '4.0.0' );
	}

	public function run_addon( $plugin ) {
		// We only do something within the admin interface
		if ( is_admin() ) {
			add_action( 'cuar_admin_submenu_pages', array( &$this, 'add_menu_items' ), 10000 );
			add_action( 'admin_init', array( &$this, 'handle_core_section_actions' ), 500 );
		} 
	}	

	/**
	 * Add the menu item
	 */
	public function add_menu_items( $submenus ) {
		$submenus[] = array(
							'page_title'	=> __( 'Customer Area - Plugin status', 'cuar' ),
							'title'			=> __( 'Status', 'cuar' ),
							'slug' 			=> self::$STATUS_PAGE_SLUG,
							'function' 		=> array( &$this, 'print_status_page' ),
							'capability' 	=> 'manage_options' 
						); 
		
		return $submenus;
	}
	
	/**
	 * Display the main status page
	 */
	public function print_status_page() {
		$sections = $this->get_status_sections();
		
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/status',
				'status-page.template.php',
				'templates' ));
	}
	
	/**
	 * Get the sections available to the main page
	 */
	public function get_status_sections() {
		if ( $this->sections==null ) {
			$this->sections = array(
				'needs-attention' => array(
						'id'			=> 'needs-attention',
						'label'			=> __('Needs attention', 'cuar'),
						'title'			=> __('Things that need your attention', 'cuar')
					),
				'installed-addons' => array(
						'id'			=> 'installed-addons',
						'label'			=> __('Installed add-ons', 'cuar'),
						'title'			=> __('Core and commercial add-ons currently enabled', 'cuar'),
						'linked-checks'	=> array( 'outdated-plugin-version' )
					)
			);
			
			$this->sections = apply_filters( 'cuar_status_sections', $this->sections );
			
			$this->sections['reset'] = array(
					'id'			=> 'reset',
					'label'			=> __('Reset', 'cuar'),
					'title'			=> __('Reset settings', 'cuar'),
					'actions'		=> array(
							'cuar-reset-all-settings'	=> array( &$this, 'reset_settings' )
						)
				);
		}
		return $this->sections;
	}
	
	public function print_section_template( $section ) {
		$template_path = isset( $section['template_path'] ) ? $section['template_path'] : CUAR_INCLUDES_DIR . '/core-addons/status';
		$template_file = 'status-section-' . $section['id'] . '.template.php';
		
		$template = $this->plugin->get_template_file_path( $template_path, $template_file, 'templates' );
		
		if ( !empty( $template ) ) {
			include( $template );
		}
	}
	
	public function handle_core_section_actions() {
		if ( !isset( $_POST['cuar-do-status-action'] ) ) return;

		$sections = $this->get_status_sections();
		
		foreach ( $sections as $id => $section ) {		
			$actions = isset( $section['actions'] ) ? $section['actions'] : array();
			
			foreach ( $actions as $name => $callback ) {
				$nonce = isset( $_POST[$name . '_nonce'] ) ? $_POST[$name . '_nonce'] : '';
				
				if ( isset( $_POST[$name] ) && wp_verify_nonce( $nonce, $name ) ) {
					call_user_func( $callback );

					$current_section_id = isset( $_GET['cuar_section'] ) ? $_GET['cuar_section'] : 'needs-attention';
					wp_redirect( admin_url( 'admin.php?page=cuar-status&cuar_section=' . $current_section_id ) );
					exit;
				}
			}
		}
	}
	
	private function reset_settings() {
		$this->plugin->reset_defaults();
		$this->plugin->add_admin_notice( __('Settings have been resetted to default values', 'cuar'), 'updated' );
	}
	
	protected $sections = null;
	
	public static $STATUS_PAGE_SLUG = 'cuar-status';
}

// Make sure the addon is loaded
new CUAR_StatusAddOn();

endif; // if (!class_exists('CUAR_StatusAddOn')) 

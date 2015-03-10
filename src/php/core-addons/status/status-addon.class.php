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
		parent::__construct( 'status', '4.0.0' );
	}
	
	public function get_addon_name() {
		return __( 'Status', 'cuar' );
	}

	public function run_addon( $plugin ) {
		// We only do something within the admin interface
		if ( is_admin() ) {
			add_action( 'cuar/core/admin/main-menu-pages', array( &$this, 'add_menu_items' ), 10000 );
			add_action( 'cuar/core/admin/adminbar-menu-items', array( &$this, 'add_adminbar_menu_items' ), 10000 );
			add_action( 'admin_init', array( &$this, 'handle_core_section_actions' ), 500 );
		} 
	}

	/**
	 * Add the menu item
	 */
	public function add_menu_items( $submenus ) {
		$submenus[] = array(
				'page_title'	=> __( 'WP Customer Area - Plugin status', 'cuar' ),
				'title'			=> __( 'Status', 'cuar' ),
				'slug' 			=> self::$STATUS_PAGE_SLUG,
				'function' 		=> array( &$this, 'print_status_page' ),
				'capability' 	=> 'manage_options' 
			); 
		
		return $submenus;
	}

	/**
	 * Add the menu item
	 */
	public function add_adminbar_menu_items( $submenus ) {		
		if ( current_user_can( 'manage_options' ) ) {
			$submenus[] = array(
					'parent'=> 'customer-area',
					'id' 	=> 'customer-area-status',
					'title' => __( 'Status', 'cuar' ),
					'href' 	=> admin_url( 'admin.php?page=cuar-status' )
				);
			
			$sections = $this->get_status_sections();
			foreach ( $sections as $section ) {
				if ( !isset( $section['label'] ) ) continue;
				
				$submenus[] = array(
						'parent'=> 'customer-area-status',
						'id' 	=> 'customer-area-status-' . $section['id'],
						'title' => $section['label'],
						'href' 	=> admin_url( 'admin.php?page=cuar-status&cuar_section=' . $section['id'] )
					);
			}
		}
	
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
			
			$this->sections = apply_filters( 'cuar/core/status/sections', $this->sections );
			
			$this->sections['hooks'] = array(
					'id'			=> 'hooks',
					'label'			=> __('Actions and filters', 'cuar'),
					'title'			=> __('Listing of all actions and filters', 'cuar')
				);
			
			$this->sections['templates'] = array(
					'id'			=> 'templates',
					'label'			=> __('Templates', 'cuar'),
					'title'			=> __('Template files', 'cuar'),
					'linked-checks'	=> array( 'outdated-templates' ),
					'actions'		=> array(
							'cuar-ignore-outdated-templates'	=> array( &$this, 'ignore_outdated_templates_flag' ),
						)
				);
			
			$this->sections['reset'] = array(
					'id'			=> 'reset',
					'label'			=> __('Settings', 'cuar'),
					'title'			=> __('Settings tools', 'cuar'),
					'actions'		=> array(
							'cuar-reset-all-settings'	=> array( &$this, 'reset_settings' ),
							'cuar-export-settings'	=> array( &$this, 'export_settings' ),
							'cuar-import-settings'	=> array( &$this, 'import_settings' )
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
	
	private function import_settings() {
		if ( !isset($_FILES['cuar-settings-file']) 
				|| !isset($_FILES['cuar-settings-file']['error']) 
				|| is_array($_FILES['cuar-settings-file']['error']) ) {
			$this->plugin->add_admin_notice( __('Invalid parameters. No file sent', 'cuar') );
			return;
		}
		
		switch ($_FILES['cuar-settings-file']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				$this->plugin->add_admin_notice( __('No file sent.', 'cuar') );
				return;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$this->plugin->add_admin_notice( __('Exceeded filesize limit.', 'cuar') );	
				return;
			default:
				$this->plugin->add_admin_notice( __('Unknown errors.', 'cuar') );
				return;
		}
		
		$as_json = file_get_contents( $_FILES['cuar-settings-file']['tmp_name'] );
		$options = json_decode( $as_json, true );

		$this->plugin->set_options( $options );
		$this->plugin->add_admin_notice( __('Settings have been imported successfully', 'cuar'), 'updated' );	
	}
	
	private function export_settings() {
		$options = $this->plugin->get_options();		
		
		// Filter some options we don't want to be exported
		foreach ( $options as $key => $value ) {
			if ( strstr( $key, 'cuar_license_' )!=false ) {
				unset( $options[$key]);
			}
		}
		
		// Encode to JSON and output
		$as_json = json_encode( $options, JSON_PRETTY_PRINT );
		
		@ob_end_clean(); //turn off output buffering to decrease cpu usage
		@ob_clean();

		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="cuar-settings.json"');

		/* The three lines below basically make the	download non-cacheable */
		header("Cache-control: private");
		header('Pragma: private');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

		echo $as_json;

		die();
	}
	
	public function ignore_outdated_templates_flag() {
		$this->plugin->clear_attention_needed( 'outdated-templates' );
	}
	
	protected $sections = null;
	
	public static $STATUS_PAGE_SLUG = 'cuar-status';
}

// Make sure the addon is loaded
new CUAR_StatusAddOn();

endif; // if (!class_exists('CUAR_StatusAddOn')) 

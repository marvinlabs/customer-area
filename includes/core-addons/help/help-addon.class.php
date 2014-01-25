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

require_once( CUAR_INCLUDES_DIR . '/addon.class.php' );

if (!class_exists('CUAR_HelpAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_HelpAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'help', __( 'Help', 'cuar' ), '2.0.0' );
	}

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;
		
		// We only do something within the admin interface
		if ( is_admin() ) {
			add_filter( 'cuar_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 1000, 1 );
			add_filter( 'cuar_after_settings_side', array( &$this, 'print_addons_sidebox' ), 800 );
			add_filter( 'cuar_after_settings_side', array( &$this, 'print_marvinlabs_sidebox' ), 900 );
			add_filter( 'cuar_after_settings_side', array( &$this, 'print_newsletter_sidebox' ), 1000 );
			add_filter( 'cuar_before_settings_cuar_addons', array( &$this, 'print_addons' ) );
			add_filter( 'cuar_before_settings_cuar_troubleshooting', array( &$this, 'print_troubleshooting' ) );
			add_filter( 'admin_init', array( &$this, 'add_dashboard_metaboxes' ) );
			add_filter( 'admin_init', array( &$this, 'handle_extra_actions' ) );
			
			$plugin_file = 'customer-area/customer-area.php';
			add_filter( "plugin_action_links_{$plugin_file}", array( &$this, 'print_plugin_action_links' ), 10, 2 );
		} 
	}	
	
	public function print_plugin_action_links( $links, $file ) {
		$link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' .  CUAR_Settings::$OPTIONS_PAGE_SLUG 
				. '&cuar_tab=cuar_addons">'
				. __( 'Add-ons', 'cuar' ) . '</a>';
		array_unshift( $links, $link );	
		return $links;
	}

	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/
	
	public function add_settings_tab( $tabs ) {
		$tabs[ 'cuar_addons' ] = __( 'Add-ons', 'cuar' );
		$tabs[ 'cuar_troubleshooting' ] = __( 'Support', 'cuar' );
		return $tabs;
	}
	
	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function print_addons( $cuar_settings ) {
		include( dirname( __FILE__ ) . '/templates/list-addons.template.php' );
	}
	
	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function print_troubleshooting( $cuar_settings ) {
		include( dirname( __FILE__ ) . '/templates/troubleshooting.template.php' );
	}	
	
	public function handle_extra_actions() {
		if ( isset( $_POST['cuar-reset-all-settings'] ) ) {
			$this->plugin->reset_defaults();
			$this->plugin->add_admin_notice( __('Settings have been resetted to default values', 'cuar'), 'updated' );
		}
	}
	
	public function add_dashboard_metaboxes() {	
		add_meta_box('cuar_dashboard_addons', __( 'Enhance your customer area', 'cuar' ), 
				array( &$this, 'get_addons_sidebox_content' ), 'customer-area', 'side' );
		add_meta_box('cuar_dashboard_newletter', __( 'Subscribe to our newsletter', 'cuar' ), 
				array( &$this, 'get_newsletter_sidebox_content' ), 'customer-area', 'side' );
		add_meta_box('cuar_dashboard_marvinlabs', __( 'Get more from MarvinLabs', 'cuar' ), 
				array( &$this, 'get_marvinlabs_sidebox_content' ), 'customer-area', 'side' );
	}
	
	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function print_addons_sidebox( $cuar_settings ) {	
		$cuar_settings->print_sidebox( __( 'Enhance your customer area', 'cuar' ),
				$this->get_addons_sidebox_content() );
	}

	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function print_marvinlabs_sidebox( $cuar_settings ) {
		$cuar_settings->print_sidebox( __( 'Get more from MarvinLabs', 'cuar' ), 
				$this->get_marvinlabs_sidebox_content() );		
	}

	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function print_newsletter_sidebox( $cuar_settings ) {
		$cuar_settings->print_sidebox( __( 'Subscribe to our newsletter', 'cuar' ), 
				$this->get_newsletter_sidebox_content() );		
	}
	
	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function get_addons_sidebox_content( $args = null ) {	
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
			
		$content = sprintf( '<p>%s</p><p><a href="%s" class="button-primary" target="_blank">%s</a></p>', 
						__( '&laquo Customer Area &raquo; is a very modular plugin. We have built it so that it can be ' 
							. 'extended in many ways. Some add-ons are presented in this page by selecting the '
							. '&laquo Add-ons &raquo; tab. You can also view all extensions we have by clicking the '
							. 'link below.' , 'cuar' ),
						"http://www.marvinlabs.com/downloads/category/customer-area/",
						__( 'Browse all extensions', 'cuar' ) );
		
		if ( $echo ) echo $content;
		
		return $content;
	}


	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function get_marvinlabs_sidebox_content( $args = null ) {
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
		
		$content = sprintf( '<p>&raquo; ' . 
				__( 'If you like our plugins, you might want to <a href="%s">check our website</a> for more.', 'cuar' ) 
				. '</p>', 'http://www.marvinlabs.com' );
	
		$content .= '<p>&raquo; ' . __( 'If you want to get updates about our plugins, you can:', 'cuar' ) . '</p><ul>';
		$content .= sprintf( '<li><a href="%2$s">%1$s</a>', 
				__( 'Follow us on Twitter', 'cuar' ), 
				"http://twitter.com/marvinlabs");
		$content .= sprintf( '<li><a href="%2$s">%1$s</a>', 
				__( 'Follow us on Google+', 'cuar' ), 
				"https://plus.google.com/u/0/117677945360605555441");
		$content .= sprintf( '<li><a href="%2$s">%1$s</a>', 
				__( 'Follow us on Facebook', 'cuar' ), 
				"http://www.facebook.com/studio.marvinlabs");
		$content .= '</ul>';		
		
		if ( $echo ) echo $content;
		
		return $content;	
	}


	/**
	 * @param CUAR_Settings $cuar_settings
	 */
	public function get_newsletter_sidebox_content( $args = null ) {
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
		
		$content = sprintf( '<p>&raquo; ' . 
				__( "You can also get notified when we've got something exciting to say (plugin updates, news, etc.). Simply "
					. "subscribe to our newsletter, we won't spam, we send at most one email per month!", 'cuar' ) 
				. '</p>', 'http://www.marvinlabs.com' );
		
		$content .= '<!-- Begin MailChimp Signup Form -->';
		$content .= '<div id="mc_embed_signup">';
		$content .= '<form action="http://marvinlabs.us7.list-manage.com/subscribe/post?u=1bbbff0bec2e3841b42494431&amp;id=4b52ced231" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>';
	
		$content .= '<p class="mc-field-group">';
		$content .= '<label for="mce-EMAIL">' . __('Email Address', 'cuar' ) . ' </label>';
		$content .= '<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" /><br/>';
		$content .= '</p>';
		$content .= '<div id="mce-responses" class="clear">';
		$content .= '<div class="response" id="mce-error-response" style="display:none"></div>';
		$content .= '<div class="response" id="mce-success-response" style="display:none"></div>';
		$content .= '</div>	<div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button button-primary"></div>';
		$content .= '</form>';
		$content .= '</div>';

		$content .= '<!--End mc_embed_signup-->';
		
		
		if ( $echo ) echo $content;
		
		return $content;	
	}
	
	/** @var CUAR_Plugin */
	private $plugin;
}

// Make sure the addon is loaded
global $cuar_he_addon;
$cuar_he_addon = new CUAR_HelpAddOn();

endif; // if (!class_exists('CUAR_HelpAddOn')) 

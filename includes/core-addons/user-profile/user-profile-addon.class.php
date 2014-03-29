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

if (!class_exists('CUAR_UserProfileAddOn')) :

/**
 * Add-on to setup the user profile
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_UserProfileAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'user-profile', '4.6.0' );
	}
	
	public function get_addon_name() {
		return __( 'User Profile', 'cuar' );
	}

	public function run_addon( $plugin ) {
		// Init the admin interface if needed
		if ( is_admin() ) {
		} else {
		}
	}	
	
	/**
	 * Set the default values for the options
	 * 
	 * @param array $defaults
	 * @return array
	 */
	public function set_default_options( $defaults ) {
		$defaults = parent::set_default_options($defaults);
		
		$defaults[ self::$OPTION_PROFILE_FIELDS ] = array(
				new CUAR_FieldGroup( __( 'Account details', 'cuar' ), array( 
						new CUAR_SimpleField('user_login', 
								new CUAR_ShortTextFieldRenderer( __('Username', 'cuar' ), true, __('Your username cannot be changed.', 'cuar') ), 
								new CUAR_UserStorage(), 
								new CUAR_SimpleValidation(true)
							),
						new CUAR_SimpleField('user_email', 
								new CUAR_EmailFieldRenderer( __('Primary email address', 'cuar' ), false ), 
								new CUAR_UserStorage(), 
								new CUAR_EmailValidation(true)
							),
						new CUAR_UserPasswordField('user_pass', 
								__( 'Password', 'cuar' ), '', 
								__( 'Password (confirm)', 'cuar' ), __( 'The password must at least be composed of 5 characters.', 'cuar' ), 
								apply_filters( 'cuar_user_password_validation_rule', new CUAR_PasswordValidation( false, 5, null ) )
							),
					) )
			);
		
		return $defaults;
	}
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function get_profile_fields() {
		return $this->plugin->get_option( self::$OPTION_PROFILE_FIELDS );
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/
	
	
	// General options
	public static $OPTION_PROFILE_FIELDS		= 'profile_fields';
}

// Make sure the addon is loaded
new CUAR_UserProfileAddOn();

endif; // if (!class_exists('CUAR_UserProfileAddOn')) 

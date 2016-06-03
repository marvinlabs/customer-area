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

if ( !class_exists('CUAR_UserProfileAddOn')) :

    /**
     * Add-on to setup the user profile
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_UserProfileAddOn extends CUAR_AddOn
    {

        public function __construct()
        {
            parent::__construct('user-profile', '4.6.0');
        }

        public function get_addon_name()
        {
            return __('User Profile', 'cuar');
        }

        public function run_addon($plugin)
        {
            // Init the admin interface if needed
            if (is_admin())
            {
            }
            else
            {
            }
        }

        /**
         * Set the default values for the options
         *
         * @param array $defaults
         *
         * @return array
         */
        public function set_default_options($defaults)
        {
            $defaults = parent::set_default_options($defaults);

            return $defaults;
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function get_profile_fields()
        {
            $default_fields = array(

                'user_login'      => new CUAR_TextField('user_login', new CUAR_UserStorage(), array(
                    'label'       => __('Username', 'cuar'),
                    'readonly'    => true,
                    'inline_help' => __('Your username cannot be changed.', 'cuar'),
                    'required'    => true,
                )),

                'user_email'      => new CUAR_EmailField('user_email', new CUAR_UserStorage(), array(
                    'label'    => __('Primary email', 'cuar'),
                    'required' => true,
                )),

                'first_name' => new CUAR_TextField('first_name', new CUAR_UserStorage(), array(
                    'label' => __('First name', 'cuar')
                )),

                'last_name' => new CUAR_TextField('last_name', new CUAR_UserStorage(), array(
                    'label' => __('Last name', 'cuar')
                )),

                'nickname' => new CUAR_TextField('nickname', new CUAR_UserStorage(), array(
                    'label' => __('Nickname', 'cuar')
                )),

                'display_name' => new CUAR_DisplayNameField('display_name', new CUAR_UserStorage(), array(
                    'label' => __('Display name', 'cuar')
                )),

                'url' => new CUAR_TextField('url', new CUAR_UserStorage(), array(
                    'label' => __('Personal Website', 'cuar')
                )),

                'description' => new CUAR_TextField('description', new CUAR_UserStorage(), array(
                    'label' => __('Biography', 'cuar'),
                    'inline_help' => __('Your can write a short description about you.', 'cuar'),
                    'type' => 'long-text'
                )),

                'user_pass'       => new CUAR_UserPasswordField('user_pass', array(
                    'label'               => __('Password', 'cuar'),
                    'confirm_label'       => __('Password (confirm)', 'cuar'),
                    'confirm_inline_help' => __('The password must at least be composed of 5 characters. You will be requested to login again after your password gets changed. Leave these fields empty if you want to keep your current password',
                        'cuar'),
                    'min_length'          => 5,
                ))
            );

            return apply_filters('cuar/core/user-profile/get_profile_fields', $default_fields);
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

    }

// Make sure the addon is loaded
    new CUAR_UserProfileAddOn();

endif; // if (!class_exists('CUAR_UserProfileAddOn')) 

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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-page.class.php');

if ( !class_exists('CUAR_CustomerAccountAddOn')) :

    /**
     * Add-on to show the customer dashboard page
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_CustomerAccountAddOn extends CUAR_AbstractPageAddOn
    {

        public function __construct()
        {
            parent::__construct('customer-account');

            $this->set_page_parameters(810, array(
                    'slug'                => 'customer-account',
                    'parent_slug'         => 'customer-account-home',
                    'required_capability' => 'cuar_view_account'
                )
            );

            $this->set_page_shortcode('customer-area-account');
        }

        public function get_label()
        {
            return __('Account - Details', 'cuar');
        }

        public function get_title()
        {
            return __('Account details', 'cuar');
        }

        public function get_hint()
        {
            return __('This page shows a summary of the user account', 'cuar');
        }

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);

            add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'));
        }

        public function get_page_addon_path()
        {
            return CUAR_INCLUDES_DIR . '/core-addons/customer-account';
        }

        /*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            if (isset($capability_groups['cuar_general'])) {
                if ( !isset($capability_groups['cuar_general']['groups']['front-office'])) {
                    $capability_groups['cuar_general']['groups']['front-office'] = array(
                        'group_name'   => __('Front-office', 'cuar'),
                        'capabilities' => array()
                    );
                }

                $capability_groups['cuar_general']['groups']['front-office']['capabilities']['cuar_view_account'] = __('View account details', 'cuar');
                $capability_groups['cuar_general']['groups']['front-office']['capabilities']['cuar_edit_account'] = __('Edit account details', 'cuar');
            }

            return $capability_groups;
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        public function print_account_fields()
        {
            $current_user = $this->get_current_user();

            /** @var CUAR_UserProfileAddOn $up_addon */
            $up_addon = $this->plugin->get_addon('user-profile');
            $groups = $up_addon->get_profile_field_groups();
            $fields = $up_addon->get_profile_fields();

            do_action('cuar/core/user-profile/view/before_fields', $current_user);

            foreach ($groups as $group_id => $group_label)
            {
                $group_fields = array_filter($fields, function ($field) use ($group_id) {
                    if (empty($group_id) || $group_id === 'default')
                    {
                        return $field->get_arg('group') === '' || $field->get_arg('group') === 'default';
                    }

                    return $field->get_arg('group') === $group_id;
                });

                if (empty($group_fields))
                {
                    do_action('cuar/core/user-profile/view/before_field_group?id=' . $group_id, $current_user);
                    do_action('cuar/core/user-profile/view/after_field_group?id=' . $group_id, $current_user);
                    continue;
                }

                do_action('cuar/core/user-profile/view/before_field_group?id=' . $group_id, $current_user);

                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/customer-account',
                    [], 'templates', 'customer-account-view-content-field-group-open.template.php'));

                foreach ($group_fields as $id => $field)
                {
                    do_action('cuar/core/user-profile/view/before_field?id=' . $id, $current_user);

                    $field->render_read_only_field($current_user->ID);

                    do_action('cuar/core/user-profile/view/after_field?id=' . $id, $current_user);
                }

                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/customer-account',
                    [], 'templates', 'customer-account-view-content-field-group-close.template.php'));

                do_action('cuar/core/user-profile/view/after_field_group?id=' . $group_id, $current_user);
            }

            do_action('cuar/core/user-profile/view/after_fields', $current_user);
        }

        public function print_address_fields()
        {
            $current_user = $this->get_current_user();

            /** @var CUAR_AddressesAddOn $ad_addon */
            $ad_addon = $this->plugin->get_addon('address-manager');

            $user_addresses = $ad_addon->get_registered_user_addresses();
            foreach ($user_addresses as $address_id => $address_label) {
                $address = $ad_addon->get_owner_address('usr', array($current_user->ID), $address_id);
                $ad_addon->print_address($address, $address_id, $address_label, 'account');
            }
        }

        protected function get_current_user()
        {
            if ($this->current_user == null) {
                $user_id = apply_filters('cuar/core/user-profile/view/override-user-id', get_current_user_id());
                $this->current_user = get_userdata($user_id);
            }

            return $this->current_user;
        }

        private $current_user = null;
    }

// Make sure the addon is loaded
    new CUAR_CustomerAccountAddOn();

endif; // if (!class_exists('CUAR_CustomerAccountAddOn')) :

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

if ( !class_exists('CUAR_AddressesAddOn')) :

    /**
     * Add-on to manage addresses used in the customer area
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_AddressesAddOn extends CUAR_AddOn
    {

        public function __construct()
        {
            parent::__construct('address-manager', '6.3.0');
        }

        public function get_addon_name()
        {
            return __('Address Manager', 'cuar');
        }

        public function run_addon($plugin)
        {
            add_filter('cuar/core/js-messages?zone=admin', array(&$this, 'add_js_messages'));
            add_filter('cuar/core/js-messages?zone=frontend', array(&$this, 'add_js_messages'));

            add_action('wp_ajax_cuar_load_address_from_owner', array(&$this, 'ajax_load_address_from_owner'));
            add_action('wp_ajax_nopriv_cuar_load_address_from_owner', array(&$this, 'ajax_load_address_from_owner'));

            add_action('wp_ajax_cuar_save_address_for_owner', array(&$this, 'ajax_save_address_for_owner'));
            add_action('wp_ajax_nopriv_cuar_save_address_for_owner', array(&$this, 'ajax_save_address_for_owner'));

            add_action('wp_ajax_cuar_get_country_states', array(&$this, 'ajax_get_country_states'));
            add_action('wp_ajax_nopriv_get_country_states', array(&$this, 'ajax_get_country_states'));

            if (is_admin())
            {
                // User profile
                add_action('show_user_profile', array(&$this, 'edit_user_profile'));
                add_action('edit_user_profile', array(&$this, 'edit_user_profile'));
                add_action('personal_options_update', array(&$this, 'edit_user_profile_update'));
                add_action('edit_user_profile_update', array(&$this, 'edit_user_profile_update'));
            }
        }

        /**
         * Get the addresses available for users
         * @return array
         */
        public function get_registered_user_addresses()
        {
            return apply_filters('cuar/core/address/user-addresses', array(
                CUAR_AddressHelper::$DEFAULT_HOME_ADDRESS_ID    => __('Home address', 'cuar'),
                CUAR_AddressHelper::$DEFAULT_BILLING_ADDRESS_ID => __('Billing address', 'cuar')
            ));
        }

        /*------- CUSTOMISATION OF THE USER PROFILE PAGE ----------------------------------------------------------------*/

        /**
         * Callback for the user profile page
         *
         * @param WP_User $user The user being edited
         */
        public function edit_user_profile($user)
        {
            $user_addresses = $this->get_registered_user_addresses();
            foreach ($user_addresses as $address_id => $address_label)
            {
                $address = $this->get_owner_address('usr', array($user->ID), $address_id);
                $address_actions = array(
                    'reset' => array(
                        'label'   => __('Clear', 'cuar'),
                        'tooltip' => __('Clear the address', 'cuar')
                    ),
                );
                $extra_scripts = '';

                $this->print_address_editor($address,
                    $address_id, $address_label,
                    $address_actions, $extra_scripts, 'profile');
            }
        }

        /**
         * Called when the user profile is saved in the admin
         *
         * @param int $user_id
         */
        public function edit_user_profile_update($user_id)
        {
            $user_addresses = $this->get_registered_user_addresses();
            foreach ($user_addresses as $address_id => $address_label)
            {
                $address = isset($_POST[$address_id]) ? $_POST[$address_id] : array();

                $this->set_owner_address('usr', array($user_id), $address_id, $address);
            }
        }

        /*------- AJAX FUNCTIONS -------------------------------------------------------------------------------------*/

        /**
         * Add our JS messages
         *
         * @param array $messages
         *
         * @return array
         */
        public function add_js_messages($messages)
        {
            $messages['addressMustPickOwnerFirst'] = __('Error: you must choose an owner first', 'cuar');
            $messages['addressConfirmSaveAddressForOwner'] = __('Are you sure that you want to associate this address with that owner?', 'cuar');
            $messages['addressConfirmLoadAddressFromOwner'] = __('Do you really want to replace current address?', 'cuar');
            $messages['addressNoAddressFromOwner'] = __('There is no address linked to that owner', 'cuar');
            $messages['addressConfirmResetAddress'] = __('Do you really want to clear the address?', 'cuar');

            return $messages;
        }

        /**
         * Get the states for a given country
         */
        public function ajax_get_country_states()
        {
            $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
            if (empty($address_id))
            {
                wp_send_json_error(__('Address ID must be specified', 'cuar'));
            }

            // Check nonce
            $nonce_action = 'cuar_' . $address_id;
            $nonce_name = 'cuar_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action))
            {
                wp_send_json_error(__('Trying to cheat?', 'cuar'));
            }

            $country = isset($_POST['country']) ? $_POST['country'] : null;
            if ($country == null)
            {
                wp_send_json_success(array('states' => null));
            }

            $states = CUAR_CountryHelper::getStates($country);
            $statesAsHtml = '';
            foreach ($states as $code => $label)
            {
                $statesAsHtml .= '<option value="' . esc_attr($code) . '">' . $label . '</option>' . "\n";
            }

            wp_send_json_success(array(
                'states'      => empty($states) ? null : $states,
                'htmlOptions' => $statesAsHtml
            ));
        }

        /**
         * Load the billing address linked to the specified owner
         */
        public function ajax_load_address_from_owner()
        {
            $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
            if (empty($address_id))
            {
                wp_send_json_error(__('Address ID must be specified', 'cuar'));
            }

            // Check nonce
            $nonce_action = 'cuar_' . $address_id;
            $nonce_name = 'cuar_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action))
            {
                wp_send_json_error(__('Trying to cheat?', 'cuar'));
            }

            $owner = isset($_POST['owner']) ? $_POST['owner'] : null;
            if ($owner == null)
            {
                wp_send_json_error(__('You must specify the owner', 'cuar'));
            }

            $address = $this->get_owner_address($owner['type'], $owner['ids'], $address_id);

            wp_send_json_success(array(
                'address_id' => $address_id,
                'address'    => $address,
            ));
        }

        /**
         * Save a billing address to link it to the specified owner
         */
        public function ajax_save_address_for_owner()
        {
            $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
            if (empty($address_id))
            {
                wp_send_json_error(__('Address ID must be specified', 'cuar'));
            }

            // Check nonce
            $nonce_action = 'cuar_' . $address_id;
            $nonce_name = 'cuar_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action))
            {
                wp_send_json_error(__('Trying to cheat?', 'cuar'));
            }

            $owner = isset($_POST['owner']) ? $_POST['owner'] : null;
            if ($owner == null)
            {
                wp_send_json_error(__('You must specify the owner', 'cuar'));
            }

            $address = isset($_POST['address']) ? $_POST['address'] : null;
            if ($address == null)
            {
                wp_send_json_error(__('You must specify the address', 'cuar'));
            }

            $this->set_owner_address($owner['type'], $owner['ids'], $address_id, $address);

            wp_send_json_success(array(
                'address_id' => $address_id,
                'address'    => $address,
            ));
        }

        /*------- OWNER ADDRESS FUNCTIONS ----------------------------------------------------------------------------*/

        /**
         * Save the default billing address for that owner
         *
         * @param string $owner_type
         * @param array  $owner_ids
         * @param string $address_id
         * @param array  $address
         */
        public function set_owner_address($owner_type, $owner_ids, $address_id, $address)
        {
            $addresses = $this->get_owner_addresses($owner_type, $owner_ids);

            if ( !is_array($address)) $address = array();
            $address = CUAR_AddressHelper::sanitize_address($address);

            $addresses[$address_id] = $address;

            $this->set_owner_addresses($owner_type, $owner_ids, $addresses);
        }

        /**
         * Get the default billing address for that owner
         *
         * @param string $owner_type
         * @param array  $owner_ids
         * @param string $address_id
         *
         * @return array
         */
        public function get_owner_address($owner_type, $owner_ids, $address_id)
        {
            $addresses = $this->get_owner_addresses($owner_type, $owner_ids);

            return CUAR_AddressHelper::sanitize_address(isset($addresses[$address_id]) ? $addresses[$address_id] : array());
        }

        /**
         *
         * @param string $owner_type
         * @param array  $owner_ids
         *
         * @return string
         */
        private function get_owner_addresses($owner_type, $owner_ids)
        {
            if ( !is_array($owner_ids))
            {
                $owner_ids = array($owner_ids);
            }

            sort($owner_ids);
            $key = 'cuar_owner_addresses|' . $owner_type . '|' . implode(',', $owner_ids);

            return get_option($key, array());
        }

        /**
         *
         * @param string $owner_type
         * @param array  $owner_ids
         * @param array  $addresses
         *
         * @return string
         */
        private function set_owner_addresses($owner_type, $owner_ids, $addresses)
        {
            if ( !is_array($owner_ids))
            {
                $owner_ids = array($owner_ids);
            }

            sort($owner_ids);
            $key = 'cuar_owner_addresses|' . $owner_type . '|' . implode(',', $owner_ids);

            return update_option($key, $addresses);
        }

        /*------- PRINTING FUNCTIONS ---------------------------------------------------------------------------------*/

        /**
         * Print the address form fields
         *
         * @param array  $address
         * @param string $address_id
         * @param string $address_label
         * @param array  $address_actions
         * @param string $extra_scripts
         * @param string $template_prefix
         */
        public function print_address_editor($address,
            $address_id,
            $address_label = '',
            $address_actions = array(),
            $extra_scripts = '',
            $template_prefix = '')
        {
            $this->plugin->enable_library('jquery.select2');

            if (is_admin()) wp_enqueue_media();
            wp_enqueue_script(is_admin() ? 'cuar.admin' : 'cuar.frontend');

            $address_class = str_replace('_', '-', $address_id);
            $excluded_fields = apply_filters('cuar/core/addresses/excluded-fields?mode=edit', array(), $address_id, $address);
            $template_suffix = is_admin() ? '-admin' : '-frontend';

            $template_stack = array();
            if ( !empty($template_prefix))
            {
                $template_stack[] = $template_prefix . '-' . $address_class . '-edit-address' . $template_suffix . '.template.php';
                $template_stack[] = $template_prefix . '-edit-address' . $template_suffix . '.template.php';
                $template_stack[] = $template_prefix . '-edit-address.template.php';
            }

            $template_stack[] = $address_class . '-edit-address' . $template_suffix . '.template.php';
            $template_stack[] = $address_class . '-edit-address.template.php';

            $template_stack[] = 'edit-address' . $template_suffix . '.template.php';
            $template_stack[] = 'edit-address.template.php';

            include($this->plugin->get_template_file_path(CUAR_INCLUDES_DIR . '/core-addons/addresses', $template_stack, 'templates'));
        }

        /**
         * Print the address as a read-only HTML block
         *
         * @param array  $address
         * @param string $address_id
         * @param string $address_label
         * @param string $template_prefix
         */
        public function print_address($address,
            $address_id,
            $address_label = '',
            $template_prefix = '')
        {
            $address_class = str_replace('_', '-', $address_id);
            $excluded_fields = apply_filters('cuar/core/addresses/excluded-fields?mode=view', array('logo'), $address_id, $address);
            $template_suffix = is_admin() ? '-admin' : '-frontend';

            $template_stack = array();
            if ( !empty($template_prefix))
            {
                $template_stack[] = $template_prefix . '-' . $address_class . 'view-address' . $template_suffix . '.template.php';
                $template_stack[] = $template_prefix . '-view-address' . $template_suffix . '.template.php';
                $template_stack[] = $template_prefix . '-view-address.template.php';
            }

            $template_stack[] = $address_class . '-view-address' . $template_suffix . '.template.php';
            $template_stack[] = $address_class . '-view-address.template.php';

            $template_stack[] = 'view-address' . $template_suffix . '.template.php';
            $template_stack[] = 'view-address.template.php';

            include($this->plugin->get_template_file_path(CUAR_INCLUDES_DIR . '/core-addons/addresses', $template_stack, 'templates'));
        }
    }

    // Make sure the addon is loaded
    new CUAR_AddressesAddOn();

endif; // if (!class_exists('CUAR_AddressesAddOn'))

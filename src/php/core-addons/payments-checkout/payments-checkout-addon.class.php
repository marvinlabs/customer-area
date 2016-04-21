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

if ( !class_exists('CUAR_PaymentsCheckoutAddOn')) :

    /**
     * Add-on to show the form to validate a payment
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_PaymentsCheckoutAddOn extends CUAR_AbstractPageAddOn
    {

        public function __construct()
        {
            parent::__construct('payments-checkout', '6.4.0');

            $this->set_page_parameters(220, array(
                    'slug'         => 'payments-checkout',
                    'parent_slug'  => 'payments-home',
                    'hide_in_menu' => true,
                )
            );

            $this->set_page_shortcode('customer-area-payment-checkout');
        }

        public function get_label()
        {
            return __('Payments - Checkout', 'cuar');
        }

        public function get_title()
        {
            return __('Checkout', 'cuar');
        }

        public function get_hint()
        {
            return __('This page allows a user to pay something', 'cuar');
        }

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);
        }

        public function get_page_addon_path()
        {
            return CUAR_INCLUDES_DIR . '/core-addons/payments-checkout';
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        public function print_page_content($args = array(), $shortcode_content = '')
        {
            if (isset($_SESSION['cuar_checkout_data']) && !empty($_SESSION['cuar_checkout_data']))
            {
                $form_data = $_SESSION['cuar_checkout_data'];
                unset($_SESSION['cuar_checkout_data']);
            }
            else
            {
                $form_data = $_POST;
            }

            $error_message = isset($_SESSION['cuar_checkout_error']) ? $_SESSION['cuar_checkout_error'] : '';
            unset($_SESSION['cuar_checkout_error']);

            $object_id = isset($form_data['cuar_object_id']) ? $form_data['cuar_object_id'] : 0;
            $object_type = isset($form_data['cuar_object_type']) ? $form_data['cuar_object_type'] : '';
            $amount = isset($form_data['cuar_amount']) ? $form_data['cuar_amount'] : 0;
            $currency = isset($form_data['cuar_currency']) ? $form_data['cuar_currency'] : '';
            $address = isset($form_data['cuar_address']) ? $form_data['cuar_address'] : array();
            $selected_gateway = isset($form_data['cuar_gateway']) ? $form_data['cuar_gateway'] : '';

            if (empty($object_type) || 0 == $object_id || empty($currency))
            {
                die(__('Some checkout information has not been provided, please go back and try again', 'cuar'));
            }

            // Payment gateways
            /** @var CUAR_PaymentsAddOn $pa_addon */
            $pa_addon = $this->plugin->get_addon('payments');
            $gateways = $pa_addon->settings()->get_enabled_gateways();
            if (empty($selected_gateway))
            {
                $selected_gateway = $pa_addon->settings()->get_default_gateway();
            }

            // Payer address
            /** @var CUAR_AddressesAddOn $ad_addon */
            $ad_addon = $this->plugin->get_addon('address-manager');
            $address = apply_filters('cuar/core/payments/checkout-address', $address, $object_type, $object_id);
            $address = CUAR_AddressHelper::sanitize_address($address);
            $is_address_editable = apply_filters('cuar/core/payments/checkout-is-address-editable', false, $object_type, $object_id);

            // Templates
            $summary_template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments-checkout',
                'payments-checkout-summary.template.php',
                'templates'
            );

            $gateways_template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments-checkout',
                'payments-checkout-gateways.template.php',
                'templates'
            );

            $address_template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments-checkout',
                'payments-checkout-address.template.php',
                'templates'
            );

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments-checkout',
                'payments-checkout.template.php',
                'templates'
            ));


        }

    }

    // Make sure the addon is loaded
    new CUAR_PaymentsCheckoutAddOn();

endif; // if (!class_exists('CUAR_PaymentsCheckoutAddOn')) :
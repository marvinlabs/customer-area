<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

if ( !class_exists('CUAR_AddressHelper')) :

    /**
     * Gathers some helper functions to facilitate some coding
     */
    class CUAR_AddressHelper
    {
        public static $DEFAULT_HOME_ADDRESS_ID = 'home_address';
        public static $DEFAULT_BILLING_ADDRESS_ID = 'billing_address';

        /**
         * Make sure we always have a proper address
         *
         * @param array $address
         *
         * @return array
         */
        public static function sanitize_address($address)
        {
            $address = array_merge(array(
                'name'    => '',
                'company' => '',
                'line1'   => '',
                'line2'   => '',
                'zip'     => '',
                'city'    => '',
                'country' => '',
                'state'   => ''
            ), $address);

            if (empty($address['country'])) $address['state'] = '';

            $address['name'] = stripslashes($address['name']);
            $address['company'] = stripslashes($address['company']);
            $address['line1'] = stripslashes($address['line1']);
            $address['line2'] = stripslashes($address['line2']);
            $address['zip'] = stripslashes($address['zip']);
            $address['city'] = stripslashes($address['city']);
            $address['country'] = stripslashes($address['country']);
            $address['state'] = stripslashes($address['state']);

            return apply_filters('cuar/core/address', $address);
        }
    }

endif; // class_exists CUAR_AddressHelper
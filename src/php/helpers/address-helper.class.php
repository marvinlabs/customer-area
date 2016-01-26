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
         * Compare two addresses
         *
         * @param array $a
         * @param array $b
         *
         * @return bool true if addresses are the same
         */
        public static function compare_addresses($a, $b)
        {
            return 0 == strcmp($a['name'], $b['name'])
            && 0 == strcmp($a['company'], $b['company'])
            && 0 == strcmp($a['vat_number'], $b['vat_number'])
            && 0 == strcmp($a['logo_url'], $b['logo_url'])
            && 0 == strcmp($a['line1'], $b['line1'])
            && 0 == strcmp($a['line2'], $b['line2'])
            && 0 == strcmp($a['zip'], $b['zip'])
            && 0 == strcmp($a['city'], $b['city'])
            && 0 == strcmp($a['country'], $b['country'])
            && 0 == strcmp($a['state'], $b['state']);
        }

        /**
         * Make sure we always have a proper address
         *
         * @param array $address
         *
         * @return array
         */
        public static function sanitize_address($address)
        {
            if (!empty($address['vat-number'])) $address['vat_number'] = $address['vat-number'];
            if (!empty($address['logo-url'])) $address['logo_url'] = $address['logo-url'];

            $address = array_merge(array(
                'name'       => '',
                'company'    => '',
                'vat_number' => '',
                'logo_url'   => '',
                'line1'      => '',
                'line2'      => '',
                'zip'        => '',
                'city'       => '',
                'country'    => '',
                'state'      => '',
            ), $address);

            if (empty($address['country'])) $address['state'] = '';

            $address['name'] = stripslashes($address['name']);
            $address['company'] = stripslashes($address['company']);
            $address['vat_number'] = stripslashes($address['vat_number']);
            $address['logo_url'] = stripslashes($address['logo_url']);
            $address['line1'] = stripslashes($address['line1']);
            $address['line2'] = stripslashes($address['line2']);
            $address['zip'] = stripslashes($address['zip']);
            $address['city'] = stripslashes($address['city']);
            $address['country'] = stripslashes($address['country']);
            $address['state'] = stripslashes($address['state']);

            return apply_filters('cuar/core/address', $address);
        }

        /**
         * @param $address
         *
         * @return mixed|void
         */
        public static function get_address_as_string($address)
        {
            $out = '';
            if ( !empty($address['name']))
            {
                $out .= $address['name'];
            }

            if ( !empty($address['company']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['company'];
            }

            if ( !empty($address['vat_number']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['vat_number'];
            }

            if ( !empty($address['line1']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['line1'];
            }

            if ( !empty($address['line2']))
            {
                $out .= $address['line2'];
            }

            if ( !empty($address['zip']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['zip'];
            }

            if ( !empty($address['city']))
            {
                if ( !empty($address['zip'])) $out .= ' ';
                $out .= $address['city'];
            }

            if ( !empty($address['state']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['state'];
            }

            if ( !empty($address['country']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['country'];
            }

            return apply_filters('cuar/core/address-as-string', $out, $address);
        }

        /**
         * @param $address
         *
         * @return mixed|void
         */
        public static function get_address_as_short_string($address)
        {
            $out = '';
            if ( !empty($address['line1']))
            {
                $out .= $address['line1'];
            }

            if ( !empty($address['line2']))
            {
                $out .= $address['line2'];
            }

            if ( !empty($address['zip']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['zip'];
            }

            if ( !empty($address['city']))
            {
                if ( !empty($address['zip'])) $out .= ' ';
                $out .= $address['city'];
            }

            if ( !empty($address['country']))
            {
                if ( !empty($out)) $out .= ', ';
                $out .= $address['country'];
            }

            return apply_filters('cuar/core/address-as-short-string', $out, $address);
        }
    }

endif; // class_exists CUAR_AddressHelper
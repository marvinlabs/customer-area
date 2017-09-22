<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('CUAR_GeneralHelper')) :

    /**
     * Gathers some helper functions to facilitate some coding
     */
    class CUAR_GeneralHelper
    {

        public static function get_timezone_id()
        {
            // if site timezone string exists, return it
            if ($timezone = get_option('timezone_string'))
                return $timezone;

            // get UTC offset, if it isn't set return UTC
            if (!($utc_offset = 3600 * get_option('gmt_offset', 0)))
                return 'UTC';

            // attempt to guess the timezone string from the UTC offset
            $timezone = timezone_name_from_abbr('', $utc_offset);

            // last try, guess timezone string manually
            if ($timezone === false) {

                $is_dst = date('I');

                foreach (timezone_abbreviations_list() as $abbr) {
                    foreach ($abbr as $city) {
                        if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset)
                            return $city['timezone_id'];
                    }
                }
            }

            // fallback
            return 'UTC';
        }

        public static function get_ip()
        {
            $ip = '127.0.0.1';

            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            return apply_filters('cuar/core/get-ip', $ip);
        }

    }

endif; // class_exists CUAR_GeneralHelper
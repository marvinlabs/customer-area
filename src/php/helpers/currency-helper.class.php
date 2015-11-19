<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

if ( !class_exists('CUAR_CurrencyHelper')) :

    /**
     * Gathers some helper functions to facilitate some coding
     */
    class CUAR_CurrencyHelper
    {

        /**
         * Ge the name of a currency given the currency code
         *
         * @param string $code currency code
         *
         * @return string
         */
        public static function getCurrencyName($code)
        {
            $currencies = self::getCurrencies();

            return isset($currencies[$code]) ? $currencies[$code] : '-';
        }

        /**
         * Get all currencies known to Customer Area, indexed by 2-letter ISO currency code
         *
         * @return array
         */
        public static function getCurrencies()
        {
            return apply_filters('cuar/core/currencies', array(
                'AUD'  => __('Australian Dollar (&#36;)', 'cuar'),
                'BRL'  => __('Brazilian Real (R&#36;)', 'cuar'),
                'CAD'  => __('Canadian Dollar (&#36;)', 'cuar'),
                'CZK'  => __('Czech Koruna', 'cuar'),
                'DKK'  => __('Danish Krone', 'cuar'),
                'EUR'  => __('Euro (&euro;)', 'cuar'),
                'HKD'  => __('Hong Kong Dollar (&#36;)', 'cuar'),
                'HUF'  => __('Hungarian Forint', 'cuar'),
                'INR'  => __('Indian Rupee (&#8377;)', 'cuar'),
                'RIAL' => __('Iranian Rial (&#65020;)', 'cuar'),
                'ILS'  => __('Israeli Shekel (&#8362;)', 'cuar'),
                'JPY'  => __('Japanese Yen (&yen;)', 'cuar'),
                'MYR'  => __('Malaysian Ringgit', 'cuar'),
                'MXN'  => __('Mexican Peso (&#36;)', 'cuar'),
                'NZD'  => __('New Zealand Dollar (&#36;)', 'cuar'),
                'NOK'  => __('Norwegian Krone', 'cuar'),
                'PHP'  => __('Philippine Peso', 'cuar'),
                'PLN'  => __('Polish Zloty', 'cuar'),
                'GBP'  => __('Pounds Sterling (&pound;)', 'cuar'),
                'RUB'  => __('Russian Ruble', 'cuar'),
                'SGD'  => __('Singapore Dollar (&#36;)', 'cuar'),
                'SEK'  => __('Swedish Krona', 'cuar'),
                'CHF'  => __('Swiss Franc', 'cuar'),
                'TWD'  => __('Taiwan New Dollar', 'cuar'),
                'THB'  => __('Thai Baht (&#3647;)', 'cuar'),
                'TRY'  => __('Turkish Lira (&#8378;)', 'cuar'),
                'USD'  => __('US Dollar (&#36;)', 'cuar')
            ));
        }

        /**
         * Get the states of a currency given the ISO-2 currency code
         *
         * @param string $currencyCode 2-letter currency code
         *
         * @return array
         */
        public static function getSymbol($currencyCode)
        {
            switch ($currencyCode)
            {
                case "GBP" :
                    $symbol = '&pound;';
                    break;

                case "BRL" :
                    $symbol = 'R&#36;';
                    break;

                case "EUR" :
                    $symbol = '&euro;';
                    break;

                case "USD" :
                case "AUD" :
                case "NZD" :
                case "CAD" :
                case "HKD" :
                case "MXN" :
                case "SGD" :
                    $symbol = '&#36;';
                    break;

                case "JPY" :
                    $symbol = '&yen;';
                    break;

                default :
                    $symbol = $currencyCode;
                    break;
            }

            return apply_filters('cuar/core/currency-symbol?currency=' . $currencyCode, $symbol);
        }

        public static function formatAmount($amount, $currencyCode, $wrapper = 'span')
        {
            $format = _x('%2$s %1$s', 'Currency amount (1=amount, 2=currency)', 'cuar');
            $symbol = self::getSymbol($currencyCode);
            $amount = number_format_i18n(doubleval($amount), 2);

            if ($wrapper != null)
            {
                $symbol = '<' . $wrapper . ' class="cuar-currency">' . $symbol . '</' . $wrapper . '>';
                $amount = '<' . $wrapper . ' class="cuar-amount">' . $amount . '</' . $wrapper . '>';
            }

            return sprintf($format, $amount, $symbol);
        }
    }

endif; // class_exists CUAR_CurrencyHelper
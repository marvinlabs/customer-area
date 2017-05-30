<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_ChequePaymentGateway extends CUAR_AbstractPaymentGateway
{
    public static $OPTION_SUCCESS_MESSAGE = 'success_message';
    public static $OPTION_CHECKOUT_MESSAGE = 'checkout_message';

    /**
     * CUAR_AbstractPaymentGateway constructor.
     *
     * @param CUAR_Plugin $plugin
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    //-- CUAR_PaymentGateway implementation -------------------------------------------------------------------------------------------------------------------/

    public function get_id()
    {
        return 'cheque';
    }

    public function get_name()
    {
        return __('Cheque', 'cuar');
    }

    public function get_description()
    {
        return __('The cheque gateway allows you to accept payments via cheque. Your instructions will be shown to the user after he validates the payment. Payments status will be left to pending and you will need to manually accept them once the money gets on your bank account.', 'cuar');
    }

    public function has_form()
    {
        return true;
    }

    public function process_payment($payment, $payment_data, $gateway_params)
    {
        $message = $this->get_success_message();
        $this->redirect_to_success_page($payment->ID, $message);
    }

    //-- Settings helper functions ----------------------------------------------------------------------------------------------------------------------------/

    public function validate_options($validated, $cuar_settings, $input)
    {
        $validated = parent::validate_options($validated, $cuar_settings, $input);

        $cuar_settings->validate_always($input, $validated, $this->get_option_id(self::$OPTION_SUCCESS_MESSAGE));
        $cuar_settings->validate_always($input, $validated, $this->get_option_id(self::$OPTION_CHECKOUT_MESSAGE));

        return $validated;
    }

    public function get_success_message()
    {
        $message = $this->get_option(self::$OPTION_SUCCESS_MESSAGE);

        return apply_filters('cuar/core/payments/gateway/cheque/success-message', $message);
    }

    public function get_checkout_message()
    {
        $message = $this->get_option(self::$OPTION_CHECKOUT_MESSAGE);

        return apply_filters('cuar/core/payments/gateway/cheque/checkout-message', $message);
    }

    public static function set_default_options($defaults)
    {
        $g = new CUAR_ChequePaymentGateway(cuar());

        $defaults[$g->get_option_id(self::$OPTION_ENABLED)] = true;
        $defaults[$g->get_option_id(self::$OPTION_LOG_ENABLED)] = false;

        $defaults[$g->get_option_id(self::$OPTION_SUCCESS_MESSAGE)] =
            "<p>"
            . __('Please send your cheque transfer to the following address. Once the cheque has been received and deposited on our account, we will validate your payment as soon as possible.',
                'cuar')
            . "</p>\n\n"
            . "<p><strong>" . __('Write the cheque to the order of:', 'cuar') . "</strong></p>\n"
            . "<p>" . __('Your name or company name', 'cuar') . "</p>\n\n"
            . "<p><strong>" . __('Send to the address:', 'cuar') . "</strong></p>\n\n"
            . "<address>\n"
            . __('Your name', 'cuar') . "<br>\n"
            . __('Street address', 'cuar') . "<br>\n"
            . __('Zip City', 'cuar') . "<br>\n"
            . __('Country', 'cuar') . "<br>\n"
            . "</address>\n";

        $defaults[$g->get_option_id(self::$OPTION_CHECKOUT_MESSAGE)] =
            __('Once the cheque has been received and deposited on our account, we will validate your payment as soon as possible.', 'cuar');

        return $defaults;
    }

}
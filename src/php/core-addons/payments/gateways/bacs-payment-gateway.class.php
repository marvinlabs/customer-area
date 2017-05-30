<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_BacsPaymentGateway extends CUAR_AbstractPaymentGateway
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
        return 'bacs';
    }

    public function get_name()
    {
        return __('Wired transfer', 'cuar');
    }

    public function get_description()
    {
        return __('The BACS gateway allows you to accept payments via wired transfers (aka direct bank transfer). Your bank account information will be shown to the user after he validates the payment. Payments status will be left to pending and you will need to manually accept them once the money gets on your bank account.', 'cuar');
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

        return apply_filters('cuar/core/payments/gateway/bacs/success-message', $message);
    }

    public function get_checkout_message()
    {
        $message = $this->get_option(self::$OPTION_CHECKOUT_MESSAGE);

        return apply_filters('cuar/core/payments/gateway/bacs/checkout-message', $message);
    }

    public static function set_default_options($defaults)
    {
        $g = new CUAR_BacsPaymentGateway(cuar());

        $defaults[$g->get_option_id(self::$OPTION_ENABLED)] = true;
        $defaults[$g->get_option_id(self::$OPTION_LOG_ENABLED)] = false;

        $defaults[$g->get_option_id(self::$OPTION_SUCCESS_MESSAGE)] =
            "<p>"
            . __('Please send your wired transfer to the following bank account. We will monitor our bank account and validate your payment as soon as possible.',
                'cuar')
            . "</p>\n\n"
            . "<table class='table'>"
            . "<tr><td><strong>" . __('Name', 'cuar') . "</strong></td><td>Your name here</td></tr>\n"
            . "<tr><td><strong>" . __('Bank', 'cuar') . "</strong></td><td>Bank name and address</td></tr>\n"
            . "<tr><td><strong>" . __('IBAN', 'cuar') . "</strong></td><td>IBAN account number</td></tr>\n"
            . "<tr><td><strong>" . __('SWIFT', 'cuar') . "</strong></td><td>Swift code</td></tr>\n"
            . "</table>\n";

        $defaults[$g->get_option_id(self::$OPTION_CHECKOUT_MESSAGE)] =
            __('We will monitor our bank account and validate your payment as soon as possible.', 'cuar');

        return $defaults;
    }

}
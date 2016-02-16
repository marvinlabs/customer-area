<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_BacsPaymentGateway extends CUAR_AbstractPaymentGateway
{
    public static $OPTION_SUCCESS_MESSAGE = 'success_message';

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

    public function get_icon()
    {
        return array(
            'icon' => CUAR_PLUGIN_URL . 'assets/common/img/gateways/bacs.png',
            'link' => '',
        );
    }

    public function process_payment($payment, $payment_data, $gateway_params)
    {
        $message = apply_filters('cuar/core/payments/gateway/bacs/success-message',
            __('We will monitor our bank account and validate your payment as soon as possible.', 'cuar'));

        $this->redirect_to_success_page($message);
    }

    //-- Settings helper functions ----------------------------------------------------------------------------------------------------------------------------/

    public function validate_options($validated, $cuar_settings, $input)
    {
        $validated = parent::validate_options($validated, $cuar_settings, $input);

        $cuar_settings->validate_always($input, $validated, $this->get_option_id(self::$OPTION_SUCCESS_MESSAGE));

        return $validated;
    }

    public function get_success_message()
    {
        $message = $this->get_option(self::$OPTION_SUCCESS_MESSAGE);

        return apply_filters('cuar/core/payments/gateway/bacs/success-message', $message);
    }

    public function set_default_options($defaults)
    {
        $defaults[$this->get_option_id(self::$OPTION_SUCCESS_MESSAGE)] = __('We will monitor our bank account and validate your payment as soon as possible.', 'cuar');
    }

}
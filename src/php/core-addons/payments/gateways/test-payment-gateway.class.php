<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_TestPaymentGateway extends CUAR_AbstractPaymentGateway
{

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
        return 'test';
    }

    public function get_name()
    {
        return __('Test gateway', 'cuar');
    }

    public function has_form()
    {
        return true;
    }

    public function process_payment($payment, $payment_data, $gateway_params)
    {
        switch ($gateway_params['expected_result'])
        {
            case 'success':
                $this->redirect_to_success_page(__('The test gateway accepted the payment', 'cuar'));
                break;

            case 'rejected':
                $this->redirect_to_failure_page(__('The test gateway failed to process the payment', 'cuar'));
                break;

            case 'pending':
                $this->redirect_to_success_page(__('The test gateway did not validate the payment yet', 'cuar'));
                break;

            default:
                die('Unhandled test gateway expected result value');
        }
    }

    //-- Settings helper functions ----------------------------------------------------------------------------------------------------------------------------/

    public function validate_options($validated, $cuar_settings, $input)
    {
        $validated = parent::validate_options($validated, $cuar_settings, $input);

        // $cuar_settings->validate_boolean($input, $validated, $this->get_option_id(self::$OPTION_ENABLED));

        return $validated;
    }

}
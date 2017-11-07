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

    public function get_description()
    {
        return __('The test gateway allows you to see how your website is behaving when the gateways return different results.', 'cuar');
    }

    public function has_form()
    {
        return true;
    }

    public function process_payment($payment, $payment_data, $gateway_params)
    {
        $this->log(sprintf(__('PAYMENT %s - Payment processed, status set to: %s', 'cuar'), $payment->ID, $gateway_params['expected_result']));

        switch ($gateway_params['expected_result']) {
            case 'success':
                // In case of success we update the payment status
                $payment->update_status(CUAR_PaymentStatus::$STATUS_COMPLETE, $this->get_name());

                // Finally redirect to the success page
                $this->redirect_to_success_page($payment->ID, __('The test gateway accepted the payment', 'cuar'));
                break;

            case 'rejected':
                // In case of failure we update the payment status
                $payment->update_status(CUAR_PaymentStatus::$STATUS_FAILED, $this->get_name());

                // Finally redirect to the success page
                $this->redirect_to_failure_page($payment->ID, __('The test gateway failed to process the payment', 'cuar'));
                break;

            case 'pending':
                // We do not change the payment status but show success with a message to tell the user we are working on it
                $this->redirect_to_success_page($payment->ID, __('The test gateway did not validate the payment yet', 'cuar'));
                break;

            default:
                die('Unhandled test gateway expected result value');
        }
    }

    //-- Settings helper functions ----------------------------------------------------------------------------------------------------------------------------/

    public static function set_default_options($defaults)
    {
        $g = new CUAR_TestPaymentGateway(cuar());

        $defaults[$g->get_option_id(self::$OPTION_ENABLED)] = true;
        $defaults[$g->get_option_id(self::$OPTION_LOG_ENABLED)] = true;

        return $defaults;
    }
}
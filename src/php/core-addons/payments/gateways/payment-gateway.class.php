<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

interface CUAR_PaymentGateway
{

    //-- General functions ------------------------------------------------------------------------------------------------------------------------------------/

    /**
     * @return string The gateway's unique ID
     */
    function get_id();

    /**
     * @return string The gateway's unique ID when listening for callback functions
     */
    function get_listener_id();

    /**
     * @return string The gateway's name
     */
    function get_name();

    /**
     * @return string The gateway's description
     */
    function get_description();

    /**
     * @return bool Is the gateway enabled
     */
    function is_enabled();

    /**
     * Process the payment which has been created with the pending status
     *
     * @param CUAR_Payment $payment        The payment object
     * @param array        $payment_data   The payment data as submitted in the checkout form
     * @param array        $gateway_params The parameters from the gateway form
     */
    function process_payment($payment, $payment_data, $gateway_params);

    /**
     * Process the result from the payment gateway
     */
    function process_callback();

    //-- UI functions -----------------------------------------------------------------------------------------------------------------------------------------/

    /**
     * @return bool
     */
    function has_form();

    /**
     *
     */
    function print_form();

    //-- Settings functions -----------------------------------------------------------------------------------------------------------------------------------/

    /**
     * Add our fields to the settings tab
     *
     * @param CUAR_Settings $settings
     *
     * @return
     */
    function print_settings($settings);

    /**
     * Validate our options
     *
     * @param CUAR_Settings $cuar_settings
     * @param array         $input
     * @param array         $validated
     *
     * @return array
     */
    function validate_options($validated, $cuar_settings, $input);

}
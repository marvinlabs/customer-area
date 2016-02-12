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
     * @return string The gateway's name
     */
    function get_name();

    /**
     * @return bool Is the gateway enabled
     */
    function is_enabled();

    //-- Settings functions -----------------------------------------------------------------------------------------------------------------------------------/

    /**
     * Add our fields to the settings tab
     */
    function print_settings();

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
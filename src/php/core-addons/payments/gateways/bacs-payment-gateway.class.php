<?php
/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_BacsPaymentGateway extends CUAR_AbstractPaymentGateway
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
        return 'bacs';
    }

    public function get_name()
    {
        return __('Wired transfer', 'cuar');
    }

    //-- Settings helper functions ----------------------------------------------------------------------------------------------------------------------------/

    public function validate_options($validated, $cuar_settings, $input)
    {
        $validated = parent::validate_options($validated, $cuar_settings, $input);

        // $cuar_settings->validate_boolean($input, $validated, $this->get_option_id(self::$OPTION_ENABLED));

        return $validated;
    }

}
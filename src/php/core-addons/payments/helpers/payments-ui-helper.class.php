<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsUiHelper
{
    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PaymentsAddOn */
    private $pa_addon;

    /**
     * Constructor
     *
     * @param CUAR_Plugin        $plugin
     * @param CUAR_PaymentsAddOn $pa_addon
     */
    public function __construct($plugin, $pa_addon)
    {
        $this->plugin = $plugin;
        $this->pa_addon = $pa_addon;
    }

    /**
     * Show a button which leads to the page where the user can pay for the given object
     *
     * @param string $object_type
     * @param int    $object_id
     * @param double $amount
     * @param string $currency
     * @param string $label The label to show on the button
     * @param array  $address
     */
    public function show_payment_button($object_type, $object_id, $amount, $currency, $label, $address)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $gateways = $this->pa_addon->settings()->get_enabled_gateways();
        if (empty($gateways)) return;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $accepted_credit_cards = $this->pa_addon->settings()->get_enabled_credit_cards();

        $template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-button-' . $object_type . '.template.php',
                'payment-button.template.php',
            ),
            'templates');

        include($template);
    }
}
<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsSettingsHelper
{
    public static $OPTION_DEFAULT_STATUS = 'cuar_in_default_status';
    public static $OPTION_CLOSED_STATUSES = 'cuar_in_closed_statuses';
    public static $OPTION_DEFAULT_CURRENCY = 'cuar_in_default_currency';
    public static $OPTION_DEFAULT_EMITTER = 'cuar_in_default_emitter';
    public static $OPTION_TAX_RATES = 'cuar_in_tax_rates';
    public static $OPTION_IS_SEQUENTIAL_NUMBERS_ENABLED = 'cuar_in_generate_sequential_numbers';
    public static $OPTION_NUMBER_FORMAT = 'cuar_in_number_format';
    public static $OPTION_SEQUENCE_NUMBER_CURRENT = 'cuar_in_current_sequence_number';
    public static $OPTION_SEQUENCE_NUMBER_START = 'cuar_in_initial_sequence_number';
    public static $OPTION_DEFAULT_HEADER = 'cuar_in_default_header';
    public static $OPTION_DEFAULT_FOOTER = 'cuar_in_default_footer';

    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PaymentsAddOn */
    private $pa_addon;

    /**
     * Constructor
     */
    public function __construct($plugin, $pa_addon)
    {
        $this->plugin = $plugin;
        $this->pa_addon = $pa_addon;

        if (is_admin())
        {
            // Add a settings tab for payments
            add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_settings_tab'), 530, 1);
            add_action('cuar/core/settings/print-settings?tab=cuar_payments', array(&$this, 'print_settings'), 10, 2);
            add_filter('cuar/core/settings/validate-settings?tab=cuar_payments', array(&$this, 'validate_options'), 10, 3);
        }

        // Default invoice properties provided by settings
        add_filter('cuar/private-content/payments/emitter-address', array(&$this, 'provide_default_invoice_emitter_address'), 10, 2);
        add_filter('cuar/private-content/payments/status', array(&$this, 'provide_default_invoice_status'), 10, 2);
        add_filter('cuar/private-content/payments/currency', array(&$this, 'provide_default_invoice_currency'), 10, 2);
        add_filter('cuar/private-content/payments/tax-rate', array(&$this, 'provide_default_invoice_tax_rate'), 10, 2);
        add_filter('cuar/private-content/payments/generate-new-number', array(&$this, 'provide_new_invoice_number'), 10, 2);
        add_filter('cuar/private-content/payments/number', array(&$this, 'format_invoice_number'), 100, 2);
        add_filter('cuar/private-content/payments/header', array(&$this, 'provide_default_invoice_header'), 10, 2);
        add_filter('cuar/private-content/payments/footer', array(&$this, 'provide_default_invoice_footer'), 10, 2);
    }

    //------- PAYMENT DEFAULTS --------------------------------------------------------------------------------------------------------------------------------/

    /**
     * Make sure the payments always return a default address
     *
     * @param array        $tax_rate
     * @param CUAR_Invoice $invoice
     *
     * @return int
     */
    public function provide_default_invoice_tax_rate($tax_rate, $invoice)
    {
        if ($tax_rate == null)
        {
            $tax_rate = $this->get_default_tax_rate();
        }

        return $tax_rate;
    }

    //------- SETTINGS ----------------------------------------------------------------------------------------------------------------------------------------/

    /**
     * Set the default values for the options
     *
     * @param array $defaults
     *
     * @return array
     */
    public static function set_default_options($defaults)
    {
        // $defaults[self::$OPTION_DEFAULT_STATUS] = -1;

        return $defaults;
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_available_gateways()
    {
        return apply_filters('cuar/private-content/payments/gateways', array(
            'bacs' => new CUAR_BacsPaymentGateway($this->plugin),
            'test' => new CUAR_TestPaymentGateway($this->plugin),
        ));
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_enabled_gateways()
    {
        $gateways = $this->get_available_gateways();

        /** @var CUAR_PaymentGateway $gateway */
        foreach ($gateways as $id => $gateway)
        {
            // TODO Read settings for that gateway to see if it is enabled
        }

        return $gateways;
    }

    //------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE -------------------------------------------------------------------------------------------------------/

    /**
     * Add a tab to the settings page
     *
     * @param array $tabs
     *
     * @return array
     */
    public function add_settings_tab($tabs)
    {
        $tabs['cuar_payments'] = __('Payments', 'cuar');

        return $tabs;
    }

    /**
     * Add our fields to the settings page
     *
     * @param CUAR_Settings $cuar_settings The settings class
     * @param string        $options_group
     */
    public function print_settings($cuar_settings, $options_group)
    {
//        add_settings_section(
//            'cuar_payments_general',
//            __('General settings', 'cuar'),
//            array(&$cuar_settings, 'print_empty_section_info'),
//            CUAR_Settings::$OPTIONS_PAGE_SLUG
//        );
//
//        $gateways = $this->get_available_gateways();
//
//        /** @var CUAR_PaymentGateway $gateway */
//        foreach ($gateways as $gateway)
//        {
//            $defaults = $gateway->set_default_options($defaults);
//        }

        add_settings_section(
            'cuar_payments_gateways',
            __('Gateways', 'cuar'),
            array(&$this, 'print_gateways_section_info'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG
        );
    }

    /**
     * Validate our options
     *
     * @param CUAR_Settings $cuar_settings
     * @param array         $input
     * @param array         $validated
     *
     * @return array
     */
    public function validate_options($validated, $cuar_settings, $input)
    {
        $gateways = $this->get_available_gateways();

        /** @var CUAR_PaymentGateway $gateway */
        foreach ($gateways as $gateway)
        {
            $validated  = $gateway->validate_options($validated, $cuar_settings, $input);
        }

        return $validated;
    }

    /**
     * Print some info about the section
     */
    public function print_gateways_section_info()
    {
        wp_enqueue_script('jquery-ui-tabs');

        /** @noinspection PhpUnusedLocalVariableInspection */
        $gateways = $this->get_available_gateways();

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            'gateways-table.template.php',
            'templates'));
    }
}
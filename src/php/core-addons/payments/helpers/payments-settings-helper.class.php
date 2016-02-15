<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsSettingsHelper
{
    public static $OPTION_ENABLED_CREDIT_CARDS = 'cuar_enabled_credit_cards';

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
        // add_filter('cuar/private-content/payments/tax-rate', array(&$this, 'provide_default_invoice_tax_rate'), 10, 2);
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
            // $tax_rate = $this->get_default_tax_rate();
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
        $defaults[self::$OPTION_ENABLED_CREDIT_CARDS] = array('visa', 'mastercard', 'maestro');

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
            if ( !$gateway->is_enabled()) unset($gateways[$id]);
        }

        return $gateways;
    }

    /**
     * @return array The IDs of enabled credit cards
     */
    public function get_enabled_credit_card_ids()
    {
        return $this->plugin->get_option(self::$OPTION_ENABLED_CREDIT_CARDS);
    }

    /**
     * @return array A description of all available credit cards
     */
    public function get_available_credit_cards()
    {
        $img_path = CUAR_PLUGIN_URL . 'assets/common/img/credit-cards/';

        return apply_filters('cuar/private-content/payments/credit-cards', array(
            'visa'       => array(
                'label' => __('Visa', 'cuar'),
                'icon'  => $img_path . 'visa.png',
            ),
            'amex'       => array(
                'label' => __('American Express', 'cuar'),
                'icon'  => $img_path . 'amex.png',
            ),
            'diners'     => array(
                'label' => __('Diner\'s Club', 'cuar'),
                'icon'  => $img_path . 'diners.png',
            ),
            'maestro'    => array(
                'label' => __('Maestro', 'cuar'),
                'icon'  => $img_path . 'maestro.png',
            ),
            'mastercard' => array(
                'label' => __('Master Card', 'cuar'),
                'icon'  => $img_path . 'mastercard.png',
            ),
            'discover'   => array(
                'label' => __('Discover', 'cuar'),
                'icon'  => $img_path . 'discover.png',
            ),
            'jcb'        => array(
                'label' => __('JCB International', 'cuar'),
                'icon'  => $img_path . 'jcb.png',
            ),
        ));
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_enabled_credit_cards()
    {
        $cards = $this->get_available_credit_cards();
        $enabled = $this->get_enabled_credit_card_ids();

        /** @var CUAR_PaymentGateway $gateway */
        foreach ($cards as $id => $card)
        {
            if ( !in_array($id, $enabled)) unset($cards[$id]);
        }

        return apply_filters('cuar/private-content/payments/enabled-credit-cards', $cards);
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
        add_settings_section(
            'cuar_payments_general',
            __('General settings', 'cuar'),
            array(&$cuar_settings, 'print_empty_section_info'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG
        );

        add_settings_field(
            self::$OPTION_ENABLED_CREDIT_CARDS,
            __("Credit Cards", 'cuarin'),
            array(&$cuar_settings, 'print_select_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_payments_general',
            array(
                'option_id' => self::$OPTION_ENABLED_CREDIT_CARDS,
                'options'   => $this->get_selectable_credit_card_options(),
                'multiple'  => true,
                'after'     => '<p class="description">' . __('Show the following credit card logos next to the payment button', 'cuar') . '</p>',
            )
        );



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
        $cuar_settings->validate_select($input, $validated, self::$OPTION_ENABLED_CREDIT_CARDS, $this->get_selectable_credit_card_options(), true);

        $gateways = $this->get_available_gateways();
        /** @var CUAR_PaymentGateway $gateway */
        foreach ($gateways as $gateway)
        {
            $validated = $gateway->validate_options($validated, $cuar_settings, $input);
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

    /**
     * @return array
     */
    private function get_selectable_credit_card_options()
    {
        $cc_options = $this->get_available_credit_cards();
        foreach ($cc_options as $id => $card)
        {
            $cc_options[$id] = $card['label'];
        }

        return $cc_options;
    }
}
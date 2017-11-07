<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsSettingsHelper
{
    public static $OPTION_ENABLED_PAYMENT_ICON_PACK = 'cuar_enabled_payment_icon_pack';
    public static $OPTION_ENABLED_PAYMENT_ICONS = 'cuar_enabled_payment_icons_';
    public static $OPTION_DEFAULT_GATEWAY = 'cuar_default_gateway';

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

        if (is_admin()) {
            // Add a settings tab for payments
            add_filter('cuar/core/settings/settings-tabs', array(&$this, 'add_settings_tab'), 530, 1);
            add_action('cuar/core/settings/print-settings?tab=cuar_payments', array(&$this, 'print_settings'), 10, 2);
            add_filter('cuar/core/settings/validate-settings?tab=cuar_payments', array(&$this, 'validate_options'), 10, 3);
        }

        // Default invoice properties provided by settings
        // add_filter('cuar/core/payments/tax-rate', array(&$this, 'provide_default_invoice_tax_rate'), 10, 2);
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
        if ($tax_rate == null) {
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
        $defaults[self::$OPTION_ENABLED_PAYMENT_ICON_PACK] = 'color-light';
        $defaults[self::$OPTION_ENABLED_PAYMENT_ICONS . 'color-light'] = array('visa', 'mastercard');

        // For gateways too
        $defaults = CUAR_TestPaymentGateway::set_default_options($defaults);
        $defaults = CUAR_BacsPaymentGateway::set_default_options($defaults);
        $defaults = CUAR_ChequePaymentGateway::set_default_options($defaults);

        return $defaults;
    }

    /**
     * @return CUAR_PaymentGateway|bool The gateway or false if not available
     */
    public function get_gateway($gateway_id)
    {
        $gateways = $this->get_enabled_gateways();

        return isset($gateways[$gateway_id]) ? $gateways[$gateway_id] : false;
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_available_gateways()
    {
        return apply_filters('cuar/core/payments/gateways', array(
            'bacs'   => new CUAR_BacsPaymentGateway($this->plugin),
            'cheque' => new CUAR_ChequePaymentGateway($this->plugin),
            'test'   => new CUAR_TestPaymentGateway($this->plugin),
        ));
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_available_gateways_for_select()
    {
        $gw = $this->get_available_gateways();
        $out = array();
        foreach ($gw as $g) {
            $out[$g->get_id()] = $g->get_name();
        }

        return $out;
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_enabled_gateways()
    {
        $gateways = $this->get_available_gateways();

        /** @var CUAR_PaymentGateway $gateway */
        foreach ($gateways as $id => $gateway) {
            if ( !$gateway->is_enabled()) unset($gateways[$id]);
        }

        return $gateways;
    }

    /**
     * @return string The gateway selected by default
     */
    public function get_default_gateway()
    {
        return $this->plugin->get_option(self::$OPTION_DEFAULT_GATEWAY);
    }

    /**
     * @return array The enabled payment icon pack
     */
    public function get_enabled_payment_icon_pack()
    {
        $id = $this->plugin->get_option(self::$OPTION_ENABLED_PAYMENT_ICON_PACK);
        $packs = $this->get_available_payment_icon_packs();

        return isset($packs) ? $packs[$id] : $packs['color-light'];
    }

    /**
     * @return array The IDs of enabled payment icons
     */
    public function get_enabled_payment_icon_ids()
    {
        $icon_pack = $this->get_enabled_payment_icon_pack();

        return $this->plugin->get_option(self::$OPTION_ENABLED_PAYMENT_ICONS . $icon_pack['id']);
    }

    /**
     * @return array A description of all available payment icons
     */
    public function get_available_payment_icon_packs()
    {
        return apply_filters('cuar/core/payments/payment-icons/packs', array(
            'color-light' => array(
                'id'    => 'color-light',
                'label' => __('Colorful Light', 'cuar'),
                'path'  => CUAR_PLUGIN_URL . '/assets/common/img/payment-icons/color-light/',
            ),
        ));
    }

    /**
     * @return array A description of all available payment icons
     */
    public function get_available_payment_icons($icon_pack)
    {
        if ($icon_pack['id'] != 'color-light') {
            return apply_filters('cuar/core/payments/payment-icons/available?pack=' . $icon_pack['id'], array(), $icon_pack);
        }

        return array(
            '2checkout'           => array(
                'label' => __('2 Checkout', 'cuar'),
                'icon'  => $icon_pack['path'] . '2checkout.png',
            ),
            'amazon'              => array(
                'label' => __('Amazon', 'cuar'),
                'icon'  => $icon_pack['path'] . 'amazon.png',
            ),
            'amazon-a'            => array(
                'label' => __('Amazon', 'cuar'),
                'icon'  => $icon_pack['path'] . 'amazon-a.png',
            ),
            'amazon-payments'     => array(
                'label' => __('Amazon Payments', 'cuar'),
                'icon'  => $icon_pack['path'] . 'amazon-payments.png',
            ),
            'american-express'    => array(
                'label' => __('American Express', 'cuar'),
                'icon'  => $icon_pack['path'] . 'american-express.png',
            ),
            'amex'                => array(
                'label' => __('Amex', 'cuar'),
                'icon'  => $icon_pack['path'] . 'amex.png',
            ),
            'chase'               => array(
                'label' => __('Chase', 'cuar'),
                'icon'  => $icon_pack['path'] . 'chase.png',
            ),
            'chase-2'             => array(
                'label' => __('Chase', 'cuar'),
                'icon'  => $icon_pack['path'] . 'chase-2.png',
            ),
            'cirrus'              => array(
                'label' => __('Cirrus', 'cuar'),
                'icon'  => $icon_pack['path'] . 'cirrus.png',
            ),
            'delta'               => array(
                'label' => __('Delta', 'cuar'),
                'icon'  => $icon_pack['path'] . 'delta.png',
            ),
            'diners-club'         => array(
                'label' => __('Diner\'s Club', 'cuar'),
                'icon'  => $icon_pack['path'] . 'diners-club.png',
            ),
            'direct-debit'        => array(
                'label' => __('Direct Debit', 'cuar'),
                'icon'  => $icon_pack['path'] . 'direct-debit.png',
            ),
            'discover'            => array(
                'label' => __('Discover', 'cuar'),
                'icon'  => $icon_pack['path'] . 'discover.png',
            ),
            'ebay'                => array(
                'label' => __('Ebay', 'cuar'),
                'icon'  => $icon_pack['path'] . 'ebay.png',
            ),
            'etsy'                => array(
                'label' => __('Etsy', 'cuar'),
                'icon'  => $icon_pack['path'] . 'etsy.png',
            ),
            'eway'                => array(
                'label' => __('Eway', 'cuar'),
                'icon'  => $icon_pack['path'] . 'eway.png',
            ),
            'google-wallet'       => array(
                'label' => __('Google Wallet', 'cuar'),
                'icon'  => $icon_pack['path'] . 'google-wallet.png',
            ),
            'jcb'                 => array(
                'label' => __('JCB', 'cuar'),
                'icon'  => $icon_pack['path'] . 'jcb.png',
            ),
            'maestro'             => array(
                'label' => __('Maestro', 'cuar'),
                'icon'  => $icon_pack['path'] . 'maestro.png',
            ),
            'mastercard'          => array(
                'label' => __('Mastercard', 'cuar'),
                'icon'  => $icon_pack['path'] . 'mastercard.png',
            ),
            'moneybookers'        => array(
                'label' => __('Moneybookers', 'cuar'),
                'icon'  => $icon_pack['path'] . 'moneybookers.png',
            ),
            'paypal'              => array(
                'label' => __('PayPal', 'cuar'),
                'icon'  => $icon_pack['path'] . 'paypal.png',
            ),
            'paypal-p'            => array(
                'label' => __('PayPal', 'cuar'),
                'icon'  => $icon_pack['path'] . 'paypal-p.png',
            ),
            'sage'                => array(
                'label' => __('Sage', 'cuar'),
                'icon'  => $icon_pack['path'] . 'sage.png',
            ),
            'shopify'             => array(
                'label' => __('Shopify', 'cuar'),
                'icon'  => $icon_pack['path'] . 'shopify.png',
            ),
            'skrill'              => array(
                'label' => __('Skrill', 'cuar'),
                'icon'  => $icon_pack['path'] . 'skrill.png',
            ),
            'skrill-moneybookers' => array(
                'label' => __('Skrill Moneybookers', 'cuar'),
                'icon'  => $icon_pack['path'] . 'skrill-moneybookers.png',
            ),
            'solo'                => array(
                'label' => __('Solo', 'cuar'),
                'icon'  => $icon_pack['path'] . 'solo.png',
            ),
            'switch'              => array(
                'label' => __('Switch', 'cuar'),
                'icon'  => $icon_pack['path'] . 'switch.png',
            ),
            'visa'                => array(
                'label' => __('Visa', 'cuar'),
                'icon'  => $icon_pack['path'] . 'visa.png',
            ),
            'visa-electron'       => array(
                'label' => __('Visa Electron', 'cuar'),
                'icon'  => $icon_pack['path'] . 'visa-electron.png',
            ),
            'western-union'       => array(
                'label' => __('Western Union', 'cuar'),
                'icon'  => $icon_pack['path'] . 'western-union.png',
            ),
            'worldpay'            => array(
                'label' => __('World Pay', 'cuar'),
                'icon'  => $icon_pack['path'] . 'worldpay.png',
            ),
        );
    }

    /**
     * @return array The default tax rates we can quickly select
     */
    public function get_enabled_payment_icons()
    {
        $icon_pack = $this->get_enabled_payment_icon_pack();
        $icons = $this->get_available_payment_icons($icon_pack);
        $enabled = $this->get_enabled_payment_icon_ids();

        /** @var CUAR_PaymentGateway $gateway */
        foreach ($icons as $id => $icon) {
            if ( !in_array($id, $enabled)) unset($icons[$id]);
        }

        return $icons;
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
            array(&$this, 'print_general_section_info'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG
        );

        add_settings_field(
            self::$OPTION_ENABLED_PAYMENT_ICON_PACK,
            __("Payment icon pack", 'cuar'),
            array(&$cuar_settings, 'print_select_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_payments_general',
            array(
                'option_id' => self::$OPTION_ENABLED_PAYMENT_ICON_PACK,
                'options'   => $this->get_selectable_payment_icon_pack_options(),
                'multiple'  => false,
                'after'     => '', //'<p class="description">' . __('Show the following payment icon logos next to the payment button', 'cuar') . '</p>',
            )
        );

        $icon_packs = $this->get_available_payment_icon_packs();
        foreach ($icon_packs as $pack_id => $pack) {
            add_settings_field(
                self::$OPTION_ENABLED_PAYMENT_ICONS . $pack_id,
                '',
                array(&$cuar_settings, 'print_select_field'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG,
                'cuar_payments_general',
                array(
                    'option_id' => self::$OPTION_ENABLED_PAYMENT_ICONS . $pack_id,
                    'options'   => $this->get_selectable_payment_icon_options($pack),
                    'multiple'  => true,
                    'class'  => 'cuar-payment-icon-setting cuar-payment-icon-setting-' . $pack_id,
                    'after'     => $this->get_selectable_payment_icons_description($pack)
                )
            );
        }

        add_settings_field(
            self::$OPTION_DEFAULT_GATEWAY,
            __("Default gateway", 'cuar'),
            array(&$cuar_settings, 'print_select_field'),
            CUAR_Settings::$OPTIONS_PAGE_SLUG,
            'cuar_payments_general',
            array(
                'option_id' => self::$OPTION_DEFAULT_GATEWAY,
                'options'   => $this->get_available_gateways_for_select(),
                'multiple'  => false,
                'after'     => '<p class="description">' . __('The gateway which will be selected by default on the checkout page', 'cuar') . '</p>',
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
        $icon_packs = $this->get_available_payment_icon_packs();
        $cuar_settings->validate_select($input, $validated, self::$OPTION_ENABLED_PAYMENT_ICON_PACK, $icon_packs, false);

        foreach ($icon_packs as $id => $pack) {
            $cuar_settings->validate_select($input, $validated,
                self::$OPTION_ENABLED_PAYMENT_ICONS . $id,
                $this->get_selectable_payment_icon_options($pack),
                true);
        }

        $cuar_settings->validate_select($input, $validated, self::$OPTION_DEFAULT_GATEWAY, $this->get_available_gateways_for_select(), false);

        $gateways = $this->get_available_gateways();
        /** @var CUAR_PaymentGateway $gateway */
        foreach ($gateways as $gateway) {
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

        /** @noinspection PhpUnusedLocalVariableInspection */
        $settings = $this->plugin->get_settings();

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            'settings-gateways-table.template.php',
            'templates'));
    }

    /**
     * Print some info about the section
     */
    public function print_general_section_info()
    {
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            'settings-payment-icons-scripts.template.php',
            'templates'));
    }

    /**
     * @param array $icon_pack
     *
     * @return array
     */
    private function get_selectable_payment_icon_pack_options()
    {
        $packs = $this->get_available_payment_icon_packs();
        foreach ($packs as $id => $pack) {
            $packs[$id] = $pack['label'];
        }

        return $packs;
    }

    /**
     * @param array $icon_pack
     *
     * @return array
     */
    private function get_selectable_payment_icon_options($icon_pack)
    {
        $icons = $this->get_available_payment_icons($icon_pack);
        foreach ($icons as $id => $icon) {
            $icons[$id] = $icon['label'];
        }

        return $icons;
    }

    /**
     * @param array $icon_pack
     *
     * @return string
     */
    private function get_selectable_payment_icons_description($icon_pack)
    {
        $out = '<p class="description">';
        $out .= __('Select the payment icons you want to show amongst all the available icons in this pack:', 'cuar') . '<br/><br/>';
        $icons = $this->get_available_payment_icons($icon_pack);
        foreach ($icons as $id => $icon) {
            $out .= sprintf('<img width="64" src="%1$s" title="%2$s" class="cuar-payment-icon" data-pack="%3$s" data-id="%4$s"/> ',
                $icon['icon'],
                $icon['label'],
                $icon_pack['id'],
                $id
            );
        }
        $out .= '</p>';

        return $out;
    }
}
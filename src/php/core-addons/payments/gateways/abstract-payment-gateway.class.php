<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

abstract class CUAR_AbstractPaymentGateway implements CUAR_PaymentGateway
{
    public static $OPTION_ENABLED = 'enabled';

    /** @var CUAR_Plugin */
    protected $plugin;

    /**
     * CUAR_AbstractPaymentGateway constructor.
     *
     * @param CUAR_Plugin $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    //-- General functions ------------------------------------------------------------------------------------------------------------------------------------/

    public function is_enabled()
    {
        $value = $this->get_option(self::$OPTION_ENABLED);

        return isset($value) && $value == 1 ? true : false;
    }

    //-- UI functions -----------------------------------------------------------------------------------------------------------------------------------------/

    public function get_icon()
    {
        return array(
            'icon' => '',
            'link' => '',
        );
    }

    /**
     * @return bool
     */
    public function has_form()
    {
        return false;
    }

    /**
     *
     */
    function print_form()
    {
        if ($this->has_form())
        {
            $form_template = $this->plugin->get_template_file_path(
                $this->get_template_files_root(),
                'gateway-checkout-form-' . $this->get_id() . '.template.php',
                'templates'
            );
            if ( !empty($form_template)) include($form_template);
        }
    }

    //-- Settings functions -----------------------------------------------------------------------------------------------------------------------------------/

    public function print_settings()
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $gateway = $this;

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            'gateway-settings-common.template.php',
            'templates'
        ));

        $settings_template = $this->plugin->get_template_file_path(
            $this->get_template_files_root(),
            'gateway-settings-' . $this->get_id() . '.template.php',
            'templates'
        );
        if ( !empty($settings_template)) include($settings_template);
    }

    public function validate_options($validated, $cuar_settings, $input)
    {
        $cuar_settings->validate_boolean($input, $validated, $this->get_option_id(self::$OPTION_ENABLED));

        return $validated;
    }

    public function get_option_id($option_id)
    {
        return 'cuar_gateway_' . $this->get_id() . '_' . $option_id;
    }

    public function get_option($option_id)
    {
        return $this->plugin->get_option($this->get_option_id($option_id));
    }

    protected function get_template_files_root()
    {
        return CUAR_INCLUDES_DIR . '/core-addons/payments';
    }
}
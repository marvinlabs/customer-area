<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

abstract class CUAR_AbstractPaymentGateway implements CUAR_PaymentGateway
{
    public static $OPTION_ENABLED = 'enabled';
    public static $OPTION_LOG_ENABLED = 'log_enabled';

    /** @var CUAR_Plugin */
    protected $plugin;

    /** @var CUAR_FileLogger */
    protected $logger;

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

    public function is_log_enabled()
    {
        $value = $this->get_option(self::$OPTION_LOG_ENABLED);

        return isset($value) && $value == 1 ? true : false;
    }

    public function is_enabled()
    {
        $value = $this->get_option(self::$OPTION_ENABLED);

        return isset($value) && $value == 1 ? true : false;
    }

    public function redirect_to_success_page($payment_id, $message = '')
    {
        if ( !empty($message)) {
            $this->set_result_message($message);
        }

        $url = cuar_get_payment_success_url($payment_id);
        wp_redirect($url);
        exit;
    }

    public function redirect_to_failure_page($payment_id, $message = '')
    {
        if ( !empty($message)) {
            $this->set_result_message($message);
        }

        $url = cuar_get_payment_failure_url($payment_id);
        wp_redirect($url);
        exit;
    }

    public function get_listener_id()
    {
        return $this->get_id();
    }

    public function get_listener_url()
    {
        return add_query_arg('cuar-payment-listener', $this->get_listener_id(), home_url('index.php'));
    }

    public function process_callback()
    {
    }

    //-- UI functions -----------------------------------------------------------------------------------------------------------------------------------------/

    public function has_form()
    {
        return false;
    }

    public function print_form()
    {
        if ($this->has_form()) {
            $form_template = $this->plugin->get_template_file_path(
                $this->get_template_files_root(),
                'gateway-checkout-form-' . $this->get_id() . '.template.php',
                'templates'
            );
            if ( !empty($form_template)) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $gateway = $this;
                include($form_template);
            }
        }
    }

    protected function set_result_message($message)
    {
        $_SESSION['cuar_gateway_message'] = $message;
    }

    //-- Settings functions -----------------------------------------------------------------------------------------------------------------------------------/

    public function print_settings($settings)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $gateway = $this;

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            'settings-gateway-common.template.php',
            'templates'
        ));

        $settings_template = $this->plugin->get_template_file_path(
            $this->get_template_files_root(),
            'settings-gateway-' . $this->get_id() . '.template.php',
            'templates'
        );
        if ( !empty($settings_template)) include($settings_template);
    }

    public function validate_options($validated, $cuar_settings, $input)
    {
        $cuar_settings->validate_boolean($input, $validated, $this->get_option_id(self::$OPTION_ENABLED));
        $cuar_settings->validate_boolean($input, $validated, $this->get_option_id(self::$OPTION_LOG_ENABLED));

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

    /**
     * Logging method.
     *
     * @param string $message
     */
    protected function log($message)
    {
        if ( !$this->is_log_enabled()) return;

        if (empty($this->logger)) {
            $this->logger = new CUAR_FileLogger();
        }
        $this->logger->add('gateway-' . $this->get_id(), $message);
    }
}
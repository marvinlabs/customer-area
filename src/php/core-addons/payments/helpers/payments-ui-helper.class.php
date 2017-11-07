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

        add_action('template_redirect', array(&$this, 'process_payment'));
        add_action('template_redirect', array(&$this, 'process_payment_listener_call'));
    }

    /**
     * Show a button which leads to the page where the user can pay for the given object
     *
     * @param string $object_type
     * @param int    $object_id
     * @param double $amount
     * @param string $currency
     * @param array  $address
     */
    public function show_payment_button($object_type, $object_id, $amount, $currency, $address)
    {
        // Check if we already have a payment for that object, and if any, check the amount
        /** @var CUAR_PaymentsHelper $payments_helper */
        $payments_helper = cuar_addon('payments')->payments();
        $payments = $payments_helper->get_payments_for_object($object_type, $object_id, false);
        $paid_amount = 0;
        $template_suffix = is_admin() ? '-admin' : '-frontend';

        /** @var CUAR_Payment $p */
        foreach ($payments as $p)
        {
            if ($p->get_post()->post_status !== CUAR_PaymentStatus::$STATUS_COMPLETE) continue;

            $paid_amount += $p->get_amount();
        }
        $remaining_amount = $amount - $paid_amount;

        // If everything is paid, we do not show the payment button
        if ($remaining_amount > 0)
        {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $button_label = apply_filters('cuar/core/payments/templates/payment-button-label',
                sprintf(__('Pay %s', 'cuar'), CUAR_CurrencyHelper::formatAmount($remaining_amount, $currency, '')));

            /** @noinspection PhpUnusedLocalVariableInspection */
            $gateways = $this->pa_addon->settings()->get_enabled_gateways();
            if (empty($gateways)) return;

            /** @noinspection PhpUnusedLocalVariableInspection */
            $payment_icons = $this->pa_addon->settings()->get_enabled_payment_icons();

            $template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments',
                array(
                    'payment-button-' . $object_type . $template_suffix . '.template.php',
                    'payment-button-' . $object_type . '.template.php',
                    'payment-button' . $template_suffix . '.template.php',
                    'payment-button.template.php',
                ),
                'templates');

            include($template);
        }

        // Display existing payments
        if ( !empty($payments) && $paid_amount > 0)
        {
            $template = $this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments',
                array(
                    'payment-history-inline-' . $object_type . $template_suffix . '.template.php',
                    'payment-history-inline-' . $object_type . '.template.php',
                    'payment-history-inline' . $template_suffix . '.template.php',
                    'payment-history-inline.template.php',
                ),
                'templates');

            include($template);
        }
    }

    public function show_payment_history($object_type, $object_id)
    {
        // Check if we already have a payment for that object, and if any, check the amount
        /** @var CUAR_PaymentsHelper $payments_helper */
        $payments_helper = cuar_addon('payments')->payments();
        $payments = $payments_helper->get_payments_for_object($object_type, $object_id, false);
        $template_suffix = is_admin() ? '-admin' : '-frontend';

        $template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-history-inline-' . $object_type . $template_suffix . '.template.php',
                'payment-history-inline-' . $object_type . '.template.php',
                'payment-history-inline' . $template_suffix . '.template.php',
                'payment-history-inline.template.php',
            ),
            'templates');

        include($template);
    }

    //---------- PROCESSING FUNCTIONS -------------------------------------------------------------------------------------------------------------------------/

    public function process_payment()
    {
        // Check that we have the required flag in the _POST data
        if ( !isset($_POST['cuar_action']) || $_POST['cuar_action'] !== 'process_payment') return;

        // Get payment data as submitted by the checkout form
        $object_id = isset($_POST['cuar_object_id']) ? $_POST['cuar_object_id'] : 0;
        $object_type = isset($_POST['cuar_object_type']) ? $_POST['cuar_object_type'] : '';

        // Verify nonce
        $nonce_action = 'process_payment_' . md5($object_type . $object_id);
        $nonce_value = isset($_POST['cuar_process_payment_nonce']) ? $_POST['cuar_process_payment_nonce'] : '';
        if ( !wp_verify_nonce($nonce_value, $nonce_action))
        {
            wp_die('Trying to cheat!');
        }

        // Get more required payment data
        $amount = isset($_POST['cuar_amount']) ? $_POST['cuar_amount'] : 0;
        $currency = isset($_POST['cuar_currency']) ? $_POST['cuar_currency'] : '';
        $gateway_id = isset($_POST['cuar_selected_gateway']) ? $_POST['cuar_selected_gateway'] : '';
        $selected_gateway = $this->pa_addon->settings()->get_gateway($gateway_id);
        if (empty($object_type) || 0 == $object_id || empty($currency) || $selected_gateway == false)
        {
            wp_die(__('Some checkout information has not been provided, please go back and try again', 'cuar'));
        }

        // And some more data, optional this time
        $address = isset($_POST['cuar_address']) ? $_POST['cuar_address'] : array();
        $gateway_params = isset($_POST['cuar_gateway']) && isset($_POST['cuar_gateway'][$gateway_id]) ? $_POST['cuar_gateway'][$gateway_id] : array();

        // Data we compute directly
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $pto = get_post_type_object($object_type);
        if ($pto===null) {
            wp_die('Post type not found. Did you uninstall a plugin in the meantime?');
        }

        $title = sprintf(_x('%1$s: %2$s', 'payment title format (type: title)', 'cuar'),
            $pto->labels->singular_name,
            get_the_title($object_id));

        // Allow themes and plugins to hook before the gateway
        do_action('cuar/core/payments/process/before-gateway', $_POST);

        // Allow themes and plugins to modify payment data before processing
        $payment_data = apply_filters('cuar/core/payments/data-before-gateway', array(
            'title'      => $title,
            'amount'     => $amount,
            'currency'   => $currency,
            'user_id'    => $user_id,
            'address'    => $address,
            'extra_data' => array(),
        ), $object_type, $object_id, $_POST);

        // Create the payment with pending status
        $payment_id = $this->pa_addon->payments()->add(
            $object_type, $object_id,
            $payment_data['title'],
            $gateway_id,
            $payment_data['amount'], $payment_data['currency'],
            $payment_data['user_id'], $payment_data['address'],
            $payment_data['extra_data']);

        if ($payment_id === false)
        {
            $_SESSION['cuar_checkout_data'] = $_POST;
            $_SESSION['cuar_checkout_error'] = __('The payment could not be processed, please contact us.', 'cuar');
            wp_redirect(cuar_get_checkout_url());

            return;
        }

        // Send for processing to the gateway
        $payment = new CUAR_Payment($payment_id);
        $selected_gateway->process_payment($payment, $payment_data, $gateway_params);

        exit;
    }

    public function process_payment_listener_call()
    {
        if ( !(isset($_GET['cuar-payment-listener']) || !isset($_POST['cuar-payment-listener']))) return;

        $listener_id = isset($_GET['cuar-payment-listener'])
            ? $_GET['cuar-payment-listener']
            : (isset($_POST['cuar-payment-listener'])
                ? $_POST['cuar-payment-listener']
                : '');

        // Try to find the gateway responsible for handling this event
        $gateways = $this->pa_addon->settings()->get_enabled_gateways();

        /** @var CUAR_PaymentGateway $gateway */
        foreach ($gateways as $gateway_id => $gateway)
        {
            if ($gateway->get_listener_id() === $listener_id)
            {
                // Gateway found. Let it process the result
                $gateway->process_callback();
                die();
            }
        }
    }
}
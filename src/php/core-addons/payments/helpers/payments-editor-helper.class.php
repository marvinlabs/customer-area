<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsEditorHelper
{
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
    }

    public function print_object_summary($payment_id)
    {
        $payment = new CUAR_Payment($payment_id);
        $object = $payment->get_object();

        do_action('cuar/core/payments/show-object-details?type=' . $object['type'], $payment, $object['id']);
    }

    public function print_data_fields($payment_id)
    {
        $payment = new CUAR_Payment($payment_id);

        $template_suffix = is_admin() ? '-admin' : '-frontend';
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-editor-data' . $template_suffix . '.template.php',
                'payment-editor-data.template.php',
            ),
            'templates'));
    }
    
    public function print_gateway_fields($payment_id) 
    {
        $payment = new CUAR_Payment($payment_id);
        $gateways = $this->pa_addon->settings()->get_available_gateways();

        $template_suffix = is_admin() ? '-admin' : '-frontend';
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-editor-gateway' . $template_suffix . '.template.php',
                'payment-editor-gateway.template.php',
            ),
            'templates'));
    }

    /**
     * Print the payment items manager
     */
    public function print_notes_manager($payment_id)
    {
        $this->pa_addon->enqueue_scripts();

        $payment = new CUAR_Payment($payment_id);
        $notes = $payment->get_notes();

        $template_suffix = is_admin() ? '-admin' : '-frontend';
        $item_template = $this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-editor-note-list-item' . $template_suffix . '.template.php',
                'payment-editor-note-list-item.template.php',
            ),
            'templates');

        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-editor-note-list' . $template_suffix . '.template.php',
                'payment-editor-note-list.template.php',
            ),
            'templates'));
    }

    /**
     * Print the input fields corresponding to the payment billing address
     *
     * @param int $payment_id
     */
    public function print_address_fields($payment_id)
    {
        $this->plugin->enable_library('select2');
        $this->pa_addon->enqueue_scripts();

        $payment = new CUAR_Payment($payment_id);

        $template_suffix = is_admin() ? '-admin' : '-frontend';
        include($this->plugin->get_template_file_path(
            CUAR_INCLUDES_DIR . '/core-addons/payments',
            array(
                'payment-editor-payer' . $template_suffix . '.template.php',
                'payment-editor-payer.template.php',
            ),
            'templates'));
    }

    /**
     * Save the billing information
     *
     * @param int   $payment_id The ID of the payment
     * @param array $form_data  The form data (typically $_POST)
     */
    public function save_address_fields($payment_id, $form_data)
    {
        $address = isset($form_data['cuar_address']) ? $form_data['cuar_address'] : array();

        $payer = isset($form_data['payer']) ? $form_data['payer'] : array();
        $user_id = isset($payer['user_id']) ? $payer['user_id'] : 0;
        $user_ip = isset($payer['user_ip']) ? $payer['user_ip'] : '';

        $payment = new CUAR_Payment($payment_id, false);
        $payment->set_address($address);
        $payment->set_user_id($user_id);
        $payment->set_user_ip($user_ip);
    }

    /**
     * Save the payment gateway properties
     *
     * @param int   $payment_id The ID of the payment
     * @param array $form_data  The form data (typically $_POST)
     */
    public function save_gateway_fields($payment_id, $form_data)
    {
        $gw = isset($form_data['gateway']) ? $form_data['gateway'] : array();
        $gateway = isset($gw['gateway']) ? $gw['gateway'] : 0;

        $payment = new CUAR_Payment($payment_id, true);
        $payment->set_gateway($gateway);

        do_action('cuar/core/payments/payment-editor-save-gateway-meta?gateway=' . $gateway, $payment);
    }

    /**
     * Save the general payment properties
     *
     * @param int   $payment_id The ID of the payment
     * @param array $form_data  The form data (typically $_POST)
     */
    public function save_data_fields($payment_id, $form_data)
    {
        $data = isset($form_data['data']) ? $form_data['data'] : array();
        $currency = isset($data['currency']) ? $data['currency'] : 'EUR';
        $status = isset($data['status']) ? $data['status'] : '';
        $date = isset($data['date']) ? $data['date'] : '';
        $amount = isset($data['amount']) ? $data['amount'] : 0;

        $payment = new CUAR_Payment($payment_id, false);
        $payment->set_currency($currency);
        $payment->set_amount($amount);

        // Date
        $update_args = array(
            'ID' => $payment_id
        );

        if (!empty($date)) {
            $date .= ' 00:00:00';
            $update_args['post_date'] = $date;
            $update_args['post_date_gmt'] = $date;
        }

        if (count($update_args)>1) {
            wp_update_post($update_args);
        }

        // Status
        $user = get_userdata(get_current_user_id());
        $payment->update_status($status, $user->user_login);
    }
}
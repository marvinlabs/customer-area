<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsEditorHelper
{
    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_InvoicingAddOn */
    private $pa_addon;

    /**
     * Constructor
     */
    public function __construct($plugin, $pa_addon)
    {
        $this->plugin = $plugin;
        $this->pa_addon = $pa_addon;
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
        $this->pa_addon->enqueue_scripts();

        $payment = new CUAR_Payment($payment_id);
        $address = $payment->get_address();

        /** @var CUAR_AddressesAddOn $am */
        $am = $this->plugin->get_addon('address-manager');
        $am->print_address_editor($address,
            'cuar_address', '',
            array(), '',
            'metabox');
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

        $payment = new CUAR_Payment($payment_id, false);
        $payment->set_address($address);
    }

    /**
     * Save the general payment properties
     *
     * @param int   $payment_id The ID of the payment
     * @param array $form_data  The form data (typically $_POST)
     */
    public function save_settings_fields($payment_id, $form_data)
    {
        $settings = isset($form_data['settings']) ? $form_data['settings'] : array();
        $currency = isset($settings['currency']) ? $settings['currency'] : $this->pa_addon->settings()->get_default_currency();
        $status = isset($settings['status']) ? $settings['status'] : $this->pa_addon->settings()->get_default_status();
        $due_date = isset($settings['due_date']) ? $settings['due_date'] : '';
        $payment_mode = isset($settings['payment_mode']) ? $settings['payment_mode'] : '';

        $payment = new CUAR_Payment($payment_id, false);
        $payment->set_currency($currency);
        $payment->set_status($status);
        $payment->set_due_date($due_date);
        $payment->set_payment_mode($payment_mode);
    }
}
<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com) */

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/payment.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/payment-status.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-admin-interface.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-settings-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-ui-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-editor-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/payment-gateway.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/abstract-payment-gateway.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/bacs-payment-gateway.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/cheque-payment-gateway.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/test-payment-gateway.class.php');

if ( !class_exists('CUAR_PaymentsAddOn')) :

    /**
     * Add-on to allow users to send messages to each other
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_PaymentsAddOn extends CUAR_AddOn
    {
        /** @var CUAR_PaymentsSettingsHelper */
        private $settings;

        /** @var CUAR_PaymentsAdminInterface */
        private $admin_interface;

        /** @var CUAR_PaymentsHelper */
        private $payments;

        /** @var CUAR_PaymentsUiHelper */
        private $payments_ui;

        /** @var CUAR_PaymentsEditorHelper */
        private $editor;

        /**
         * CUAR_PaymentsAddOn constructor.
         */
        public function __construct()
        {
            parent::__construct('payments');
        }

        /** @override */
        public function get_addon_name()
        {
            return __('Payments', 'cuar');
        }

        /**
         * @param CUAR_Plugin $plugin
         */
        public function run_addon($plugin)
        {
            if ( !$this->is_enabled()) return;

            $this->editor = new CUAR_PaymentsEditorHelper($plugin, $this);
            $this->settings = new CUAR_PaymentsSettingsHelper($plugin, $this);
            $this->payments = new CUAR_PaymentsHelper($plugin, $this);
            $this->payments_ui = new CUAR_PaymentsUiHelper($plugin, $this);

            // Init the admin interface if needed
            if (is_admin()) {
                $this->admin_interface = new CUAR_PaymentsAdminInterface($plugin, $this);
            }

            add_action('init', array(&$this, 'register_custom_types'));
            add_filter('cuar/core/post-types/other', array(&$this, 'add_managed_post_type'), 10, 1);

            // For AJAX
            add_filter('cuar/core/js-messages?zone=admin', array(&$this, 'add_js_messages'));
            add_action('wp_ajax_cuar_delete_payment_note', array(&$this, 'ajax_delete_payment_note'));
            add_action('wp_ajax_cuar_add_payment_note', array(&$this, 'ajax_add_payment_note'));
        }

        /**
         * @return CUAR_PaymentsEditorHelper
         */
        public function editor()
        {
            return $this->editor;
        }

        /**
         * @return CUAR_PaymentsSettingsHelper
         */
        public function settings()
        {
            return $this->settings;
        }

        /**
         * @return CUAR_PaymentsHelper
         */
        public function payments()
        {
            return $this->payments;
        }

        /**
         * @return CUAR_PaymentsUiHelper
         */
        public function ui()
        {
            return $this->payments_ui;
        }

        /**
         * Get the types which can be paid
         *
         * @return array
         */
        public function get_payable_types()
        {
            return apply_filters('cuar/core/payments/payable-types', array());
        }

        public function is_enabled()
        {
            $payable_types = $this->get_payable_types();

            return !empty($payable_types);
        }

        /*------- SCRIPTS & AJAX -----------------------------------------------------------------------------------------*/

        public function add_managed_post_type($post_types)
        {
            $post_types[CUAR_Payment::$POST_TYPE] = array(
                "label-singular"     => __('Payment', 'cuar'),
                "label-plural"       => __('Payments', 'cuar'),
                "content-page-addon" => null,
                "type"               => "other",
            );

            return $post_types;
        }

        /**
         * Enqueue the invoicing scripts
         */
        public function enqueue_scripts()
        {
            wp_enqueue_script(is_admin() ? 'cuar.admin' : 'cuar.frontend');
            $this->plugin->enable_library('jquery.autogrow');
        }

        /**
         * Add our JS messages
         *
         * @param array $messages
         *
         * @return array
         */
        public function add_js_messages($messages)
        {
            $messages['confirmDeletePaymentNote'] = __('Are you sure that you want to delete this note?', 'cuar');

            return $messages;
        }

        /**
         * Delete a task from our list via AJAX
         */
        public function ajax_delete_payment_note()
        {
            $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : 0;
            if ($payment_id <= 0) {
                wp_send_json_error(__('Payment id is not specified', 'cuar'));
            }

            // Check nonce
            $nonce_action = 'cuar_delete_payment_note';
            $nonce_name = 'cuar_delete_payment_note_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action)) {
                wp_send_json_error(__('Trying to cheat?', 'cuar'));
            }

            // Check permissions
            if ( !current_user_can('cuar_pay_edit')) {
                wp_send_json_error(__('You are not allowed to manage payment notes', 'cuar'));
            }

            $note_id = isset($_POST['note_id']) ? $_POST['note_id'] : 0;
            if ($note_id <= 0) {
                wp_send_json_error(__('Note id is not specified', 'cuar'));
            }

            $payment = new CUAR_Payment($payment_id);
            $payment->delete_note($note_id);

            wp_send_json_success(array('deleted' => true));
        }

        /**
         * Add a task via AJAX
         */
        public function ajax_add_payment_note()
        {
            $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : 0;
            if ($payment_id <= 0) {
                wp_send_json_error(__('Payment id is not specified', 'cuar'));
            }

            // Check nonce
            $nonce_action = 'cuar_add_payment_note';
            $nonce_name = 'cuar_add_payment_note_nonce';
            if ( !isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action)) {
                wp_send_json_error(__('Trying to cheat?', 'cuar'));
            }

            // Check permissions
            if ( !current_user_can('cuar_pay_edit')) {
                wp_send_json_error(__('You are not allowed to manage payment notes', 'cuar'));
            }


            $message = isset($_POST['message']) ? $_POST['message'] : '';
            if (empty($message)) {
                wp_send_json_error(__('You must provide a message', 'cuar'));
            }

            $author = get_userdata(get_current_user_id());

            $payment = new CUAR_Payment($payment_id);
            $note = $payment->add_note($author->user_login, $message);

            wp_send_json_success($note);
        }

        /*------- INITIALISATION -----------------------------------------------------------------------------------------*/

        /**
         * Register the custom post type for files and the associated taxonomies
         */
        public function register_custom_types()
        {
            CUAR_PaymentStatus::register_statuses();
            CUAR_Payment::register_post_type();
        }

        /**
         * Set the default values for the options
         *
         * @param array $defaults
         *
         * @return array
         */
        public function set_default_options($defaults)
        {
            $defaults = parent::set_default_options($defaults);
            $defaults = CUAR_PaymentsSettingsHelper::set_default_options($defaults);

            return $defaults;
        }
    }

    // Make sure the addon is loaded
    new CUAR_PaymentsAddOn();

endif; // if (!class_exists('CUAR_PaymentsAddOn')) 

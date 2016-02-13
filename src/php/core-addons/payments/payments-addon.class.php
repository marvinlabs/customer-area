<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com) */

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/payment.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/payment-status.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-admin-interface.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payments-settings-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/payment-gateway.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/abstract-payment-gateway.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/payments/gateways/bacs-payment-gateway.class.php');
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

        /**
         * CUAR_PaymentsAddOn constructor.
         */
        public function __construct()
        {
            parent::__construct('payments', '6.4.0');
        }

        /** @override */
        public function get_addon_name()
        {
            return __('Payments', 'cuarme');
        }

        /**
         * @param CUAR_Plugin $plugin
         */
        public function run_addon($plugin)
        {
            $this->settings = new CUAR_PaymentsSettingsHelper($plugin, $this);
//            $this->logger = new CUAR_PaymentsLogger($plugin, $this);
//            $this->editor = new CUAR_PaymentEditorHelper($plugin, $this);

            // Init the admin interface if needed
            if (is_admin())
            {
                $this->admin_interface = new CUAR_PaymentsAdminInterface($plugin, $this);
            }

            add_action('init', array(&$this, 'register_custom_types'));
        }

        /**
         * @return CUAR_PaymentsSettingsHelper
         */
        public function settings()
        {
            return $this->settings;
        }

        /*------- INITIALISATION -----------------------------------------------------------------------------------------*/

        /**
         * Register the custom post type for files and the associated taxonomies
         */
        public function register_custom_types()
        {
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

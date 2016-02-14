<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com) */

require_once(CUAR_INCLUDES_DIR . '/core-classes/settings.class.php');

if ( !class_exists('CUAR_PaymentsAdminInterface')) :

    /**
     * Administation area for payments
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_PaymentsAdminInterface
    {
        /** @var CUAR_Plugin */
        private $plugin;

        /** @var CUAR_PaymentsAddOn */
        private $pa_addon;

        public function __construct($plugin, $pa_addon)
        {
            $this->plugin = $plugin;
            $this->pa_addon = $pa_addon;

            // Menu
            add_action('cuar/core/admin/print-admin-page?page=payments', array(&$this, 'print_payments_page'), 99);
            add_action('cuar/core/admin/submenu-items?group=tools', array(&$this, 'add_menu_items'), 90);
            add_action("load-post-new.php", array(&$this, 'block_default_admin_pages'));
            add_action("load-edit.php", array(&$this, 'block_default_admin_pages'));
        }

        /*------- ADMIN PAGE -----------------------------------------------------------------------------------------*/

        private static $PAYMENT_PAGE_SLUG = "wpca-payments";

        /**
         * Protect the default edition and listing pages
         */
        public function block_default_admin_pages()
        {
            if (isset($_GET["post_type"]) && $_GET["post_type"] == "cuar_payment")
            {
                wp_redirect(admin_url("admin.php?page=" . self::$PAYMENT_PAGE_SLUG));
            }
        }

        /**
         * Add the menu item
         */
        public function add_menu_items($submenus)
        {
            $submenus[] = array(
                'page_title' => __('WP Customer Area - Payments', 'cuar'),
                'title'      => __('Payments', 'cuar'),
                'slug'       => self::$PAYMENT_PAGE_SLUG,
                'href'       => 'admin.php?page=' . self::$PAYMENT_PAGE_SLUG,
                'capability' => 'manage_options'
            );

            return $submenus;
        }

        /**
         * Display the main logs page
         */
        public function print_payments_page()
        {
            require_once(CUAR_INCLUDES_DIR . '/core-addons/payments/helpers/payment-table.class.php');
            $payments_table = new CUAR_PaymentTable($this->plugin);
            $payments_table->initialize();

            /** @noinspection PhpIncludeInspection */
            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/payments',
                'payments-page.template.php',
                'templates'));
        }


    }

endif; // if (!class_exists('CUAR_PaymentsAdminInterface'))
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

            add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'));

            // Payments page
            add_action('add_meta_boxes', array(&$this, 'register_edit_page_meta_boxes'), 120);
        }

        /*------- PERMISSIONS --------------------------------------------------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            $capability_groups['cuar_general']['groups']['payments'] = array(
                'group_name'   => __('Payments', 'cuar'),
                'capabilities' => array(
                    'cuar_pay_list_all' => __('List all payments', 'cuar'),
                    'cuar_pay_edit'     => __('Create/Edit payments', 'cuar'),
                    'cuar_pay_delete'   => __('Delete payments', 'cuar'),
                    'cuar_pay_read'     => __('Access payments', 'cuar'),
                )
            );

            return $capability_groups;
        }

        /*------- ADMIN PAGE ---------------------------------------------------------------------------------------------------------------------------------*/

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

        /*------- EDIT PAGE ----------------------------------------------------------------------------------------------------------------------------------*/

        /**
         * Register some additional boxes on the page to edit the payments
         *
         * @param string $post_type
         */
        public function register_edit_page_meta_boxes($post_type)
        {
            if ($post_type != CUAR_Payment::$POST_TYPE) return;

            remove_meta_box( 'submitdiv', $post_type, 'side' );

            add_meta_box(
                'cuar_payment_data_metabox',
                __('Payment data', 'cuar'),
                array(&$this, 'print_payment_data_metabox'),
                CUAR_Payment::$POST_TYPE,
                'side', 'high');

            add_meta_box(
                'cuar_payment_gateway_metabox',
                __('Gateway', 'cuar'),
                array(&$this, 'print_gateway_metabox'),
                CUAR_Payment::$POST_TYPE,
                'side', 'high');

            add_meta_box(
                'cuar_payment_object_metabox',
                __('Paid object', 'cuar'),
                array(&$this, 'print_payment_object_metabox'),
                CUAR_Payment::$POST_TYPE,
                'normal', 'high');

            add_meta_box(
                'cuar_payment_notes_metabox',
                __('Notes', 'cuar'),
                array(&$this, 'print_notes_metabox'),
                CUAR_Payment::$POST_TYPE,
                'normal', 'low');
        }

        public function print_payment_data_metabox()
        {
            echo 'data';
        }

        public function print_gateway_metabox()
        {
            echo 'gateway';
        }

        public function print_payment_object_metabox()
        {
            echo 'object';
        }

        public function print_notes_metabox()
        {
            echo 'notes';
        }

    }

endif; // if (!class_exists('CUAR_PaymentsAdminInterface'))
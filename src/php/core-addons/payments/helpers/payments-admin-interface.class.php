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

            add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'));

            // Payments page
            add_action('add_meta_boxes', array(&$this, 'register_edit_page_meta_boxes'), 120);
            add_action("save_post_" . CUAR_Payment::$POST_TYPE, array(&$this, 'on_save_payment'));

            // New payment button + history on post type edit pages
            add_action('add_meta_boxes', array(&$this, 'register_new_payment_meta_box'), 120);
            add_action('admin_init', array(&$this, 'handle_create_payment_action'));
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
            if (isset($_GET["post_type"]) && $_GET["post_type"] == CUAR_Payment::$POST_TYPE)
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

        /*------- EDIT PAGE FOR PAYABLE TYPES ----------------------------------------------------------------------------------------------------------------*/

        public function register_new_payment_meta_box($post_type)
        {
            $payable_types = $this->pa_addon->get_payable_types();

            if ( !in_array($post_type, $payable_types)) return;

            add_meta_box(
                'cuar_payment_history_metabox',
                __('Payment history', 'cuar'),
                array(&$this, 'print_payment_history_metabox'),
                $post_type,
                'normal', 'low');
        }

        public function print_payment_history_metabox()
        {
            global $post;
            $this->pa_addon->ui()->show_payment_history($post->post_type, $post->ID);
        }

        public function handle_create_payment_action()
        {
            if ( !isset($_GET['cuar_action']) || $_GET['cuar_action'] != 'create-payment') return;

            $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
            $action = 'create-payment';
            if ( !wp_verify_nonce($nonce, $action))
            {
                wp_die(__("Trying to cheat?", 'cuar'));
            }

            $object_type = isset($_GET['object_type']) ? $_GET['object_type'] : '';
            $object_id = isset($_GET['object_id']) ? $_GET['object_id'] : 0;
            if (empty($object_type) || 0 == $object_id)
            {
                wp_die(__("Invalid arguments. Cannot create payment.", 'cuar'));
            }

            $pto = get_post_type_object($object_type);
            $title = sprintf(__('%1$s | %2$s', 'cuar'),
                $pto->labels->singular_name,
                get_the_title($object_id));

            $payment_data = apply_filters('cuar/core/payments/data-before-manual-add', array(
                'title'      => $title,
                'amount'     => 0,
                'currency'   => 'EUR',
                'gateway'    => '',
                'user_id'    => get_current_user_id(),
                'address'    => CUAR_AddressHelper::sanitize_address(array()),
                'extra_data' => array(),
            ), $object_type, $object_id);

            $payment_id = $this->pa_addon->payments()->add(
                $object_type, $object_id,
                $payment_data['title'],
                $payment_data['gateway'],
                $payment_data['amount'], $payment_data['currency'],
                $payment_data['user_id'], $payment_data['address'],
                $payment_data['extra_data']);

            wp_redirect(admin_url('post.php?action=edit&post_type=' . CUAR_Payment::$POST_TYPE . '&post=' . $payment_id));
        }

        /*------- EDIT PAGE ----------------------------------------------------------------------------------------------------------------------------------*/

        /**
         * Callback when the post is saved
         *
         * @param int $post_id
         */
        public function on_save_payment($post_id)
        {
            remove_action("save_post_" . CUAR_Payment::$POST_TYPE, array(&$this, 'on_save_payment'));

            $this->pa_addon->editor()->save_address_fields($post_id, $_POST);
            $this->pa_addon->editor()->save_gateway_fields($post_id, $_POST);
            $this->pa_addon->editor()->save_data_fields($post_id, $_POST);

            add_action("save_post_" . CUAR_Payment::$POST_TYPE, array(&$this, 'on_save_payment'));
        }

        /**
         * Register some additional boxes on the page to edit the payments
         *
         * @param string $post_type
         */
        public function register_edit_page_meta_boxes($post_type)
        {
            if ($post_type != CUAR_Payment::$POST_TYPE) return;

            remove_meta_box('submitdiv', $post_type, 'side');

            add_meta_box(
                'cuar_payment_object_metabox',
                __('Paid object', 'cuar'),
                array(&$this, 'print_object_metabox'),
                CUAR_Payment::$POST_TYPE,
                'side', 'high');

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
                'cuar_payment_author_metabox',
                __('Payer', 'cuar'),
                array(&$this, 'print_author_metabox'),
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
            global $post;
            $this->pa_addon->editor()->print_data_fields($post->ID);
        }

        public function print_gateway_metabox()
        {
            global $post;
            $this->pa_addon->editor()->print_gateway_fields($post->ID);
        }

        public function print_object_metabox()
        {
            global $post;
            $this->pa_addon->editor()->print_object_summary($post->ID);
        }

        public function print_notes_metabox()
        {
            global $post;
            $this->pa_addon->editor()->print_notes_manager($post->ID);
        }

        public function print_author_metabox()
        {
            global $post;
            $this->pa_addon->editor()->print_address_fields($post->ID);
        }

    }

endif; // if (!class_exists('CUAR_PaymentsAdminInterface'))
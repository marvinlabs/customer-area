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

        }
    }

endif; // if (!class_exists('CUAR_PaymentsAdminInterface'))
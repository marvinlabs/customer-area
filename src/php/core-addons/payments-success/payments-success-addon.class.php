<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-page.class.php');

if ( !class_exists('CUAR_PaymentsSuccessAddOn')) :

    /**
     * Add-on to show the success message for payments
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_PaymentsSuccessAddOn extends CUAR_AbstractPageAddOn
    {

        public function __construct()
        {
            parent::__construct('payments-success', '6.4.0');

            $this->set_page_parameters(220, array(
                    'slug'         => 'payments-success',
                    'parent_slug'  => 'payments-home',
                    'hide_in_menu' => true,
                )
            );

            $this->set_page_shortcode('customer-area-payment-success');
        }

        public function get_label()
        {
            return __('Payments - Success', 'cuar');
        }

        public function get_title()
        {
            return __('Payment accepted', 'cuar');
        }

        public function get_hint()
        {
            return __('This page show a user a message when payment has been validated', 'cuar');
        }

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);
        }

        public function get_page_addon_path()
        {
            return CUAR_INCLUDES_DIR . '/core-addons/payments-success';
        }
    }

    // Make sure the addon is loaded
    new CUAR_PaymentsSuccessAddOn();

endif; // if (!class_exists('CUAR_PaymentsSuccessAddOn')) :
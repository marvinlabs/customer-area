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

/**
 * Show a button which leads to the page where the user can pay for the given object
 *
 * @param string $object_type
 * @param int    $object_id
 * @param double $amount
 * @param string $currency
 * @param string $label The label to show on the button
 */
function cuar_the_payment_button($object_type, $object_id, $amount, $currency, $label, $address)
{
    /** @var CUAR_PaymentsAddOn $pa_addon */
    $pa_addon = cuar_addon('payments');

    $pa_addon->ui()->show_payment_button($object_type, $object_id, $amount, $currency, $label, $address);
}

/**
 * Get the URL to the checkout page
 *
 * @return string
 */
function cuar_get_checkout_url()
{
    /** @var CUAR_CustomerPagesAddOn $pa_addon */
    $pa_addon = cuar_addon('customer-pages');

    return $pa_addon->get_page_url('payments-checkout');
}

/**
 * Get the URL to the success page
 *
 * @return string
 */
function cuar_get_payment_success_url()
{
    /** @var CUAR_CustomerPagesAddOn $pa_addon */
    $pa_addon = cuar_addon('customer-pages');

    return $pa_addon->get_page_url('payments-success');
}

/**
 * Get the URL to the failure page
 *
 * @return string
 */
function cuar_get_payment_failure_url()
{
    /** @var CUAR_CustomerPagesAddOn $pa_addon */
    $pa_addon = cuar_addon('customer-pages');

    return $pa_addon->get_page_url('payments-failure');
}

/**
 * Get the URL to the checkout page
 *
 * @return string
 */
function cuar_get_gateway_message()
{
    if (isset($_SESSION['cuar_gateway_message']))
    {
        $message = $_SESSION['cuar_gateway_message'];
        unset($_SESSION['cuar_gateway_message']);

        return $message;
    }

    return '';
}
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
 * Created by PhpStorm.
 * User: vprat
 * Date: 29/11/2014
 * Time: 08:57
 */
class CUAR_LicenseValidationResult
{

    /**
     * Build a successful result
     *
     * @param CUAR_LicenseStore $store The store where plugins are bought
     * @param string $message The message to show to the user
     * @param boolean $is_new_store_license The license comes from the legacy marvinlabs store if true
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function success($store, $message, $is_new_store_license, $expires)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = true;
        $res->message = self::maybe_append_legacy_store_warning($store, $message, $is_new_store_license);
        $res->expires = $expires;
        $res->is_new_store_license = $is_new_store_license;
        return $res;
    }

    /**
     * Build a failure result
     *
     * @param CUAR_LicenseStore $store The store where plugins are bought
     * @param string $message The message to show to the user
     * @param boolean $is_new_store_license The license comes from the legacy marvinlabs store if true
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function failure($store, $message, $is_new_store_license = true, $expires = null)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = false;
        $res->message = self::maybe_append_legacy_store_warning($store, $message, $is_new_store_license);
        $res->expires = $expires;
        $res->is_new_store_license = $is_new_store_license;
        return $res;
    }

    /**
     * Append a warning message when a license is detected to be hosted on the legacy marvinlabs shop
     *
     * @param CUAR_LicenseStore $store The store where plugins are bought
     * @param string $message The original message
     * @param boolean $is_new_store_license true if the license is hosted on the new wpca shop
     * @return string The updated message
     */
    protected static function maybe_append_legacy_store_warning($store, $message, $is_new_store_license) {
        if ($is_new_store_license) return $message;

        return $message
                . '<br/>'
                . '<em class="cuar-old-shop-warning">'
                . sprintf(__('You have bought your license on the previous shop. We have a <a href="%1$s">brand new website</a>, please contact us so that we can help you migrate your license to the new shop.', 'cuar'),
                        $store->get_store_url() )
                . '</em>';
    }

    /**
     * @var bool If the validation was successful
     */
    public $success = false;

    /**
     * @var string The message to show to the end-user
     */
    public $message = '';

    /**
     * @var string the expiry date for the license
     */
    public $expires = null;

    /**
     * @var boolean the raw JSON response as received from the update server
     */
    public $is_new_store_license = true;

} 
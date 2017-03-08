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
     * @param string $message The message to show to the user
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function success($message, $expires)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = true;
        $res->message = $message;
        $res->expires = $expires;
        return $res;
    }

    /**
     * Build a failure result
     *
     * @param string $message The message to show to the user
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function failure($message, $expires = null, $serverResponse = null)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = false;
        $res->message = $message;
        $res->expires = $expires;
        $res->serverResponse = $serverResponse;
        return $res;
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

} 
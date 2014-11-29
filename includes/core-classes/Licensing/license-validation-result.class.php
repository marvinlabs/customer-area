<?php

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
     * @param string $api_response The raw response as received from the licensing server
     * @param string $message The message to show to the user
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function success($api_response, $message, $expires)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = true;
        $res->message = $message;
        $res->expires = $expires;
        $res->api_response = json_encode($api_response, JSON_PRETTY_PRINT);
        return $res;
    }

    /**
     * Build a successful result
     * @param string $api_response The raw response as received from the licensing server
     * @param string $message The message to show to the user
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function failure($api_response, $message, $expires = null)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = false;
        $res->message = $message;
        $res->expires = $expires;
        $res->api_response = json_encode($api_response, JSON_PRETTY_PRINT);
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

    /**
     * @var string the raw JSON response as received from the update server
     */
    public $api_response = null;

} 
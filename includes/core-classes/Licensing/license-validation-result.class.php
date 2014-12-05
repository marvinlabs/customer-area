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
     *
     * @param string $message The message to show to the user
     * @param boolean $is_new_store_license The license comes from the legacy marvinlabs store if true
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function success($message, $is_new_store_license, $expires)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = true;
        $res->message = self::maybe_append_legacy_store_warning($message, $is_new_store_license);
        $res->expires = $expires;
        $res->is_new_store_license = $is_new_store_license;
        return $res;
    }

    /**
     * Build a failure result
     *
     * @param string $message The message to show to the user
     * @param boolean $is_new_store_license The license comes from the legacy marvinlabs store if true
     * @param DateTime $expires The expiry date
     * @return \CUAR_LicenseValidationResult
     */
    public static function failure($message, $is_new_store_license = true, $expires = null)
    {
        $res = new CUAR_LicenseValidationResult();
        $res->success = false;
        $res->message = self::maybe_append_legacy_store_warning($message, $is_new_store_license);
        $res->expires = $expires;
        $res->is_new_store_license = $is_new_store_license;
        return $res;
    }

    /**
     * Append a warning message when a license is detected to be hosted on the legacy marvinlabs shop
     * @param string $message The original message
     * @param boolean $is_new_store_license true if the license is hosted on the new wpca shop
     * @return string The updated message
     */
    protected static function maybe_append_legacy_store_warning($message, $is_new_store_license) {
        if ($is_new_store_license) return $message;

        return $message
                . '<br/>'
                . '<em class="cuar-old-shop-warning">'
                . sprintf(__('You have bought your license on the historical MarvinLabs shop. We have a <a href="%1$s">brand new website</a>, please contact us so that we can help you migrate your license to the new shop.', 'cuar'),
                        'http://wp-customerarea.com' )
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
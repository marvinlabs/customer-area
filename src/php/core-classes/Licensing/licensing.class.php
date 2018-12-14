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
 * The class that will handle licensing checks with the WPCA store
 */
class CUAR_Licensing
{

    /** @var CUAR_LicenseStore */
    private $store;

    /**
     * Constructor
     *
     * @param $store
     */
    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * @return CUAR_LicenseStore
     */
    public function get_store()
    {
        return $this->store;
    }

    /**
     * Get the url to renew a license
     *
     * @param string $license_key The license key to renew
     *
     * @internal param int $item_id The item ID linked to the license
     * @return string The URL for the WP Customer Area store page to renew the license
     */
    public function get_renew_license_url($license_key)
    {
        $url = trailingslashit($this->store->get_store_url());
        $url .= 'checkout/';
        $url .= '?edd_license_key=' . $license_key;

        return $url;
    }

    /**
     * Query the server to check whether a license is valid or not
     *
     * @param string     $license The license key to validate
     * @param CUAR_AddOn $addon   The add-on to validate
     *
     * @return CUAR_LicenseValidationResult The result
     */
    public function validate_license($license, $addon)
    {
        if ($addon == null)
        {
            return CUAR_LicenseValidationResult::failure(
                __('Unknown add-on', 'cuar'));
        }

        $license_data = $this->query_store($license, $addon);
        if ($license_data == null)
        {
            return CUAR_LicenseValidationResult::failure(
                __('Problem when contacting our license servers. Is the license key correct? Is PHP cURL enabled?',
                    'cuar'));
        }

        if ($license_data->license == 'valid')
        {
            $result = CUAR_LicenseValidationResult::success(
                $license_data->expires === 'lifetime'
                    ? __('Your license is valid forever', 'cuar')
                    : sprintf(__('Your license is valid until: %s', 'cuar'),
                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))),
                $license_data->expires === 'lifetime' ? new DateTime('+99 years') : new DateTime($license_data->expires)
            );
        }
        else
        {
            switch ($license_data->error)
            {
                case 'revoked':
                    $result = CUAR_LicenseValidationResult::failure(
                        __('This license key has been revoked.', 'cuar'));
                    break;

                case 'no_activations_left':
                    $result = CUAR_LicenseValidationResult::failure(
                        sprintf(__(
                            'You have reached your maximum number of sites for this license key. You can use it on at most %s site(s).',
                            'cuar'),
                            $license_data->max_sites));
                    break;

                case 'expired':
                    $result = CUAR_LicenseValidationResult::failure(
                        sprintf(__('Your license has expired since %s', 'cuar'),
                            date_i18n(get_option('date_format'),
                                strtotime($license_data->expires, current_time('timestamp'))))
                        . ' '
                        . sprintf('<a href="%s" target="_blank" class="button renew-license-button">' . __('Renew license',
                                'cuar') . ' &raquo;</a>&nbsp;',
                            $this->get_renew_license_url($license)),
                        new DateTime($license_data->expires)
                    );
                    break;

                case 'key_mismatch':
                    $result = CUAR_LicenseValidationResult::failure(
                        __('This license key does not match.', 'cuar'));
                    break;

                case 'invalid_item_id':
                case 'item_name_mismatch':
                case 'product_name_mismatch':
                    $result = CUAR_LicenseValidationResult::failure(
                        __('This license key seems to have been generated for another product.', 'cuar'));
                    break;

                case 'invalid' :
                case 'site_inactive' :
                    $result = CUAR_LicenseValidationResult::failure(
                        __('Your license is not active for this URL.', 'cuar'));
                    break;

                default:
                    $result = CUAR_LicenseValidationResult::failure(
                        __('Problem when validating your license key', 'cuar') . ' -> ' . $license_data->error,
                        null,
                        $license_data);
                    break;
            }
        }

        return $result;
    }

    /**
     * @param string     $license
     * @param CUAR_AddOn $addon
     *
     * @return array|mixed|null
     */
    private function query_store($license, $addon)
    {
        // data to send in our API request
        $api_params = [
            'edd_action' => 'activate_license',
            'license'    => $license,
            'url'        => get_home_url(),
            'item_id'    => (int)($addon->store_item_id),
        ];

        // Call the custom API.
        $response = wp_remote_post($this->store->get_store_url(), [
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => $api_params,
        ]);

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response))
        {
            if (is_wp_error($response))
            {
                return json_decode(json_encode([
                    'license'  => 'error',
                    'error'    => $response->get_error_message(),
                    'response' => $response,
                ]));
            }
            else
            {
                return json_decode(json_encode([
                    'license' => 'error',
                    'error'   => 'Server responded with code ' . wp_remote_retrieve_response_code($response),
                ]));
            }
        }

        $response = wp_remote_retrieve_body($response);
        $license_data = json_decode($response);

        // If not a valid license and license is missing, return null
        if (!isset($license_data) || ($license_data->license != 'valid' && $license_data->error == 'missing'))
        {
            return json_decode(json_encode([
                'license'  => 'error',
                'error'    => 'Not a valid license or license is missing',
                'response' => $response,
            ]));
        }

        return $license_data;
    }
}

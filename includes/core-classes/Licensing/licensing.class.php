<?php

include_once('license-validation-result.class.php');

/**
 * The class that will handle licensing checks within WP Customer Area
 *
 * @todo Extract the code specific to WPCA and make this class a Framework class
 */
class CUAR_Licensing
{

    /**
     * Get the main store API url
     * @return string The URL for the WP Customer Area store
     */
    public static function get_wpca_store_url()
    {
        // TODO Change this to the prod website
        return 'http://wpca:!wpca456@staging.wp-customerarea.com';
    }

    /**
     * Get the main store API url
     * @return string The URL for the WP Customer Area store
     */
    public static function get_marvinlabs_store_url()
    {
        return 'http://www.marvinlabs.com';
    }

    /**
     * Get the url to renew a license
     *
     * @param string $license_key The license key to renew
     * @internal param int $item_id The item ID linked to the license
     * @return string The URL for the WP Customer Area store page to renew the license
     */
    public static function get_renew_license_url($license_key)
    {
        $url = trailingslashit(self::get_wpca_store_url());
        $url .= 'checkout/';
        $url .= '?edd_license_key=' . $license_key;
        return $url;
    }

    /**
     * Query the server to check whether a license is valid or not
     *
     * @param string $license The license key to validate
     * @param CUAR_AddOn $addon The add-on to validate
     * @return CUAR_LicenseValidationResult The result
     */
    public function validate_license($license, $addon)
    {
        if ($addon == null) {
            return CUAR_LicenseValidationResult::failure(
                null,
                __('Unknown add-on', 'cuar')
            );
        }

        $is_new_store_license = true;
        $license_data = $this->query_store($license, $addon, true);
        if ($license_data == null) {
            $license_data = $this->query_store($license, $addon, false);
            if ($license_data != null) $is_new_store_license = false;
        }

        if ($license_data == null) {
            return CUAR_LicenseValidationResult::failure(
                __('Problem when contacting our license servers. Is PHP cURL enabled?', 'cuar'),
                $is_new_store_license
            );
        }

        if ($license_data->license == 'valid') {
            $expiry_date = new DateTime($license_data->expires);
            $result = CUAR_LicenseValidationResult::success(
                sprintf(__('Your license is valid until: %s', 'cuar'), $expiry_date->format('d/m/Y')),
                $is_new_store_license,
                $expiry_date
            );
        } else {
            switch ($license_data->error) {
                case 'revoked':
                    $result = CUAR_LicenseValidationResult::failure(
                        __('This license key has been revoked.', 'cuar'),
                        $is_new_store_license
                    );
                    break;

                case 'no_activations_left':
                    $result = CUAR_LicenseValidationResult::failure(
                        sprintf(__('You have reached your maximum number of sites for this license key. You can use it on at most %s site(s).', 'cuar'), $license_data->max_sites),
                        $is_new_store_license
                    );
                    break;

                case 'expired':
                    $expiry_date = new DateTime($license_data->expires);

                    $result = CUAR_LicenseValidationResult::failure(
                        sprintf('<a href="%s" target="_blank" class="button renew-license-button">' . __('Renew license', 'cuar') . ' &raquo;</a>&nbsp;', self::get_renew_license_url($license))
                        . ' '
                        . sprintf(__('Your license has expired at the date: %s', 'cuar'), $expiry_date->format('d/m/Y')),
                        $is_new_store_license,
                        $expiry_date
                    );
                    break;

                case 'key_mismatch':
                    $result = CUAR_LicenseValidationResult::failure(
                        __('This license key does not match.', 'cuar'),
                        $is_new_store_license
                    );
                    break;

                case 'product_name_mismatch':
                    $result = CUAR_LicenseValidationResult::failure(
                        __('This license key seems to have been generated for another product.', 'cuar'),
                        $is_new_store_license
                    );
                    break;

                default:
                    $result = CUAR_LicenseValidationResult::failure(
                        __('Problem when validating your license key', 'cuar'),
                        $is_new_store_license
                    );
                    break;
            }
        }

        return $result;
    }

    /**
     * @param string $license
     * @param CUAR_AddOn $addon
     * @param boolean $use_new_store
     * @return array|mixed|null
     */
    private function query_store($license, $addon, $use_new_store)
    {
        // data to send in our API request
        $api_params = array(
            'edd_action'    => 'activate_license',
            'license'       => $license,
            'url'           => get_home_url()
        );

        if ($use_new_store) {
            $store_url = self::get_wpca_store_url();
            $api_params['item_id'] = (int)($addon->store_item_id);
        } else {
            $store_url = self::get_marvinlabs_store_url();
            $api_params['item_name'] = urlencode($addon->store_item_name);
        }

        // Call the custom API.
        $response = wp_remote_get(add_query_arg($api_params, $store_url), array(
            'timeout' => 15,
            'sslverify' => false
        ));

        // make sure the response came back okay
        if (is_wp_error($response)) {
            return null;
        }

        $response = wp_remote_retrieve_body($response);
        $license_data = json_decode($response);

        // If not a valid license and license is missing, return null
        if ($license_data->license != 'valid' && $license_data->error == 'missing') {
            return null;
        }

        return $license_data;
    }
}
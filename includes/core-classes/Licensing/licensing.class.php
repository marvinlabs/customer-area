<?php

include_once('license-validation-result.class.php');

/**
 * The class that will handle licensing checks within WP Customer Area
 */
class CUAR_Licensing
{

    /**
     * Get the main store API url
     * @return string The URL for the WP Customer Area store
     */
    public static function get_store_url()
    {
        // TODO Change this to the prod website
        return 'http://wpca:!wpca456@staging.wp-customerarea.com';
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
        $url = trailingslashit(self::get_store_url());
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
        if ($addon==null) {
            return CUAR_LicenseValidationResult::failure(
                null,
                __('Unknown add-on', 'cuar')
            );
        }

        // data to send in our API request
        $api_params = array (
            'edd_action' 	=> 'activate_license',
            'license' 		=> $license,
            'item_id' 	    => urlencode( $addon->store_item_id )
        );

        // Call the custom API.
        $response = wp_remote_get( add_query_arg( $api_params, self::get_store_url() ), array (
            'timeout' => 15,
            'sslverify' => false
        ) );

        // make sure the response came back okay
        if (is_wp_error( $response )) {
            return CUAR_LicenseValidationResult::failure(
                null,
                $response->get_error_message()
            );
        }

        $response = wp_remote_retrieve_body( $response );
        $license_data = json_decode( $response );

        if ( $license_data->license=='valid' ) {
            $expiry_date = new DateTime($license_data->expires);
            $result = CUAR_LicenseValidationResult::success(
                $response,
                sprintf( __( 'Your license is valid until: %s', 'cuar' ), $expiry_date->format( get_option('date_format') ) ),
                $expiry_date
            );
        } else {
            switch ( $license_data->error ) {
                case 'revoked':
                    $result = CUAR_LicenseValidationResult::failure(
                        $response,
                        __( 'This license key has been revoked.', 'cuar' )
                    );
                    break;

                case 'no_activations_left':
                    $result = CUAR_LicenseValidationResult::failure(
                        $response,
                        sprintf( __( 'You have reached your maximum number of sites for this license key. You can use it on at most %s site(s).', 'cuar' ), $license_data->max_sites )
                    );
                    break;

                case 'expired':
                    $expiry_date = new DateTime($license_data->expires);

                    $result = CUAR_LicenseValidationResult::failure(
                        $response,
                        sprintf( '<a href="%s" target="_blank" class="button renew-license-button">' . __( 'Renew license', 'cuar' ) . ' &raquo;</a>&nbsp;', self::get_renew_license_url($license) )
                            . ' '
                            . sprintf( __( 'Your license has expired at the date: %s', 'cuar' ), $expiry_date->format( get_option('date_format') )),
                        $expiry_date
                    );
                    break;

                case 'key_mismatch':
                    $result = CUAR_LicenseValidationResult::failure(
                        $response,
                        __( 'This license key does not match.', 'cuar' )
                    );
                    break;

                case 'product_name_mismatch':
                    $result = CUAR_LicenseValidationResult::failure(
                        $response,
                        __( 'This license key seems to have been generated for another product.', 'cuar' )
                    );
                    break;

                default:
                    $result = CUAR_LicenseValidationResult::failure(
                        $response,
                        __( 'Problem when validating your license key', 'cuar' )
                    );
                    break;
            }
        }

        return $result;
    }
}
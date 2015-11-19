<?php /** Template version: 1.0.0 */ ?>

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
?>

<?php /** @var $address array */ ?>
<?php /** @var $address_id string */ ?>
<?php /** @var $address_class string */ ?>
<?php /** @var $address_label string */ ?>
<?php /** @var $address_actions array */ ?>
<?php /** @var $extra_scripts string */ ?>
<?php /** @var $excluded_fields array */ ?>

<div class="cuar-address cuar-<?php echo $address_class; ?>" data-address-id="<?php echo $address_id; ?>">
    <h3><?php echo $address_label; ?></h3>

    <?php wp_nonce_field('cuar_' . $address_id, 'cuar_nonce'); ?>

    <div class="cuar-progress" style="display: none;">
        <span class="indeterminate"></span>
    </div>

    <table class="form-table">
        <tbody>
        <?php if (!empty($address_actions)) : ?>
            <tr class="cuar-address-actions">
                <th></th>
                <td>
                    <?php foreach ($address_actions as $action => $desc) : ?>
                        <a href="#" class="button cuar-action cuar-<?php echo esc_attr($action); ?>"
                           title="<?php echo esc_attr($desc['tooltip']); ?>"><?php
                            echo $desc['label'];
                            ?></a>&nbsp;
                    <?php endforeach; ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if (!in_array('name', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-name">
            <th><label for="<?php echo $address_id; ?>_name"
                       class="control-label"><?php _e('Name', 'cuar'); ?></label></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[name]" id="<?php echo $address_id; ?>_name"
                       value="<?php echo esc_attr($address['name']); ?>"
                       placeholder="<?php esc_attr_e('Name', 'cuar'); ?>" class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('company', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-company">
            <th><label for="<?php echo $address_id; ?>_company"
                       class="control-label"><?php _e('Company', 'cuar'); ?></label></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[company]" id="<?php echo $address_id; ?>_company"
                       value="<?php echo esc_attr($address['company']); ?>"
                       placeholder="<?php esc_attr_e('Company', 'cuar'); ?>" class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('vat_number', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-vat-number">
            <th><label for="<?php echo $address_id; ?>_vat_id"
                       class="control-label"><?php _e('VAT Number', 'cuar'); ?></label></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[vat_number]"
                       id="<?php echo $address_id; ?>_vat_number"
                       value="<?php echo esc_attr($address['vat_number']); ?>"
                       placeholder="<?php esc_attr_e('VAT Number', 'cuar'); ?>"
                       class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('logo_url', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-logo-url">
            <th><label for="<?php echo $address_id; ?>_logo_url" class="control-label"><?php _e('Logo', 'cuar'); ?></label></th>
            <td>
                <div class="cuar-upload-control">
                    <input type="text" name="<?php echo $address_id; ?>[logo_url]" id="<?php echo $address_id; ?>_logo" value="<?php echo esc_attr($address['logo_url']); ?>" placeholder="<?php esc_attr_e('Logo URL', 'cuar'); ?>" class="regular-text cuar-address-field cuar-upload-input"/>
                    <span>&nbsp;<input type="button" class="cuar-upload-button button-secondary" value="<?php _e('Upload Logo', 'cuar'); ?>"/></span>
                </div>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('line1', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-line1">
            <th><label for="<?php echo $address_id; ?>_line1" class="control-label"><?php _e('Street address', 'cuar'); ?></label></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[line1]" id="<?php echo $address_id; ?>_line1" value="<?php echo esc_attr($address['line1']); ?>" placeholder="<?php esc_attr_e('Street address, line 1', 'cuar'); ?>" class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('line2', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-line2">
            <th></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[line2]" id="<?php echo $address_id; ?>_line2" value="<?php echo esc_attr($address['line2']); ?>" placeholder="<?php esc_attr_e('Street address, line 2', 'cuar'); ?>" class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('zip', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-zip">
            <th><label for="<?php echo $address_id; ?>_zip" class="control-label"><?php _e('Zip/Postal code', 'cuar'); ?></label></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[zip]" id="<?php echo $address_id; ?>_zip" value="<?php echo esc_attr($address['zip']); ?>" placeholder="<?php esc_attr_e('Zip/Postal code', 'cuar'); ?>" class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('city', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-city">
            <th><label for="<?php echo $address_id; ?>_city" class="control-label"><?php _e('City', 'cuar'); ?></label></th>
            <td>
                <input type="text" name="<?php echo $address_id; ?>[city]" id="<?php echo $address_id; ?>_city" value="<?php echo esc_attr($address['city']); ?>" placeholder="<?php esc_attr_e('City', 'cuar'); ?>" class="regular-text cuar-address-field"/>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('country', $excluded_fields)) : ?>
        <tr class="form-group cuar-address-country">
            <th><label for="<?php echo $address_id; ?>_country" class="control-label"><?php _e('Country', 'cuar'); ?></label></th>
            <td>
                <select name="<?php echo $address_id; ?>[country]" id="<?php echo $address_id; ?>_country" class="regular-text cuar-address-field">
                    <?php foreach (CUAR_CountryHelper::getCountries() as $code => $label) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($address['country'], $code); ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!in_array('state', $excluded_fields)) : ?>
        <?php $country_states = CUAR_CountryHelper::getStates($address['country']); ?>
        <tr class="form-group cuar-address-state" <?php if (empty($country_states)) echo 'style="display: none;"'; ?>>
            <th><label for="<?php echo $address_id; ?>_state" class="control-label"><?php _e('State/Province', 'cuar'); ?></label></th>
            <td>
                <select name="<?php echo $address_id; ?>[state]" id="<?php echo $address_id; ?>_state" class="regular-text cuar-address-field">
                    <?php foreach ($country_states as $code => $label) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($address['state'], $code); ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.cuar-<?php echo $address_class; ?>').addressManager();
        $(".cuar-<?php echo $address_class; ?> .cuar-upload-control").mediaInputControl();
        $('.cuar-<?php echo $address_class; ?> table').bindCountryStateInputs({
            select2: {
                allowClear: true,
                placeholder: "",
                width: '25em'
            }
        });
        <?php echo $extra_scripts; ?>
    });
</script>
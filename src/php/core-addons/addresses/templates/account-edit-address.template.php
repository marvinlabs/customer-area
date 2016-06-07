<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 *
 * -= 3.0.0 =-
 * - Updated markup for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 */ ?>

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

<div class="cuar-address cuar-js-address col-lg-6 col-md-12 cuar-<?php echo $address_class; ?>" data-address-id="<?php echo $address_id; ?>">
    <?php wp_nonce_field('cuar_' . $address_id, 'cuar_nonce'); ?>

    <div class="panel">
        <div class="panel-heading">
            <span class="panel-title"><?php echo $address_label; ?></span>

            <?php if ( !empty($address_actions)) : ?>
                <div class="widget-menu pull-right mr10">
                    <div class="cuar-address-actions cuar-js-address-actions btn-group">
                        <?php foreach ($address_actions as $action => $desc) : ?>
                            <a href="#" class="btn btn-default btn-xs cuar-action cuar-js-action cuar-js-<?php echo esc_attr($action); ?>" title="<?php echo esc_attr($desc['tooltip']); ?>">
                                <?php echo $desc['label']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <span class="cuar-progress cuar-js-progress" style="display: none;"><span class="indeterminate"></span></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <?php if ( !in_array('name', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-name">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_name" class="control-label"><?php _e('Name', 'cuar'); ?></label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" name="<?php echo $address_id; ?>[name]" id="<?php echo $address_id; ?>_name" value="<?php echo esc_attr($address['name']); ?>" placeholder="<?php esc_attr_e('Name', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ( !in_array('company', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-company">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_company" class="control-label"><?php _e('Company', 'cuar'); ?></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="control-container">
                                <input type="text" name="<?php echo $address_id; ?>[company]" id="<?php echo $address_id; ?>_company" value="<?php echo esc_attr($address['company']); ?>" placeholder="<?php esc_attr_e('Company', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( !in_array('vat_number', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-vat-number">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_vat_id" class="control-label"><?php _e('VAT Number', 'cuar'); ?></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="control-container">
                                <input type="text" name="<?php echo $address_id; ?>[vat_number]" id="<?php echo $address_id; ?>_vat_number" value="<?php echo esc_attr($address['vat_number']); ?>" placeholder="<?php esc_attr_e('VAT Number', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( !in_array('line1', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-line1">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_line1" class="control-label"><?php _e('Street address', 'cuar'); ?></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="control-container">
                                <input type="text" name="<?php echo $address_id; ?>[line1]" id="<?php echo $address_id; ?>_line1" value="<?php echo esc_attr($address['line1']); ?>" placeholder="<?php esc_attr_e('Street address, line 1', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( !in_array('line2', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-line2">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_line2" class="control-label"></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="control-container">
                                <input type="text" name="<?php echo $address_id; ?>[line2]" id="<?php echo $address_id; ?>_line2" value="<?php echo esc_attr($address['line2']); ?>" placeholder="<?php esc_attr_e('Street address, line 2', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( !in_array('zip', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-zip">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_zip" class="control-label"><?php _e('Zip/Postal code', 'cuar'); ?></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="control-container">
                                <input type="text" name="<?php echo $address_id; ?>[zip]" id="<?php echo $address_id; ?>_zip" value="<?php echo esc_attr($address['zip']); ?>" placeholder="<?php esc_attr_e('Zip/Postal code', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( !in_array('city', $excluded_fields)) : ?>
                <div class="form-group cuar-js-address-field-container cuar-js-address-city">
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="<?php echo $address_id; ?>_city" class="control-label"><?php _e('City', 'cuar'); ?></label>
                        </div>
                        <div class="col-sm-9">
                            <div class="control-container">
                                <input type="text" name="<?php echo $address_id; ?>[city]" id="<?php echo $address_id; ?>_city" value="<?php echo esc_attr($address['city']); ?>" placeholder="<?php esc_attr_e('City', 'cuar'); ?>" class="form-control cuar-js-address-field"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="cuar-js-country-state-inputs">
                <?php if ( !in_array('country', $excluded_fields)) : ?>
                    <div class="form-group cuar-js-address-field-container cuar-js-address-country">
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="<?php echo $address_id; ?>_country" class="control-label"><?php _e('Country', 'cuar'); ?></label>
                            </div>
                            <div class="col-sm-9">
                                <div class="control-container">
                                    <select name="<?php echo $address_id; ?>[country]" id="<?php echo $address_id; ?>_country" class="cuar-js-address-field">
                                        <?php foreach (CUAR_CountryHelper::getCountries() as $code => $label) : ?>
                                            <option value="<?php echo esc_attr($code); ?>" <?php selected($address['country'], $code); ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( !in_array('state', $excluded_fields)) : ?><?php $country_states = CUAR_CountryHelper::getStates($address['country']); ?>
                    <div class="form-group cuar-js-address-field-container cuar-js-address-state" <?php if (empty($country_states)) echo 'style="display: none;"'; ?>>
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="<?php echo $address_id; ?>_state" class="control-label"><?php _e('State/Province', 'cuar'); ?></label>
                            </div>
                            <div class="col-sm-9">
                                <div class="control-container">
                                    <select name="<?php echo $address_id; ?>[state]" id="<?php echo $address_id; ?>_state" class="cuar-js-address-field">
                                        <?php foreach ($country_states as $code => $label) : ?>
                                            <option value="<?php echo esc_attr($code); ?>" <?php selected($address['state'], $code); ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    <!--
    (function ($) {
        "use strict";
        $(document).ready(function ($) {
            $('.cuar-<?php echo $address_class; ?>').addressManager();
            // $(".cuar-<?php echo $address_class; ?> .cuar-js-upload-control").mediaInputControl();
            $('.cuar-<?php echo $address_class; ?> .cuar-js-country-state-inputs').bindCountryStateInputs();
            <?php echo $extra_scripts; ?>
        });
    })(jQuery);
    //-->
</script>
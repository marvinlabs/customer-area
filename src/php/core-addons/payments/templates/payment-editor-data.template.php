<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_Payment $payment */ ?>

<div class="submitbox cuar-payment-data" id="submitpost">
    <div id="minor-publishing">
        <div class="row">
            <div class="form-group cuar-data-date">
                <label for="data_date" class="control-label"><?php _e('Date', 'cuar'); ?></label>

                <div class="control-container">
                    <div class="cuar-datepicker-control">
                        <input type="hidden" id="data_date" name="data[date]" value="<?php echo get_the_date('Y-m-d', $payment->get_post()); ?>"/>
                        <input type="text" id="data_date_display" name="data_date_display" value="" class="form-control cuar-datepicker-input"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group cuar-data-status">
                <label for="data_status" class="control-label"><?php _e('Status', 'cuar'); ?></label>

                <div class="control-container">
                    <select name="data[status]" id="data_status" class="form-control">
                        <?php foreach (CUAR_PaymentStatus::get_payment_statuses() as $id => $label) : ?>
                            <option value="<?php echo esc_attr($id); ?>" <?php selected($id, $payment->get_post()->post_status); ?>><?php
                                echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group cuar-data-currency">
                <label for="data_currency" class="control-label"><?php _e('Currency', 'cuar'); ?></label>

                <div class="control-container">
                    <select name="data[currency]" id="data_currency" class="form-control">
                        <?php foreach (CUAR_CurrencyHelper::getCurrencies() as $code => $label) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($payment->get_currency(), $code); ?>><?php
                                echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group cuar-data-amount">
                <label for="data_amount" class="control-label"><?php _e('Amount', 'cuar'); ?></label>

                <div class="control-container">
                    <input type="number" name="data[amount]" id="data_amount" value="<?php echo esc_attr($payment->get_amount()); ?>" step="any" min="0" class="form-control"/>
                </div>
            </div>
        </div>
    </div>


    <div id="major-publishing-actions">
        <div id="publishing-action">
            <span class="spinner"></span>
            <input name="original_publish" type="hidden" id="original_publish" value="Update">
            <input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e('Update payment', 'cuar'); ?>">
        </div>
        <div class="clear"></div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.cuar-payment-data').paymentDataManager();
    });
</script>
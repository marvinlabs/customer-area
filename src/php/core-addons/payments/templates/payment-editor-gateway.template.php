<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_Payment $payment */ ?>
<?php /** @var array $gateways  */ ?>

<div class="cuar-payment-gateway">
    <div class="row">
        <div class="form-group cuar-settings-currency">
            <label for="gateway_gateway" class="control-label"><?php _e('Gateway', 'cuar'); ?></label>

            <div class="control-container">
                <select name="gateway[gateway]" id="gateway_gateway" class="form-control">
                    <?php foreach ($gateways as $id => $gateway) : ?>
                        <option value="<?php echo esc_attr($id); ?>" <?php selected($id, $payment->get_gateway()); ?>><?php
                            echo $gateway->get_name(); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?php do_action('cuar/core/payments/templates/payment-editor-gateway-meta?gateway=' . $payment->get_gateway(), $payment); ?>
</div>
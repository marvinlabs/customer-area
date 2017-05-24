<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $accepted_credit_cards */ ?>
<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var double $remaining_amount */ ?>
<?php /** @var double $paid_amount */ ?>
<?php /** @var string $currency */ ?>
<?php /** @var string $button_label */ ?>
<?php /** @var array $address */ ?>

<form action="<?php echo esc_attr(cuar_get_checkout_url()); ?>" method="POST">
    <input type="hidden" name="cuar_object_type" value="<?php echo esc_attr($object_type); ?>"/>
    <input type="hidden" name="cuar_object_id" value="<?php echo esc_attr($object_id); ?>"/>
    <input type="hidden" name="cuar_amount" value="<?php echo esc_attr($remaining_amount); ?>"/>
    <input type="hidden" name="cuar_currency" value="<?php echo esc_attr($currency); ?>"/>
    <?php echo CUAR_AddressHelper::get_address_as_hidden_input($address, 'cuar_address'); ?>

    <?php do_action('cuar/core/payments/templates/before-payment-button', $object_type, $object_id); ?>

    <div class="row mb-md">
        <div class="col-xs-12">
            <?php if ($paid_amount <= 0) : ?>
                <h3><?php _e('Pay now', 'cuar'); ?></h3>
            <?php elseif ($remaining_amount > 0) : ?>
                <h3><?php printf(__('You have already paid %s', 'cuar'), CUAR_CurrencyHelper::formatAmount($paid_amount, $currency)); ?></h3>
            <?php else: ?>
                <h3><?php _e('You have already paid the full amount. Thank you.', 'cuar'); ?></h3>
            <?php endif; ?>
        </div>
    </div>
    <div class="row mb-md">
        <div class="form-group col-xs-8">
            <p><?php _e('We accept the following secure payment methods', 'cuar'); ?></p>

            <ul class="list-inline mt-sm cuar-payment-methods">
                <?php foreach ($accepted_credit_cards as $cc_id => $cc): ?>
                    <li>
                        <?php if ( !empty($cc['icon'])) : ?>
                            <img src="<?php echo esc_attr($cc['icon']); ?>" title="<?php echo esc_attr($cc['label']); ?>" class="cuar-gateway-icon" />
                        <?php else: ?><?php echo esc_attr($cc['label']); ?><?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php foreach ($gateways as $gateway_id => $gateway):
                    $icon = $gateway->get_icon();
                    ?>
                    <li>
                        <?php if ( !empty($icon['link'])) : ?>
                        <a href="<?php echo esc_attr($icon['link']); ?>" title="<?php echo esc_attr($gateway->get_name()); ?>" target="_blank">
                            <?php endif; ?>

                            <?php if ( !empty($icon['icon'])) : ?>
                                <img src="<?php echo esc_attr($icon['icon']); ?>" title="<?php echo esc_attr($gateway->get_name()); ?>" class="cuar-gateway-icon"/>
                            <?php else: ?><?php echo $gateway->get_name(); ?><?php endif; ?>

                            <?php if ( !empty($icon['link'])) : ?>
                        </a>
                    <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="form-group col-xs-4 text-right">
            <button type="submit" name="cuar_do_checkout" class="btn btn-primary btn-lg"><?php echo $button_label; ?></button>
        </div>
    </div>

    <?php do_action('cuar/core/payments/templates/after-payment-button', $object_type, $object_id); ?>
</form>

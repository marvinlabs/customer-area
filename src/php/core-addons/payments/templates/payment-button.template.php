<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $payment_icons */ ?>
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

    <div class="panel panel-default mb-md">
        <div class="panel-heading">
            <h3 class="panel-title">
                <?php if ($paid_amount <= 0) : ?>
                    <?php _e('Pay now', 'cuar'); ?>
                <?php elseif ($remaining_amount > 0) : ?>
                    <?php printf(__('You have already paid %s', 'cuar'), CUAR_CurrencyHelper::formatAmount($paid_amount, $currency)); ?>
                <?php else: ?>
                    <?php _e('You have already paid the full amount. Thank you.', 'cuar'); ?>
                <?php endif; ?>
            </h3>
        </div>
        <div class="panel-body">
            <p><?php _e('We accept the following secure payment methods.', 'cuar'); ?></p>

            <ul class="list-inline mt-sm cuar-payment-methods">
                <?php foreach ($payment_icons as $icon_id => $icon): ?>
                    <li>
                        <?php if ( !empty($icon['link'])) : ?>
                        <a href="<?php echo esc_attr($icon['link']); ?>" title="<?php echo esc_attr($icon['label']); ?>" target="_blank">
                            <?php endif; ?>
                            <?php if ( !empty($icon['icon'])) : ?>
                                <img src="<?php echo esc_attr($icon['icon']); ?>" title="<?php echo esc_attr($icon['label']); ?>" class="cuar-gateway-icon"/>
                            <?php else: ?><?php echo esc_attr($icon['label']); ?><?php endif; ?>
                            <?php if ( !empty($icon['link'])) : ?>
                        </a>
                    <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="panel-footer">
            <div class="text-right">
                <button type="submit" name="cuar_do_checkout" class="btn btn-primary btn-lg"><?php echo $button_label; ?></button>
            </div>
        </div>
    </div>

    <?php do_action('cuar/core/payments/templates/after-payment-button', $object_type, $object_id); ?>
</form>

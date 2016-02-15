<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $accepted_credit_cards */ ?>
<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var double $amount */ ?>
<?php /** @var string $currency */ ?>
<?php /** @var string $label The label to show on the button */ ?>

<form action="" method="POST">
    <input type="hidden" name="cuar_object_type" value="<?php echo esc_attr($object_type); ?>"/>
    <input type="hidden" name="cuar_object_id" value="<?php echo esc_attr($object_id); ?>"/>
    <input type="hidden" name="cuar_amount" value="<?php echo esc_attr($amount); ?>"/>
    <input type="hidden" name="cuar_currency" value="<?php echo esc_attr($currency); ?>"/>

    <?php do_action('cuar/private-content/payments/before-payment-button', $object_type, $object_id); ?>

    <div class="row">
        <div class="form-group col-xs-8">
            <p><?php _e('We accept the following secure payment methods', 'cuar'); ?></p>

            <ul class="list-inline mt-sm">
                <?php foreach ($accepted_credit_cards as $cc_id => $cc): ?>
                    <li>
                        <?php if ( !empty($cc['icon'])) : ?>
                            <img src="<?php echo esc_attr($cc['icon']); ?>" title="<?php echo esc_attr($cc['label']); ?>"/>
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
                        <img src="<?php echo esc_attr($icon['icon']); ?>" title="<?php echo esc_attr($gateway->get_name()); ?>"/>
                    <?php else: ?>
                        <?php echo $gateway->get_name(); ?>
                    <?php endif; ?>

                    <?php if ( !empty($icon['link'])) : ?>
                        </a>
                    <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="form-group col-xs-4 text-right">
            <button type="submit" name="cuar_do_pay" class="btn btn-primary btn-lg"><?php echo $label; ?></button>
        </div>
    </div>

    <?php do_action('cuar/private-content/payments/after-payment-button', $object_type, $object_id); ?>
</form>

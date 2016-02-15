<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var CUAR_PaymentGateway $gateway */ ?>
<?php /** @var string $selected_gateway */ ?>

<div class="row mt-lg clearfix cuar-js-gateway-picker">
    <div class="col-md-12">
        <ul class="list-inline">
            <?php foreach ($gateways as $gateway_id => $gateway):
                $icon = $gateway->get_icon();
                ?>
                <li>
                    <div class="radio-custom mb5">
                        <input type="radio" class="cuar-js-gateway-selector" id="gateway_select_<?php echo esc_attr($gateway->get_id()); ?>" name="cuar_gateway" value="<?php echo esc_attr($gateway->get_id()); ?>" data-gateway="<?php echo esc_attr($gateway->get_id()); ?>" <?php checked($selected_gateway, $gateway_id); ?>>
                        <label for="gateway_select_<?php echo esc_attr($gateway->get_id()); ?>">
                            <?php if ( !empty($icon['checkout_icon'])) : ?>
                                <img src="<?php echo esc_attr($icon['checkout_icon']); ?>" title="<?php echo esc_attr($gateway->get_name()); ?>"/>
                            <?php else: ?>
                                <?php echo $gateway->get_name(); ?>
                            <?php endif; ?>
                        </label>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="col-md-12">
    <?php foreach ($gateways as $gateway_id => $gateway): ?>
        <div class="cuar-js-gateway-form" data-gateway="<?php echo esc_attr($gateway->get_id()); ?>" <?php if ($selected_gateway!=$gateway_id) echo 'style="display: none;"'; ?>>
            <?php if ($gateway->has_form()) $gateway->print_form(); ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
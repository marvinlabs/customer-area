<?php
/**
 * Template version: 3.1.1
 *
 * -= 3.1.1 =-
 * - Fix bug when using multiple gateways
 *
 * -= 3.1.0 =-
 * - Replace clearfix CSS classes with cuar-clearfix
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var CUAR_PaymentGateway $gateway */ ?>
<?php /** @var string $selected_gateway */ ?>

<?php
$is_single_gateway = (count($gateways)==1);
$first_gateway = reset($gateways);
if ($is_single_gateway || !isset($gateways[$selected_gateway])) $selected_gateway = $first_gateway;
else $selected_gateway = $gateways[$selected_gateway];
?>

<?php do_action('cuar/core/payments/templates/checkout/before-gateways'); ?>

<div class="panel cuar-js-gateway-picker">
    <div class="panel-heading">
        <span class="panel-title"><?php
            if ($is_single_gateway) printf(__('Pay with %s', 'cuar'), $first_gateway->get_name());
            else _e('Choose a payment method', 'cuar');
            ?></span>
    </div>
    <?php if (!$is_single_gateway): ?>
    <div class="panel-menu">
        <div class="btn-group">
        <?php foreach ($gateways as $gateway_id => $gateway): ?>
            <div class="btn radio-custom">
                <input type="radio" class="cuar-js-gateway-selector" id="gateway_select_<?php echo esc_attr($gateway->get_id()); ?>" name="cuar_selected_gateway" value="<?php echo esc_attr($gateway->get_id()); ?>" data-gateway="<?php echo esc_attr($gateway->get_id()); ?>" <?php checked($selected_gateway->get_id(), $gateway_id); ?>>
                <label for="gateway_select_<?php echo esc_attr($gateway->get_id()); ?>">
                    <?php echo $gateway->get_name(); ?>
                </label>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <input type="radio" class="cuar-js-gateway-selector" id="gateway_select_<?php echo esc_attr
        ($selected_gateway->get_id()); ?>" name="cuar_selected_gateway" value="<?php echo esc_attr
        ($selected_gateway->get_id()); ?>" data-gateway="<?php echo esc_attr($selected_gateway->get_id()); ?>"
               checked="checked" style="display: none;">
    <?php endif; ?>
    <div class="panel-body">
        <?php foreach ($gateways as $gateway_id => $gateway): ?>
            <div class="cuar-clearfix cuar-js-gateway-form" data-gateway="<?php echo esc_attr($gateway->get_id()); ?>" <?php if ($selected_gateway->get_id()!=$gateway_id) echo 'style="display: none;"'; ?>>
                <?php if ($gateway->has_form()) $gateway->print_form(); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php do_action('cuar/core/payments/templates/checkout/after-gateways'); ?>

<?php if (!$is_single_gateway): ?>
<script type="text/javascript">
    <!--
    jQuery(document).ready(function($) {
       $('.cuar-js-gateway-picker').gatewayPicker();
    });
    // -->
</script>
<?php endif; ?>

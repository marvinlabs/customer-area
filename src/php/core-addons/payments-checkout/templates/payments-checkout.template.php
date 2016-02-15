<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $accepted_credit_cards */ ?>
<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var double $amount */ ?>
<?php /** @var string $currency */ ?>
<?php /** @var CUAR_AddressesAddOn $ad_addon */ ?>
<?php /** @var array $address */ ?>
<?php /** @var string $summary_template */ ?>
<?php /** @var string $address_template */ ?>
<?php /** @var string $gateways_template */ ?>

<form action="<?php echo esc_attr(cuar_get_checkout_url()); ?>" method="POST">
    <input type="hidden" name="cuar_object_type" value="<?php echo esc_attr($object_type); ?>"/>
    <input type="hidden" name="cuar_object_id" value="<?php echo esc_attr($object_id); ?>"/>
    <input type="hidden" name="cuar_amount" value="<?php echo esc_attr($amount); ?>"/>
    <input type="hidden" name="cuar_currency" value="<?php echo esc_attr($currency); ?>"/>

    <?php do_action('cuar/private-content/payments/checkout/before-summary'); ?>

    <?php include($summary_template); ?>

    <h3><?php _e('Payment information', 'cuar'); ?></h3>

    <?php do_action('cuar/private-content/payments/checkout/before-address'); ?>

    <?php include($address_template); ?>

    <h3><?php _e('Choose a payment method', 'cuar'); ?></h3>

    <?php do_action('cuar/private-content/payments/checkout/before-gateways'); ?>

    <?php include($gateways_template); ?>

    <?php do_action('cuar/private-content/payments/checkout/before-validate-button'); ?>

    <div class="row mt-md clearfix">
        <div class="form-group col-xs-12 text-right">
            <button type="submit" name="cuar_do_pay" class="btn btn-primary btn-lg"><?php _e('Continue', 'cuar'); ?></button>
        </div>
    </div>
</form>
<?php
/**
 * Template version: 3.1.0
 *
 * -= 3.1.0 =-
 * - Replace clearfix CSS classes with cuar-clearfix
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

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
    <div id="cuar_payment_data" data-object-title="<?php echo esc_attr(get_the_title($object_id)); ?>" data-amount="<?php echo esc_attr($amount); ?>" data-currency="<?php echo esc_attr($currency); ?>"></div>
    <?php wp_nonce_field("process_payment_" . md5($object_type . $object_id), "cuar_process_payment_nonce"); ?>
    <input type="hidden" name="cuar_action" value="process_payment"/>
    <input type="hidden" name="cuar_object_type" value="<?php echo esc_attr($object_type); ?>"/>
    <input type="hidden" name="cuar_object_id" value="<?php echo esc_attr($object_id); ?>"/>
    <input type="hidden" name="cuar_amount" value="<?php echo esc_attr($amount); ?>"/>
    <input type="hidden" name="cuar_currency" value="<?php echo esc_attr($currency); ?>"/>

    <?php if ( !empty($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="row cuar-clearfix mb-md">
        <div class="col-md-6">
            <?php include($summary_template); ?>
        </div>
        <div class="col-md-6">
            <?php include($address_template); ?>
        </div>
    </div>

    <?php include($gateways_template); ?>

    <div class="row mt-md cuar-clearfix">
        <div class="form-group col-xs-12 text-right">
            <?php do_action('cuar/core/payments/templates/checkout/before-validate-button'); ?>

            <button type="submit" name="cuar_do_pay" class="btn btn-primary btn-lg"><?php _e('Continue', 'cuar'); ?></button>

            <?php do_action('cuar/core/payments/templates/checkout/after-validate-button'); ?>
        </div>
    </div>
</form>
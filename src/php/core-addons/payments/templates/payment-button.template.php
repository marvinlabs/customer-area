<?php /** Template version: 3.0.0 */ ?>

<?php /** @var string $object_type */ ?>
<?php /** @var int    $object_id */ ?>
<?php /** @var double $amount */ ?>
<?php /** @var string $currency */ ?>
<?php /** @var string $label The label to show on the button */ ?>

<form action="" method="POST">
    <input type="hidden" name="cuar_object_type" value="<?php echo esc_attr($object_type); ?>" />
    <input type="hidden" name="cuar_object_id" value="<?php echo esc_attr($object_id); ?>" />
    <input type="hidden" name="cuar_amount" value="<?php echo esc_attr($amount); ?>" />
    <input type="hidden" name="cuar_currency" value="<?php echo esc_attr($currency); ?>" />

    <?php do_action('cuar/private-content/payments/before-payment-button', $object_type, $object_id); ?>

    <input type="submit" name="cuar_do_pay" value="<?php echo esc_attr($label); ?>" />

    <?php do_action('cuar/private-content/payments/after-payment-button', $object_type, $object_id); ?>
</form>

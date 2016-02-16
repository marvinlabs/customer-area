<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $accepted_credit_cards */ ?>
<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var double $amount */ ?>
<?php /** @var string $currency */ ?>

<?php
$pto = get_post_type_object($object_type);

$title = '<span class="panel-title">' . $pto->labels->singular_name . '</span>';
$title = apply_filters('cuar/core/payments/templates/checkout/summary/title', $title, $object_type, $object_id);

$content =  get_the_title($object_id);
$content = apply_filters('cuar/core/payments/templates/checkout/summary/content', $content, $object_type, $object_id);

$footer = '<strong class="fs-lg pull-right">' . CUAR_CurrencyHelper::formatAmount($amount, $currency, '') . '</strong>';
$footer = apply_filters('cuar/core/payments/templates/checkout/summary/footer', $footer, $object_type, $object_id);
?>

<?php do_action('cuar/core/payments/templates/checkout/before-summary'); ?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?php echo $title; ?>
    </div>
    <div class="panel-body">
        <?php echo $content; ?>
    </div>
    <div class="panel-footer clearfix">
        <?php echo $footer; ?>
    </div>
</div>

<?php do_action('cuar/core/payments/templates/checkout/after-summary'); ?>
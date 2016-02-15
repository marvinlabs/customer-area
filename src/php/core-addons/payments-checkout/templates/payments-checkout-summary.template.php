<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $accepted_credit_cards */ ?>
<?php /** @var array $gateways */ ?>
<?php /** @var string $object_type */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var double $amount */ ?>
<?php /** @var string $currency */ ?>


<?php do_action('cuar/private-content/payments/checkout/summary', $object_type, $object_id); ?>
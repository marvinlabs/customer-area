<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_ChequePaymentGateway $gateway */ ?>

<div class="row">
    <div class="form-group col-xs-12">
        <?php echo $gateway->get_checkout_message(); ?>
    </div>
</div>
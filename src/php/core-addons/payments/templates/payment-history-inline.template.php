<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $payments */ ?>
<?php /** @var double $paid_amount */ ?>
<?php /** @var double $remaining_amount */ ?>
<?php /** @var string $currency */ ?>

<div class="row">
    <div class="col-xs-12">
        <?php if ($remaining_amount > 0) : ?>
            <h3><?php printf(__('You have already paid %s', 'cuar'), CUAR_CurrencyHelper::formatAmount($paid_amount, $currency)); ?></h3>
        <?php else: ?>
            <h3><?php _e('You have already paid the full amount. Thank you.', 'cuar'); ?></h3>
        <?php endif; ?>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <table class="table">
            <tr>
                <th><?php _e('Gateway', 'cuar'); ?></th>
                <th><?php _e('Date', 'cuar'); ?></th>
                <th><?php _e('Amount', 'cuar'); ?></th>
            </tr>
            <?php foreach ($payments as $payment) : ?>
                <tr>
                    <td><?php cuar_the_payment_gateway($payment); ?></td>
                    <td><?php cuar_the_payment_date($payment); ?></td>
                    <td><?php cuar_the_payment_amount($payment); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
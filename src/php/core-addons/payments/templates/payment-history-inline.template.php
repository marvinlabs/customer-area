<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $payments */ ?>

<div class="row">
    <div class="col-xs-12">
        <h3><?php _e('Payment history', 'cuar'); ?></h3>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <table class="table">
            <tr>
                <th><?php _e('Date', 'cuar'); ?></th>
                <th><?php _e('Gateway', 'cuar'); ?></th>
                <th><?php _e('Amount', 'cuar'); ?></th>
                <th><?php _e('Status', 'cuar'); ?></th>
            </tr>
            <?php foreach ($payments as $payment) : ?>
                <tr>
                    <td><?php cuar_the_payment_date($payment); ?></td>
                    <td><?php cuar_the_payment_gateway($payment); ?></td>
                    <td><?php cuar_the_payment_amount($payment); ?></td>
                    <td><?php cuar_the_payment_status($payment); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $payments */ ?>

<div class="row mb-md">
    <div class="col-xs-12">
        <h2><?php _e('Payment history', 'cuar'); ?></h2>
    </div>
</div>
<div class="row mb-md">
    <div class="col-xs-12">
        <table class="table table-striped table-hover table-responsive panel panel-default">
            <thead class="panel-heading">
            <tr>
                <th><?php _e('Date', 'cuar'); ?></th>
                <th><?php _e('Gateway', 'cuar'); ?></th>
                <th><?php _e('Status', 'cuar'); ?></th>
                <th class="text-right"><?php _e('Amount', 'cuar'); ?></th>
            </tr>
            </thead>
            <tbody class="panel-body">
            <?php foreach ($payments as $payment) : ?>
                <tr>
                    <td><?php cuar_the_payment_date($payment); ?></td>
                    <td><?php cuar_the_payment_gateway($payment); ?></td>
                    <td><?php cuar_the_payment_status($payment); ?></td>
                    <td class="text-nowrap text-right"><?php cuar_the_payment_amount($payment); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
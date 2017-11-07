<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $payments */ ?>
<?php /** @var int $object_id */ ?>
<?php /** @var string $object_type */ ?>

<?php
$create_url = admin_url('admin.php?cuar_action=create-payment&object_id=' . $object_id . '&object_type=' . $object_type);
$create_url = wp_nonce_url($create_url, 'create-payment');
?>

<p>
    <a href="<?php echo esc_attr($create_url); ?>" class="button button-primary"><?php _e('Add payment manually', 'cuar'); ?></a>
</p>

<table class="widefat">
    <tr>
        <th><?php _e('ID', 'cuar'); ?></th>
        <th><?php _e('Date', 'cuar'); ?></th>
        <th><?php _e('Gateway', 'cuar'); ?></th>
        <th><?php _e('Amount', 'cuar'); ?></th>
        <th><?php _e('Status', 'cuar'); ?></th>
        <th></th>
    </tr>
    <?php foreach ($payments as $payment) : ?>
        <tr>
            <td><?php echo $payment->ID; ?></td>
            <td><?php cuar_the_payment_date($payment); ?></td>
            <td><?php cuar_the_payment_gateway($payment, false); ?></td>
            <td><?php cuar_the_payment_amount($payment); ?></td>
            <td><?php cuar_the_payment_status($payment); ?></td>
            <td><a href="<?php echo admin_url('post.php?action=edit&post_type=' . CUAR_Payment::$POST_TYPE . '&post=' . $payment->ID); ?>" class="button"><?php
                    _e('Edit', 'cuar'); ?></a></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_BacsPaymentGateway $gateway */ ?>
<?php /** @var CUAR_Settings $settings */ ?>

<tr valign="top">
    <td>
        <p><strong><?php _e('Messages', 'cuar'); ?></strong></p>
        <hr>
    </td>
</tr>
<tr valign="top">
    <td>
        <strong><?php _e('Success message', 'cuar'); ?></strong> <br>
        <p class="description"><?php _e('Message to show once the payment is validated by the user.', 'cuar'); ?></p><br>
        <?php $settings->print_input_field(array(
            'option_id' => $gateway->get_option_id(CUAR_BacsPaymentGateway::$OPTION_SUCCESS_MESSAGE),
            'type'      => 'editor',
        )); ?>
    </td>
</tr>
<tr valign="top">
    <td>
        <strong><?php _e('Checkout message', 'cuar'); ?></strong> <br>
        <p class="description"><?php _e('Message to show on the checkout page.', 'cuar'); ?></p><br>
        <?php $settings->print_input_field(array(
            'option_id' => $gateway->get_option_id(CUAR_BacsPaymentGateway::$OPTION_CHECKOUT_MESSAGE),
            'type'      => 'editor',
        )); ?>
    </td>
</tr>

<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_AbstractPaymentGateway $gateway */ ?>
<?php /** @var CUAR_Settings $settings */ ?>

<tr valign="top">
    <td>
        <h2><?php echo $gateway->get_name(); ?></h2>
        <p><?php echo $gateway->get_description(); ?></p>
    </td>
</tr>
<tr valign="top">
    <td>
        <p><strong><?php _e('General settings', 'cuar'); ?></strong></p>
        <hr>
    </td>
</tr>
<tr valign="top">
    <td>
        <?php $settings->print_input_field(array(
            'option_id' => $gateway->get_option_id(CUAR_AbstractPaymentGateway::$OPTION_ENABLED),
            'type'      => 'checkbox',
            'after'     => __('Allow payments through this gateway', 'cuar')
        )); ?>
    </td>
</tr>
<tr valign="top">
    <td>
        <?php $settings->print_input_field(array(
            'option_id' => $gateway->get_option_id(CUAR_AbstractPaymentGateway::$OPTION_LOG_ENABLED),
            'type'      => 'checkbox',
            'after'     => __('Enable error logging to a file', 'cuar')
        )); ?>
    </td>
</tr>
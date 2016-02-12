<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_AbstractPaymentGateway $gateway */ ?>

<tr valign="top">
    <td>
        <p><strong><?php _e('General settings', 'cuar'); ?></strong></p>
    </td>
</tr>
<tr valign="top">
    <td>
        <?php $option_id = $gateway->get_option_id(CUAR_AbstractPaymentGateway::$OPTION_ENABLED); ?>
        <input type="checkbox" id="<?php echo esc_attr($option_id); ?>" name="cuar_options[<?php echo esc_attr($option_id); ?>]" <?php checked(true,
            $gateway->is_enabled()); ?> /> <?php _e('Allow payments through this gateway', 'cuar'); ?>
    </td>
</tr>
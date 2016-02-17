<?php /** Template version: 3.0.0 */ ?>

<?php
$message = cuar_get_gateway_message();
?>

<div class="alert alert-primary alert-success alert-border-left mb30">
    <h3 class="mt5"><?php _e('Sorry!', 'cuar') ?></h3>
    <p><?php _e('There has been a problem when recording your payment.', 'cuar') ?></p>
    <br>
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
</div>

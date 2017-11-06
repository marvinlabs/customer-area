<?php /** Template version: 3.0.0 */ ?>

<?php
$message = cuar_get_gateway_message();
?>

<div class="alert alert-danger alert-border-left mb-lg">
    <h3 class="mt-xs mb-lg"><?php _e('Sorry!', 'cuar') ?></h3>
    <p><?php _e('There has been a problem when recording your payment.', 'cuar') ?></p>
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
</div>

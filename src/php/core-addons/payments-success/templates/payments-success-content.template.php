<?php /** Template version: 3.0.0 */ ?>

<?php
$message = cuar_get_gateway_message();
?>

<div class="alert alert-primary alert-success alert-border-left mb30">
    <h3 class="mt-xs mb-lg"><?php _e('Thank you', 'cuar') ?></h3>
    <p><?php _e('Your payment has been recorded.', 'cuar') ?></p>
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
</div>

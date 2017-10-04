<?php /** Template version: 3.0.0 */ ?>

<?php
$message = cuar_get_gateway_message();
?>

<div class="alert alert-success alert-border-left mb-lg">
    <h3 class="mt-xs mb-lg"><?php _e('Thank you', 'cuar') ?></h3>
    <p class="mb-lg"><?php _e('Your payment has been recorded.', 'cuar') ?></p>
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
</div>

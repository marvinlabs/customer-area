<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_Payment $payment */ ?>
<?php /** @var array $notes */ ?>
<?php /** @var string $item_template */ ?>

<div class="cuar-js-payment-notes-manager">
    <?php wp_nonce_field('cuar_add_payment_note', 'cuar_add_note_nonce'); ?>
    <?php wp_nonce_field('cuar_delete_payment_note', 'cuar_remove_note_nonce'); ?>

    <div class="cuar-payment-notes cuar-js-payment-notes" data-payment-id="<?php echo $payment->ID; ?>">
        <div class="cuar-payment-note cuar-clearfix cuar-js-add-note-form">
            <textarea id="cuar_new_note_message" name="cuar_new_note_message" class="cuar-js-new-note-message"></textarea>
            <button class="button cuar-js-add-note alignright" disabled="disabled"><?php _e('Add note', 'cuar'); ?></button>
        </div>

        <div class="cuar-payment-note cuar-js-empty-message" <?php if ( !empty($notes)) : ?>style="display: none;"<?php endif; ?>>
            <p class="cuar-message"><?php _e('There are currently no notes attached to this payment', 'cuar'); ?></p>
        </div>

        <?php foreach ($notes as $note) : ?><?php include($item_template); ?><?php endforeach; ?>
    </div>

    <div class="cuar-js-payment-note-template" style="display: none;">
        <?php
        $note = array(
            'id'            => '',
            'timestamp_gmt' => '',
            'author'        => '',
            'message'       => '',
        );
        include($item_template);
        ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.cuar-js-payment-notes-manager').paymentNotesManager();
    });
</script>
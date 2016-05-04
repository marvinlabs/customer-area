<?php /**
 * Template version: 1.0.0
 * Template zone: frontend
 */ ?>


<?php /** @var int $post_id */ ?>
<?php /** @var array $attached_files */ ?>
<?php /** @var string $attachment_item_template */ ?>


<?php do_action('cuar/templates/attachment-manager/before-file-attachment-list'); ?>

<table class="table cuar-file-attachments cuar-js-file-attachments">
    <?php wp_nonce_field('cuar-remove-attachment-' . $post_id, 'cuar_remove_attachment_nonce'); ?>
    <?php wp_nonce_field('cuar-update-attachment-' . $post_id, 'cuar_update_attachment_nonce'); ?>

    <?php
    foreach ($attached_files as $file_id => $attached_file)
    {
        include($attachment_item_template);
    }
    ?>
    <tr class="cuar-js-empty-message" <?php if ( !empty($attached_files)) echo 'style="display: none;"'; ?>>
        <td><p class="alert"><?php _e('This content currently does not have any file attachment', 'cuar'); ?></p></td>
    </tr>
</table>

<table class="cuar-js-file-attachment-template" style="display: none;"><?php
    $file_id = null;
    $attached_file = null;
    include($attachment_item_template);
    ?>
</table>

<?php do_action('cuar/templates/attachment-manager/after-file-attachment-list'); ?>
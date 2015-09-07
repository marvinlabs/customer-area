<?php /** Template version: 1.0.0

 */

/** @var int $post_id */
/** @var array $attached_files */
/** @var string $attachment_item_template */
?>

<?php do_action('cuar/templates/attachment-manager/before-file-attachment-list'); ?>

<div class="cuar-file-attachments">
    <?php wp_nonce_field('cuar-remove-attachment-' . $post_id, 'cuar_remove_attachment_nonce'); ?>
    <?php wp_nonce_field('cuar-update-attachment-' . $post_id, 'cuar_update_attachment_nonce'); ?>

    <?php
    foreach ($attached_files as $file_id => $attached_file)
    {
        include($attachment_item_template);
    }
    ?>
    <p class="cuar-empty-message" <?php if ( !empty($attached_files)) echo 'style="display: none;"'; ?>>
        <?php _e('This content currently does not have any file attachment', 'cuar'); ?>
    </p>
</div>

<div class="cuar-file-attachment-template" style="display: none;"><?php
    $file_id = null;
    $attached_file = null;
    include($attachment_item_template);
    ?>
</div>

<?php do_action('cuar/templates/attachment-manager/after-file-attachment-list'); ?>
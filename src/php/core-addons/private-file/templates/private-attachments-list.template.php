<?php /** Template version: 1.0.0

 */

/** @var array $attached_files */
/** @var string $attachment_item_template */
?>

<div class="cuar-file-attachments">
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
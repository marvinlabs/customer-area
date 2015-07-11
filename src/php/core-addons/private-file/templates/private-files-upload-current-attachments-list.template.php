<?php /** Template version: 1.0.0

 */

/** @var array $attached_files */
/** @var string $current_attachment_list_item_template */
?>

<div class="cuar-private-file-attachments">
    <?php foreach ($attached_files as $attached_file) : ?>
        <?php include($current_attachment_list_item_template) ?>
    <?php endforeach; ?>
</div>
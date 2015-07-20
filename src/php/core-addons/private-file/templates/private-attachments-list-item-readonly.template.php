<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
/** @var string $file_id */
/** @var array $attached_file */
?>

<div class="cuar-file-attachment <?php if ($file_id != null) echo 'cuar-state-success'; ?>">
    <?php do_action('cuar/templates/attachment-manager/before-file-attachment-caption', $post->ID, $attached_file); ?>

    <span class="cuar-caption"><?php if ($attached_file != null) cuar_the_attached_file_caption($post->ID, $attached_file); ?></span>

    <?php do_action('cuar/templates/attachment-manager/after-file-attachment-caption', $post->ID, $attached_file); ?>
</div>
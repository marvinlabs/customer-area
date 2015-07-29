<?php /** Template version: 1.0.0
 */

/** @var int $post_id */
/** @var string $file_id */
/** @var array $attached_file */
?>

<div class="cuar-file-attachment <?php if ($file_id != null) echo 'cuar-state-success'; ?>" data-post-id="<?php echo esc_attr($post_id); ?>"
     data-filename="<?php if ($attached_file != null) echo esc_attr(cuar_get_the_attached_file_name($post_id, $attached_file)); ?>">

    <?php do_action('cuar/templates/attachment-manager/before-file-attachment-caption', $post_id, $attached_file); ?>

    <span class="cuar-caption"><?php if ($attached_file != null) cuar_the_attached_file_caption($post_id, $attached_file); ?></span>

    <?php do_action('cuar/templates/attachment-manager/after-file-attachment-caption', $post_id, $attached_file); ?>

    <span class="cuar-actions">
        <?php do_action('cuar/templates/attachment-manager/file-attachment-actions', $post_id, $attached_file); ?>
        <a href="#" class="cuar-remove-action" title="<?php esc_attr_e('Remove', 'cuar'); ?>">
            <span class="dashicons dashicons-dismiss"></span></a>
    </span>
    <span class="cuar-progress" style="display: none;">
        <span class="determinate"></span>
        <span class="indeterminate"></span>
    </span>
</div>
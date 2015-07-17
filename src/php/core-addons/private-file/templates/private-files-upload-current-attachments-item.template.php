<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
/** @var string $file_id */
/** @var array $attached_file */
?>

<div class="cuar-file-attachment <?php if ($file_id != null) echo 'cuar-state-success'; ?>" data-post-id="<?php echo esc_attr($post->ID); ?>"
     data-filename="<?php if ($attached_file != null) echo esc_attr(cuar_get_the_attached_file_name($post->ID, $attached_file)); ?>">
    <span class="cuar-caption"><?php if ($attached_file != null) cuar_the_attached_file_caption($post->ID, $attached_file); ?></span>
    <span class="cuar-actions">
        <a href="#" class="cuar-remove-action" title="<?php esc_attr_e('Remove', 'cuar'); ?>">
            <span class="dashicons dashicons-dismiss"></span></a>
    </span>
    <span class="cuar-progress" style="display: none;"></span>
</div>
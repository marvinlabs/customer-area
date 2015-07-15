<?php /** Template version: 1.0.0

 */

/** @var int $file_index */
/** @var array $attached_file */
?>

<div class="cuar-private-file-attachment" data-post-id="<?php echo esc_attr($post->ID); ?>" data-file-name="<?php echo esc_attr(cuar_get_the_file_name($post->ID, $file_index)); ?>">
    <span class="cuar-caption"><?php cuar_the_file_caption($post->ID, $file_index); ?></span>
    <span class="cuar-actions">
        <a href="#" class="cuar-remove-attached-file" title="<?php esc_attr_e('Remove', 'cuar'); ?>">
            <span class="dashicons dashicons-dismiss"></span></a>
    </span>
    <span class="cuar-progress" style="display: none;"></span>
</div>
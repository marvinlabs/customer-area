<?php /** Template version: 1.0.0

 */

/** @var int $file_index */
/** @var array $attached_file */
?>

<div class="cuar-private-file-attachment">
    <span class="cuar-progress"></span>
    <span class="cuar-title"><?php cuar_the_file_caption(null, $file_index); ?></span>
    <span class="cuar-actions">
        <a href="#" class="cuar-delete-attachment" title="<?php esc_attr_e('Remove', 'cuar'); ?>"><span class="dashicons dashicons-dismiss"></span></a>
    </span>
</div>
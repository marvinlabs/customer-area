<?php /** Template version: 1.0.0

 */

/** @var array $attached_file */

var_dump($attached_file);
?>

<div class="cuar-private-file-attachment">
    <span class="cuar-title"><?php echo $attached_file['file']; ?></span>
    <span class="cuar-raw-link"><a href="<?php echo esc_attr($attached_file['file']); ?>" target="_blank"><?php _e('Link', 'cuar'); ?></a></span>
    <span class="cuar-actions">
        <a href="" class="cuar-delete-attachment"><?php _e('Remove', 'cuar'); ?></a>
    </span>
</div>
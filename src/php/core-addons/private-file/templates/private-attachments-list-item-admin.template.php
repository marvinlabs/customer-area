<?php /**
 * Template version: 3.0.0
 * Template zone: admin
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var string $file_id */ ?>
<?php /** @var array $attached_file */ ?>

<div class="cuar-file-attachment cuar-js-file-attachment <?php if ($file_id != null) echo 'cuar-js-state-success'; ?>" data-post-id="<?php echo esc_attr($post_id); ?>" data-filename="<?php if ($attached_file != null) echo esc_attr(cuar_get_the_attached_file_name($post_id, $attached_file)); ?>">

    <?php do_action('cuar/templates/attachment-manager/before-file-attachment-caption', $post_id, $attached_file); ?>

    <span class="cuar-caption cuar-js-caption"><?php if ($attached_file != null) cuar_the_attached_file_caption($post_id, $attached_file); ?></span>

    <?php do_action('cuar/templates/attachment-manager/after-file-attachment-caption', $post_id, $attached_file); ?>

    <span class="cuar-actions cuar-js-actions">
        <?php do_action('cuar/templates/attachment-manager/file-attachment-actions', $post_id, $attached_file); ?>
        <?php if ($file_id != null): ?>
        <!--
            <a href="<?php cuar_the_attached_file_link($post_id, $attached_file); ?>" class="cuar-download-action cuar-js-download-action" title="<?php esc_attr_e('Download', 'cuar'); ?>">
                <span class="dashicons dashicons-download"></span></a>
        -->
        <?php endif; ?>
        <a href="#" class="cuar-remove-action cuar-js-remove-action" title="<?php esc_attr_e('Remove', 'cuar'); ?>">
            <span class="dashicons dashicons-trash"></span></a>
    </span>
    <span class="cuar-progress cuar-js-progress" style="display: none;">
        <span class="determinate"></span>
        <span class="indeterminate"></span>
    </span>
</div>
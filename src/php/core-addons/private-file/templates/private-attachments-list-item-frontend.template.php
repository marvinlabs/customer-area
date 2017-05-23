<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var string $file_id */ ?>
<?php /** @var array $attached_file */ ?>

<tr class="cuar-js-file-attachment <?php if ($file_id != null) echo 'cuar-js-state-success'; ?>" data-post-id="<?php echo esc_attr($post_id); ?>" data-filename="<?php if ($attached_file != null) echo esc_attr(cuar_get_the_attached_file_name($post_id, $attached_file)); ?>">

    <?php do_action('cuar/templates/attachment-manager/before-file-attachment-caption', $post_id, $attached_file); ?>

    <td class="cuar-js-caption"><?php if ($attached_file != null) cuar_the_attached_file_caption($post_id, $attached_file); ?></td>

    <?php do_action('cuar/templates/attachment-manager/after-file-attachment-caption', $post_id, $attached_file); ?>

    <td class="text-right cuar-js-actions">
        <?php do_action('cuar/templates/attachment-manager/file-attachment-actions', $post_id, $attached_file); ?>
        <?php if ($file_id != null): ?>
            <!--
            <a href="<?php cuar_the_attached_file_link($post_id, $attached_file); ?>" class="btn btn-default btn-xs cuar-js-download-action" title="<?php esc_attr_e('Download', 'cuar'); ?>">
                <span class="fa fa-download"></span> <?php esc_attr_e('Download', 'cuar'); ?></a>
            -->    
        <?php endif; ?>
        <a href="#" class="btn btn-default btn-xs cuar-js-remove-action" title="<?php esc_attr_e('Remove', 'cuar'); ?>">
            <span class="fa fa-trash"></span> <?php esc_attr_e('Remove', 'cuar'); ?></a>
    </td>
    <td class="progress cuar-js-progress" style="display: none;">
        <div class="progress-bar progress-bar-primary text-center active cuar-js-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            <span class="cuar-js-progress-label">0%</span>
        </div>
    </td>
</tr>
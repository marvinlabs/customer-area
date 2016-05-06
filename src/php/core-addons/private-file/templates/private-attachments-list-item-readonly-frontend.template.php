<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var string $file_id */ ?>
<?php /** @var array $attached_file */ ?>

<div class="cuar-file-attachment cuar-js-file-attachment <?php if ($file_id != null) echo 'cuar-js-state-success'; ?>">
    <?php do_action('cuar/templates/attachment-manager/before-file-attachment-caption', $post_id, $attached_file); ?>

    <span class="cuar-caption cuar-js-caption"><?php if ($attached_file != null) cuar_the_attached_file_caption($post_id, $attached_file); ?></span>

    <?php do_action('cuar/templates/attachment-manager/after-file-attachment-caption', $post_id, $attached_file); ?>
</div>
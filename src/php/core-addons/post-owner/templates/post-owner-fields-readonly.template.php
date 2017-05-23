<?php /**
 * Template version: 3.0.0
 * Template zone: admin|frontend
 */ ?>

<?php /** @var array $owners */ ?>
<?php /** @var string $field_prefix */ ?>
<?php /** @var string $field_group */ ?>

<?php foreach ($owners as $type => $ids) : ?>
    <?php if (empty($ids)) continue; ?>
    <?php foreach ($ids as $i => $owner_id) : ?>
        <?php if (empty($owner_id)) continue; ?>
        <input type="hidden" name="<?php echo $field_prefix . $type . '[' . $i . ']'; ?>" value="<?php echo $owner_id; ?>" />
    <?php endforeach; ?>
<?php endforeach; ?>

<p class="alert alert-default">
    <i class="fa fa-info-circle mr-xs"></i> <?php _e('You are not allowed to select an owner', 'cuar'); ?>
</p>


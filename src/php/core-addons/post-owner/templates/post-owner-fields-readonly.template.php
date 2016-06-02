<?php /** @var array $owners */ ?>
<?php /** @var array $field_prefix */ ?>

<?php foreach ($owners as $type => $ids) : ?>
    <?php if (empty($ids)) continue; ?>

    <?php foreach ($ids as $i => $owner_id) : ?>
        <input type="hidden" name="<?php echo $field_prefix . $type . '[' . $i . ']'; ?>" value="<?php echo $owner_id; ?>" />
    <?php endforeach; ?>
<?php endforeach; ?>

<p class="alert alert-default">
    <i class="fa fa-info-circle mr-xs"></i>
    <?php __('You are not allowed to select an owner', 'cuar'); ?>
</p>


<?php /**
 * Template version: 3.1.0
 * Template zone: admin|frontend
 *
 *  * -= 3.1.0 =-
 * - Remove alert box to only keep hidden field
 *
 * -= 3.0.0 =-
 * - Initial version
 *
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


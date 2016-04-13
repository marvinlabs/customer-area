<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Adjustments for the new master-skin UI
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var string $field_name */ ?>
<?php /** @var string $field_label */ ?>
<?php /** @var string $field_code */ ?>
<?php /** @var string $field_help_text */ ?>

<div class="form-group row clearfix">
    <label for="<?php echo esc_attr($field_name) ?>" class="label-container col-sm-3"><?php echo $field_label; ?></label>
    <div class="control-container col-sm-9">
        <?php echo $field_code; ?>

        <?php if ( !empty($field_help_text)) : ?>
            <span class="help-block"><?php echo $field_help_text; ?></span>';
        <?php endif; ?>
    </div>
</div>

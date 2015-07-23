<?php /** Template version: 1.0.0 */ ?>

<?php /** @var string $field_name */ ?>
<?php /** @var string $field_label */ ?>
<?php /** @var string $field_code */ ?>
<?php /** @var string $field_help_text */ ?>

<div class="form-group">
    <label for="<?php echo esc_attr($field_name) ?>" class="control-label"><?php echo $field_label; ?></label>
    <div class="control-container">
        <?php echo $field_code; ?>

        <?php if ( !empty($field_help_text)) : ?>
            <span class="help-block"><?php echo $field_help_text; ?></span>';
        <?php endif; ?>
    </div>
</div>

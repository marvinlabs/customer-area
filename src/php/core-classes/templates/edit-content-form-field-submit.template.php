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

<div class="form-group mn">
    <div class="cuar-submit-container">
        <input type="submit" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($field_label); ?>" class="btn btn-default" />
    </div>
</div>

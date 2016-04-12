<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - New Wizard UI
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var array $field */ ?>

<?php
$class = implode(' ', $field['classes']);
$label = $field['label'];
$placeholder = $field['placeholder'];
$value = empty($field['value']) && $placeholder !== false ? $placeholder : $field['value'];

if (empty($value)) {
    $value_shown = $placeholder;
} else {
    if (0 != strpos($value, 'http')) $value = 'http://' . $value;

    $value_shown = str_replace('http://', '', $value);
    $value_shown = str_replace('https://', '', $value_shown);
}

if (!empty($value) || $placeholder !== false) : ?>
    <p class="cuar-field <?php echo $class; ?>">
        <span class="cuar-field-name"><?php echo $label; ?></span> <span class="cuar-field-value"><a href="<?php echo $value; ?>"><?php echo $value_shown; ?></a></span>
    </p>
<?php endif; ?>
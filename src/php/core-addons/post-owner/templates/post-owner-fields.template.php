<?php /** @var array $owner_types */ ?>
<?php /** @var array $owners */ ?>
<?php /** @var bool $print_javascript */ ?>
<?php /** @var array $field_prefix */ ?>
<?php /** @var CUAR_PostOwnerAddOn $po_addon */ ?>

<?php
foreach ($owner_types as $type_id => $type_label) :
    $selectable_owners = $po_addon->get_selectable_owners($type_id);
    if (empty($selectable_owners)) continue;

    $allow_multiple = $po_addon->is_multiple_selection_enabled($type_id);
    $field_name = $field_prefix . $type_id;
    if ($allow_multiple) $field_name .= '[]';
    ?>
    <div class="cuar-owner-field cuar-owner-field-<?php echo $type_id; ?>">
        <label for="<?php esc_attr($field_name); ?>"><?php echo $type_label; ?></label>

        <?php $extra_attrs = $po_addon->is_multiple_selection_enabled($type_id) ? ' multiple="multiple" size="8"' : ''; ?>
        <?php $css_class = 'form-control cuar-owner-select cuar-owner-select-' . $type_id; ?>
        <?php $placeholder = __('Select or search an owner', 'cuar'); ?>

        <select name="<?php echo $field_name; ?>" class="<?php echo esc_attr($css_class); ?>" data-placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo $extra_attrs; ?>>
            <option value=""></option>
            <?php foreach ($selectable_owners as $id => $name):
                $selected = isset($owners[$type_id]) && !empty($owners[$type_id]) && in_array($id, $owners[$type_id]);
                ?>
                <option value="<?php echo esc_attr($id); ?>" <?php selected($selected); ?>><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>

        <?php if ($print_javascript) : ?>
            <script type="text/javascript">
                <!--
                jQuery("document").ready(function ($) {
                    $(".cuar-owner-select-<?php echo $type_id; ?>").select2({
                        <?php if ( !is_admin()) echo "dropdownParent: $('.cuar-owner-select-" . $type_id . "').parent(),"; ?>
                        width: "100%",
                        allowClear: true
                    });
                });
                //-->
            </script>
        <?php endif; ?>
    </div>
<?php endforeach; ?>


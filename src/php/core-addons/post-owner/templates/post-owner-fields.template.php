<?php /**
 * Template version: 3.0.0
 * Template zone: admin|frontend
 */ ?>

<?php /** @var array $owner_types */ ?>
<?php /** @var array $owners */ ?>
<?php /** @var bool $print_javascript */ ?>
<?php /** @var string $field_prefix */ ?>
<?php /** @var string $field_group */ ?>
<?php /** @var CUAR_PostOwnerAddOn $po_addon */ ?>

<div class="cuar-js-owner-select-container">
<?php
$owners_are_selectable = false;
foreach ($owner_types as $type_id => $type_label) :
    $selectable_owners = $po_addon->get_selectable_owners($type_id);
    if (empty($selectable_owners)) continue;

    $allow_multiple = $po_addon->is_multiple_selection_enabled($type_id);
    $field_name = $field_prefix;
    if ($field_group != null) {
        $field_name = $field_group . '[' . $field_name . '][' . $type_id . ']';
    } else {
        $field_name .= $type_id;
    }

    $field_id = 'cuar_owner_select_' . $type_id;

    if ($allow_multiple) $field_name .= '[]';
    ?>
    <div class="cuar-owner-field cuar-owner-field-<?php echo $type_id; ?>">
        <label for="<?php esc_attr($field_name); ?>"><?php echo $type_label; ?></label>

        <?php $extra_attrs = $po_addon->is_multiple_selection_enabled($type_id) ? ' multiple="multiple" size="8"' : ''; ?>
        <?php $css_class = 'form-control cuar-owner-select cuar-js-owner-select cuar-owner-select-' . $type_id; ?>
        <?php $placeholder = __('Select or search an owner', 'cuar'); ?>

        <select id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="<?php echo esc_attr($css_class); ?>" data-owner-type="<?php echo esc_attr($type_id); ?>" data-placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo $extra_attrs; ?>>
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
                (function ($) {
                    "use strict";
                    $(document).ready(function () {

                        if (cuar.isAdmin) {
                            // Init Select 2
                            cuarInitPostOwnersSelect();
                        } else {
                            // Wait for wizard to be initialized before Select 2
                            $('#cuar-js-content-container').on('cuar:wizard:initialized', cuarInitPostOwnersSelect);
                        }

                        function cuarInitPostOwnersSelect() {
                            $("#<?php echo $field_id; ?>").select2({
                                <?php if (!is_admin()) echo "dropdownParent: $('#" . $field_id . "').parent(),"; ?>
                                width: "100%",
                                allowClear: true,
                                minimumResultsForSearch: 8
                            });
                        }
                    });
                })(jQuery);
                //-->
            </script>
        <?php endif; ?>
    </div>
    <?php $owners_are_selectable = true; ?>
<?php endforeach; ?>

<?php if (!$owners_are_selectable) : ?>
    <p class="alert alert-default">
        <i class="fa fa-info-circle mr-xs"></i>
        <?php __('You are not allowed to select an owner', 'cuar'); ?>
    </p>
<?php endif; ?>
</div>

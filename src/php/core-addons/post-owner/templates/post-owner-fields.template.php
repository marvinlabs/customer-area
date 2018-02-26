<?php /**
 * Template version: 3.0.0
 * Template zone: admin|frontend
 */ ?>

<?php /** @var array $owner_types */ ?>
<?php /** @var array $owners */ ?>
<?php /** @var bool $print_javascript */ ?>
<?php /** @var string $field_prefix */ ?>
<?php /** @var string $content_type */ ?>
<?php /** @var string $field_group */ ?>
<?php /** @var CUAR_PostOwnerAddOn $po_addon */ ?>

<?php $content_type = empty($content_type) ? "" : $content_type; ?>

<div class="cuar-js-owner-select-container">
    <?php
    $owners_are_selectable = false;
    foreach ($owner_types as $type_id => $type_label) :
        $allow_multiple = $po_addon->is_multiple_selection_enabled($type_id);
        $field_name = $field_prefix;
        if ($field_group != null)
        {
            $field_name = $field_group . '[' . $field_name . '][' . $type_id . ']';
        }
        else
        {
            $field_name .= $type_id;
        }

        $field_id = 'cuar_owner_select_' . $type_id;

        if ($allow_multiple)
        {
            $field_name .= '[]';
        }
        ?>
        <div class="cuar-owner-field cuar-owner-field-<?php echo $type_id; ?>">
            <label for="<?php esc_attr($field_id); ?>"><?php echo $type_label; ?></label>

            <?php $extra_attrs = $po_addon->is_multiple_selection_enabled($type_id) ? ' multiple="multiple" size="8"'
                : ''; ?>
            <?php $css_class = 'form-control cuar-owner-select cuar-js-owner-select cuar-owner-select-' . $type_id; ?>
            <?php $placeholder = __('Select or search an owner', 'cuar'); ?>

            <select id="<?php echo $field_id; ?>"
                    name="<?php echo $field_name; ?>"
                    class="<?php echo esc_attr($css_class); ?>"
                    data-owner-type="<?php echo esc_attr($type_id); ?>"
                    data-nonce="<?php echo wp_create_nonce('cuar_search_selectable_owner_' . $type_id); ?>"
                    data-placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo $extra_attrs; ?>>
                <?php foreach ($owners[$type_id] as $id) : ?>
                    <option value="<?php echo esc_attr($id); ?>" selected="selected"><?php
                        echo $po_addon->get_owner_display_name($type_id, $id);
                    ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php $owners_are_selectable = true; ?>
    <?php endforeach; ?>

    <?php if (!$owners_are_selectable) : ?>
        <p class="alert alert-default">
            <i class="fa fa-info-circle mr-xs"></i>
            <?php __('You are not allowed to select an owner', 'cuar'); ?>
        </p>
    <?php endif; ?>


    <?php if ($owners_are_selectable && $print_javascript) : ?>
        <script type="text/javascript">
            <!--
            (function ($)
            {
                "use strict";
                $(document).ready(function ()
                {
                    if (cuar.isAdmin) {
                        // Init Select 2
                        cuarInitPostOwnersSelect();
                    } else {
                        // Wait for wizard to be initialized before Select 2
                        $('#cuar-js-content-container').on('cuar:wizard:initialized', cuarInitPostOwnersSelect);
                    }

                    function cuarInitPostOwnersSelect()
                    {
                        $(".cuar-js-owner-select").each(function ()
                        {
                            var field = $(this);
                            var nonce = field.data('nonce');
                            var ownerType = field.data('owner-type');

                            field.select2({
                                <?php if (!is_admin()) {
                                    echo "dropdownParent: field.parent(),";
                                } ?>
                                width      : "100%",
                                allowClear : true,
                                placeholder: '',
                                ajax       : {
                                    url           : cuar.ajaxUrl,
                                    dataType      : 'json',
                                    data          : function (params)
                                    {
                                        return {
                                            search: params.term,
                                            nonce : nonce,
                                            action: 'cuar_search_selectable_owner',
                                            owner_type: ownerType,
                                            content_type: '<?php echo esc_attr($content_type); ?>',
                                            page  : params.page || 1
                                        };
                                    },
                                    processResults: function (data)
                                    {
                                        if (!data.success) {
                                            alert(data.data);
                                            return {results: []};
                                        }

                                        return {
                                            results   : data.data.results,
                                            pagination: {
                                                more: data.data.more
                                            }
                                        };
                                    }
                                }
                            });
                        })
                    }
                });
            })(jQuery);
            //-->
        </script>
    <?php endif; ?>
</div>

<?php /** Template version: 1.0.0 */ ?>

<?php
require_once(CUAR_INCLUDES_DIR . '/helpers/wordpress-helper.class.php');
$column_count = 1; // We got an empty column on the left

wp_enqueue_script('jquery-ui-tabs');
?>

<div id="sections_tabs" class="tab-container">
    <ul class="tab-wrapper">
        <?php
        foreach ($all_capability_groups as $section_id => $section) {
            $section_label = $section['label'];
            printf('<li class="nav-tab"><a href="#section_tab_%s">%s</a></li>',
                esc_attr($section_id),
                esc_html($section_label));
        }
        ?>
    </ul>

    <?php
    foreach ($all_capability_groups as $section_id => $section) : ?>

        <div id="section_tab_<?php echo esc_attr($section_id); ?>">
            <p>&nbsp;</p>

            <div style="overflow-x: scroll; overflow-y: visible;">
                <table class="widefat cuar-capabilities">
                    <thead>
                    <tr>
                        <th class="checkbox-only"></th>
                        <th></th>
                        <?php            foreach ($all_roles as $role) :
                            ++$column_count;
                            ?>
                            <th><?php echo CUAR_WordPressHelper::getRoleDisplayName($role->name); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <?php            foreach ($all_roles as $role) :
                            $readonly = $role->name == 'administrator';
                            ?>
                            <?php if ($readonly) : ?>
                            <td></td>
                        <?php else : ?>
                            <td><input type="checkbox" class="toggle_roles" data-role="<?php echo $role->name; ?>"
                                       title="<?php _e('Toggle all permissions for this role', 'cuar'); ?>"/></td>
                        <?php            endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th class="checkbox-only"></th>
                        <th></th>
                        <?php foreach ($all_roles as $role) : ?>
                            <th><?php echo CUAR_WordPressHelper::getRoleDisplayName($role->name); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    foreach ($section['groups'] as $group) :
                    $group_name = $group['group_name'];
                    $group_caps = $group['capabilities'];

                    if (empty($group_caps)) continue;
                    ?>
                    <tr>
                        <th colspan="<?php echo $column_count; ?>"><h3><?php echo $group_name; ?></h3></th>
                    </tr>
                    <?php
                    foreach ($group_caps as $cap => $cap_name) :
                        ?>
                        <tr>
                            <td class="checkbox-only"><input type="checkbox" class="toggle_caps"
                                                             data-cap="<?php echo $cap; ?>"
                                                             title="<?php _e('Toggle this permission for all roles', 'cuar'); ?>"/>
                            </td>
                            <th class="label"><?php echo $cap_name; ?></th>
                            <?php                foreach ($all_roles as $role) :
                                $id = str_replace(' ', '-', $role->name . '_' . $cap);
                                $checked = $role->has_cap($cap) ? 'checked="checked" ' : '';
                                $readonly = $role->name == 'administrator';
                                ?>
                                <td title="<?php echo esc_attr($role->name . ' &raquo; ' . $cap_name); ?>"
                                    class="value">
                                    <?php if ($readonly) : ?>
                                        <?php if (!empty($checked)) : ?>
                                            <input type="hidden" name="<?php echo esc_attr($id); ?>" value="1"/>
                                        <?php endif; ?>
                                        <input type="checkbox" name="dummy_<?php echo esc_attr($id); ?>" value="1"
                                               <?php echo $checked; ?>disabled="disabled"
                                               data-role="<?php echo $role->name; ?>" data-cap="<?php echo $cap; ?>"/>
                                    <?php else : ?>
                                        <input type="checkbox" name="<?php echo esc_attr($id); ?>"
                                               <?php echo $checked; ?>value="1" data-role="<?php echo $role->name; ?>"
                                               data-cap="<?php echo $cap; ?>"/>
                                    <?php                    endif; ?>
                                </td>
                            <?php endforeach; // Roles ?>
                        </tr>
                    <?php
                    endforeach; // Caps
                    ?>
                    </tbody>
                    <?php
                    endforeach; // Plugins
                    ?>
                </table>
            </div>
        </div>
    <?php
    endforeach; // Plugins
    ?>
</div>

<script>
    jQuery(function ($) {
        $("#sections_tabs").tabs();

        $('.toggle_roles').change(function () {
            var $toggle_box = $(this);
            var $table = $(this).parents('table');
            var targetRole = $toggle_box.data('role');
            var isChecked = $toggle_box.attr("checked");

            if (isChecked === undefined) isChecked = false;

            $table.find(':checkbox').each(function () {
                if ($(this).data('role') == targetRole) {
                    $(this).attr("checked", isChecked);
                }
            });
        });

        $('.toggle_caps').change(function () {
            var $toggle_box = $(this);
            var $table = $(this).parents('table');
            var targetCap = $toggle_box.data('cap');
            var isChecked = $toggle_box.attr("checked");

            if (isChecked === undefined) isChecked = false;

            $table.find(':checkbox').each(function () {
                if ($(this).data('cap') == targetCap && !$(this).attr('disabled')) {
                    $(this).attr("checked", isChecked);
                }
            });
        });
    });
</script>

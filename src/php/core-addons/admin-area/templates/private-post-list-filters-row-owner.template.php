<?php /** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Ajaxify field
 */ ?>

<?php
$po_addon = cuar_addon('post-owner');

$post_type = $list_table->post_type;
$visible_by_id = $list_table->get_parameter('visible-by');
$visible_by_name = empty($visible_by_id) ? '' : $po_addon->ajax()->get_user_display_value($visible_by_id, 'visible_by');
$nonce = wp_create_nonce('cuar_search_visible_by');

$is_locked = !current_user_can($post_type_object->cap->read_private_posts);
?>

<div class="cuar-filter-row">
    <label for="visible_by_select"><?php _e('Visible by', 'cuar'); ?> </label>
    <select id="visible_by_select" name="visible-by" class="postform"<?php echo $is_locked ? ' disabled="disabled"' : '' ?>>
        <?php if ($is_locked) : ?>
            <option value="<?php echo get_current_user_id(); ?>"><?php _e('Yourself', 'cuar'); ?></option>
        <?php else : ?>
            <?php if (!empty($visible_by_id)) : ?>
                <option value="<?php echo esc_attr($visible_by_id); ?>" selected="selected"><?php echo $visible_by_name; ?></option>
            <?php endif; ?>
        <?php endif; ?>
    </select>
</div>

<?php if (!$is_locked) : ?>
    <?php $po_addon->ajax()->print_field_script(
        'visible_by_select', 'cuar_search_visible_by', $nonce, array('post_type' => $post_type)
    ); ?>
<?php endif; ?>
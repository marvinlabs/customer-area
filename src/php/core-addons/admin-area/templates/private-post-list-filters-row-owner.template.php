<?php /** Template version: 1.0.0 */ ?>


<div class="cuar-filter-row">
    <?php
    $visible_by = $list_table->get_parameter('visible-by');
    $is_locked = !current_user_can($post_type_object->cap->read_private_posts);
    ?>
    <label for="visible-by"><?php _e('Visible by', 'cuar'); ?> </label>
    <select id="visible-by" name="visible-by" class="postform"<?php echo $is_locked ? ' disabled="disabled"' : '' ?>>
        <?php if ($is_locked) : ?>
            <option value="<?php echo get_current_user_id(); ?>"><?php _e('Yourself', 'cuar'); ?></option>
        <?php else : ?>
            <option value="0">-</option>
            <?php
            foreach ($all_users as $u)
            {
                $selected = selected($u->ID, $visible_by, false);
                printf('<option value="%1$s" %2$s>%3$s</option>', $u->ID, $selected, $u->display_name);
            }
            ?>
        <?php endif; ?>
    </select>
</div>
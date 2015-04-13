<?php /** Template version: 1.0.0 */ ?>


<div class="cuar-filter-row">
    <?php
    $visible_by = $list_table->get_parameter('author');
    ?>
    <label for="author"><?php _e('Created by', 'cuar'); ?> </label>
    <select id="author" name="author" class="postform"<?php echo $is_locked ? ' disabled="disabled"' : '' ?>>
        <option value="0">-</option>
        <?php
        foreach ($all_users as $u)
        {
            $selected = selected($u->ID, $visible_by, false);
            printf('<option value="%1$s" %2$s>%3$s</option>', $u->ID, $selected, $u->display_name);
        }
        ?>
    </select>
</div>
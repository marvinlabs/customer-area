<?php /** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Ajaxify field
 */ ?>

<?php
$po_addon = cuar_addon('post-owner');

$post_type = $list_table->post_type;
$author_id = $list_table->get_parameter('author');
$author_name = empty($author_id) ? '' : $po_addon->ajax()->get_user_display_value($author_id, 'author');
$nonce = wp_create_nonce('cuar_search_author');
?>

<div class="cuar-filter-row">
    <label for="author_select"><?php _e('Created by', 'cuar'); ?> </label>
    <select id="author_select" name="author" class="postform">
        <?php if (!empty($author_id)) : ?>
            <option value="<?php echo esc_attr($author_id); ?>" selected="selected"><?php echo $author_name; ?></option>
        <?php endif; ?>
    </select>

    <?php $po_addon->ajax()->print_field_script(
            'author_select', 'cuar_search_author', $nonce, array('post_type' => $post_type)
    ); ?>
</div>
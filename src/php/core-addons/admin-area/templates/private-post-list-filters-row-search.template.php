<?php /** Template version: 1.0.0 */ ?>

<div class="cuar-filter-row">
    <?php
    $search_query = $list_table->get_parameter('search-query');
    $search_field = $list_table->get_parameter('search-field');
    ?>
    <label for="search-field"><?php _e('Search', 'cuar'); ?> </label>
    <input type="text" id="search-query" name="search-query" class="postform"
           value="<?php echo esc_attr($search_query); ?>"/>

    <label for="search-field"> <?php _e('in', 'cuar'); ?> </label>
    <select id="search-field" name="search-field" class="postform">
        <option value="title" <?php selected($search_field, 'title'); ?>>
            <?php _e('Title or content', 'cuar'); ?></option>
        <option value="owner" <?php selected($search_field, 'owner'); ?>>
            <?php _e('Owner', 'cuar'); ?></option>
    </select>
</div>
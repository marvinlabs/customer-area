<?php /** Template version: 1.0.0 */ ?>

<div class="cuar-filter-row">
    <?php
    $search_query = $list_table->get_parameter('search-query');
    $search_field = $list_table->get_parameter('search-field');
    ?>
    <label for="search-field"><?php _e('Find', 'cuar'); ?> </label>
    <input type="text" id="search-query" name="search-query" class="postform"
           value="<?php echo esc_attr($search_query); ?>"/>

    <label for="search-field"> <?php _e('in title', 'cuar'); ?> </label>
    <input type=""hidden" id="search-field" name="search-field" class="postform" value="title" />

    <input type="submit" name="filter_action" id="post-query-submit" class="button cuar-filter-button"
           value="<?php echo esc_attr(sprintf(__('Search %s', 'cuar'),
               $post_type_object->labels->name)); ?>">
</div>

<div class="cuar-filter-row">
    <?php
    $start_date = $list_table->get_parameter('start-date');
    $end_date = $list_table->get_parameter('end-date');
    ?>
    <label for="start-date"><?php _e('Created between', 'cuar'); ?> </label>
    <input type="text" id="start-date" name="start-date" class="postform"
           value="<?php echo esc_attr($start_date); ?>" placeholder="dd/mm/yyyy"/>

    <label for="end-date"> <?php _e('and', 'cuar'); ?> </label>
    <input type="text" id="end-date" name="end-date" class="postform"
           value="<?php echo esc_attr($end_date); ?>" placeholder="dd/mm/yyyy"/>
</div>
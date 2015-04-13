<?php /** Template version: 1.0.0 */ ?>

<input type="submit" name="filter_action" id="post-query-submit" class="button cuar-filter-button"
       value="<?php echo esc_attr(sprintf(__('Search %s', 'cuar'),
           $post_type_object->labels->name)); ?>">
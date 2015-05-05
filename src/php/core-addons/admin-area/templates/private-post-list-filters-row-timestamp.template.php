<?php /** Template version: 1.0.0 */ ?>


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
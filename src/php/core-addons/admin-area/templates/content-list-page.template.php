<?php /** Template version: 1.0.0 */ ?>

<div class="wrap cuar-content-list">
    <h2><?php
        echo $post_type_object->labels->name;
        foreach ($title_links as $label => $url)
        {
            printf(' <a href="%2$s" class="add-new-h2">%1$s</a>', $label, $url);
        }
        ?></h2>
    <?php
    do_action('cuar/core/admin/content-list-page/after-title');
    do_action('cuar/core/admin/content-list-page/after-title?post_type=' . $post_type);
    ?>
    <form method="GET" action="<?php echo admin_url('admin.php'); ?>">
        <input type="hidden" name="referrer" value=""/>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

        <div class="cuar-list-table-filter">
            <?php do_action('cuar/core/admin/content-list-page/before-filters'); ?>

            <div class="cuar-filter-row">
                <?php
                $search_query = $list_table->get_parameter('search-query');
                $search_field = $list_table->get_parameter('search-field');
                ?>
                <label for="search-field"><?php _e('Find', 'cuar'); ?> </label>
                <input type="text" id="search-query" name="search-query" class="postform"
                       value="<?php echo esc_attr($search_query); ?>"/>

                <label for="search-field"> <?php _e('in', 'cuar'); ?> </label>
                <select id="search-field" name="search-field" class="postform">
                    <option value="title" <?php selected($search_field, 'title'); ?>>
                        <?php _e('Title', 'cuar'); ?></option>
                    <option value="owner" <?php selected($search_field, 'owner'); ?>>
                        <?php _e('Owner', 'cuar'); ?></option>
                </select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button cuar-filter-button"
                       value="<?php echo esc_attr(sprintf(__('Search %s', 'cuar'),
                           $post_type_object->labels->name)); ?>">
            </div>

            <div class="cuar-filter-row">
                <?php
                $visible_by = $list_table->get_parameter('visible-by');
                ?>
                <label for="visible-by"><?php _e('Visible by', 'cuar'); ?> </label>
                <select id="visible-by" name="visible-by" class="postform">
                    <option value="0"><?php __('Not set', 'cuar'); ?></option>

                    <?php
                    $all_users = get_users(array('orderby' => 'display_name', 'fields' => 'all_with_meta'));
                    foreach ($all_users as $u)
                    {
                        $selected = selected($u->ID, $visible_by, false);
                        printf('<option value="%1$s" %2$s>%3$s</option>', $u->ID, $selected, $u->display_name);
                    }
                    ?>
                </select>
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

            <?php do_action('cuar/core/admin/content-list-page/after-filters'); ?>
        </div>

        <?php $list_table->views(); ?>

        <?php do_action('cuar/core/admin/content-list-page/before-table'); ?>
        <?php do_action('cuar/core/admin/content-list-page/before-table?post_type=' . $post_type); ?>

        <?php $list_table->display(); ?>

        <?php do_action('cuar/core/admin/content-list-page/after-table?post_type=' . $post_type); ?>
        <?php do_action('cuar/core/admin/content-list-page/after-table'); ?>
    </form>
</div>
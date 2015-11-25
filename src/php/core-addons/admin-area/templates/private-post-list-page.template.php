<?php /** Template version: 1.0.0
 *
 * -=1.0.1=-
 * - change heading for WP 4.4
 *
 */ ?>

<div class="wrap cuar-private-post-list-page cuar-list-table-page">
    <h1><?php
        echo $post_type_object->labels->name;
        foreach ($title_links as $label => $url)
        {
            printf(' <a href="%2$s" class="add-new-h2 page-title-action">%1$s</a>', $label, $url);
        }
        ?></h1>

    <h2 class="screen-reader-text"><?php printf(__('Filter %1$s List', 'cuar'), $post_type_object->labels->name); ?></h2>

    <?php
    do_action('cuar/core/admin/' . $private_type_group . '-list-page/after-title');
    do_action('cuar/core/admin/' . $private_type_group . '-list-page/after-title?post_type=' . $post_type);
    ?>
    <form method="GET" action="<?php echo admin_url('admin.php'); ?>">
        <input type="hidden" name="referrer" value="<?php echo admin_url('admin.php?post_type=' . $post_type); ?>"/>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

        <?php $list_table->views(); ?>
        <br/>
        <div class="cuar-list-table-filter">
            <a class="cuar-filter-toggle"><?php _e('Toggle advanced filters', 'cuar'); ?></a>

            <?php $collapse_panel = $list_table->is_search_active() ? '' : 'display: none;'; ?>
            <div class="cuar-filter-panel" style="<?php echo $collapse_panel; ?>">
                <?php do_action('cuar/core/admin/' . $private_type_group . '-list-page/before-filters'); ?>

                <?php include($default_filter_template); ?>

                <?php do_action('cuar/core/admin/' . $private_type_group . '-list-page/after-filters'); ?>
            </div>
        </div>

        <?php do_action('cuar/core/admin/' . $private_type_group . '-list-page/before-table'); ?>
        <?php do_action('cuar/core/admin/' . $private_type_group . '-list-page/before-table?post_type='
            . $post_type); ?>

        <?php $list_table->display(); ?>

        <?php do_action('cuar/core/admin/' . $private_type_group . '-list-page/after-table?post_type=' . $post_type); ?>
        <?php do_action('cuar/core/admin/' . $private_type_group . '-list-page/after-table'); ?>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var filterBox = $(".cuar-list-table-filter");
        var filterToggle = filterBox.find('.cuar-filter-toggle');
        var filterPanel = filterBox.find('.cuar-filter-panel');

        filterToggle.click(function (e) {
            filterPanel.slideToggle();
            e.preventDefault();
        });

        $("#delete_all").click(function(event) {
            if (!confirm("<?php esc_attr_e('Are you sure?', 'cuar'); ?>")) {
                event.preventDefault();
            }
        });
    });
</script>
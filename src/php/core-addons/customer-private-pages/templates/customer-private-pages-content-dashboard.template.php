<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-content-block cuar-private-pages panel">
    <div class="panel-heading">
        <?php
        $pp_addon = $this->plugin->get_addon('customer-private-pages');
        $page_id = $pp_addon->get_page_id($this->get_slug());
        ?>
        <span class="panel-icon">
            <i class="fa fa-book"></i>
        </span>
        <span class="panel-title">
            <?php echo $page_subtitle; ?>
            <span class="widget-menu pull-right">
                <a href="<?php echo get_permalink($page_id); ?>" class="btn btn-default btn-xs">
                    <span class="fa fa-eye"></span> <?php _e('View all', 'cuar'); ?>
                </a>
            </span>
        </span>
    </div>
    <div class="cuar-private-pages-list cuar-item-list panel-body">
        <table class="table">
            <thead>
            <tr class="">
                <th><?php _e('Title', 'cuar'); ?></th>
                <th><?php _e('Owners', 'cuar'); ?></th>
            </tr>
            </thead>

            <tbody>
            <?php
            while ($content_query->have_posts()) {
                $content_query->the_post();
                global $post;

                include($item_template);
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
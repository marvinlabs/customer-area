<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-content-block cuar-private-pages cuar-empty cuar-empty-private-pages-category panel">
    <div class="panel-heading">
        <?php
        $pp_addon = $this->plugin->get_addon('customer-private-pages');
        $page_id = $pp_addon->get_page_id($this->get_slug());
        ?>
        <span class="panel-icon">
            <i class="fa fa-book"></i>
        </span>
        <span class="cuar-title panel-title">
            <a href="<?php echo get_permalink($page_id); ?>" title="<?php esc_attr_e('View all', 'cuar'); ?>">
                <?php echo $page_subtitle; ?>
            </a>
        </span>
    </div>
    <div class="cuar-private-pages-list cuar-item-list panel-body">
        <p><?php _e( 'There are no pages in that category.', 'cuar' ); ?></p>
    </div>
</div>
<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.1.0 =-
 * - Updated markup
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-content-block cuar-private-files panel">
    <div class="panel-heading">
        <?php
        global $cpf_addon;
        if (!$cpf_addon)
            $cpf_addon = $this->plugin->get_addon('customer-private-files');
        $page_id = $cpf_addon->get_page_id($this->get_slug());
        ?>
        <span class="panel-icon">
            <i class="fa fa-file"></i>
        </span>
        <span class="cuar-title panel-title">
            <a href="<?php echo get_permalink($page_id); ?>" title="<?php esc_attr_e('View all', 'cuar'); ?>">
                <?php echo $page_subtitle; ?>
            </a>
        </span>
    </div>
    <div class="cuar-private-file-list cuar-item-list panel-body cuar-gallery-page">

        <div id="mix-container">

            <div class="fail-message">
                <span>No items were found matching the selected filters</span>
            </div>

            <?php
            while ($content_query->have_posts()) {
                $content_query->the_post();
                global $post;

                include($item_template);
            }
            ?>
            <div class="gap"></div>
            <div class="gap"></div>
            <div class="gap"></div>
            <div class="gap"></div>

        </div>

    </div>
</div>
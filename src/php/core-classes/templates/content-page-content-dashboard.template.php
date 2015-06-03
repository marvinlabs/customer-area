<?php
/** Template version: 1.1.0
 *
 * -= 1.1.0 =-
 * - Updated markup
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-content-block panel">
    <div class="panel-heading">
        <span class="cuar-title panel-title">
            <?php echo $page_subtitle; ?>
        </span>
    </div>
    <div class="cuar-item-list panel-body">
        <?php
        while ($content_query->have_posts()) {
            $content_query->the_post();
            global $post;

            include($item_template);
        }
        ?>
    </div>
</div>
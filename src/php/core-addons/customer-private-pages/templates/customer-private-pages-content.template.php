<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="collection cuar_private_page">
    <div id="cuar-js-collection-gallery" class="collection-content" data-type="cuar_private_page">
        <div class="fail-message alert alert-warning">
            <?php _e('No items were found matching the selected filters', 'cuar'); ?>
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
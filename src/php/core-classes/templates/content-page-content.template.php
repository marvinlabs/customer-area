<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Default content template using collections
 *
 * -= 1.1.0 =-
 * - Updated markup
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var WP_Query $content_query */ ?>
<?php /** @var string $item_template */ ?>

<div class="collection cuar-page-content-default">
    <div id="cuar-js-collection-gallery" class="collection-content" data-type="collection-default">
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
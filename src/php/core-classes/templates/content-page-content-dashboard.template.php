<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Default content template for dashboard using tables
 *
 * -= 1.1.0 =-
 * - Updated markup
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var string $page_subtitle */ ?>
<?php /** @var string $all_items_url */ ?>
<?php /** @var WP_Query $content_query */ ?>
<?php /** @var string $item_template */ ?>

<div class="panel top cuar-page-content-dashboard-default">
    <div class="panel-heading">
        <span class="panel-title">
            <?php echo $page_subtitle; ?>
        </span>
    </div>
    <div class="panel-body pn">
        <table class="table table-hover table-striped">
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
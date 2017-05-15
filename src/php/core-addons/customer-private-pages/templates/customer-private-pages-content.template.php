<?php
/** Template version: 3.1.0
 *
 * -= 3.1.0 =-
 * - Added collection title
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var WP_Query $content_query */ ?>
<?php /** @var string $item_template */ ?>
<?php /** @var string $page_subtitle */ ?>
<?php /** @var int $current_page */ ?>

<?php
$current_addon_slug = 'customer-private-pages';
$current_addon_icon = apply_filters('cuar/private-content/view/icon?addon=' . $current_addon_slug, 'fa fa-book');
$current_addon = cuar_addon($current_addon_slug);
$post_type = $current_addon->get_friendly_post_type();
?>

<div class="cuar-collection-title page-heading">
    <div class="cuar-title h2">
        <small class="pull-right">
            <?php echo wp_sprintf(__('Page %1$s/%2$s', 'cuar'), $current_page, $content_query->max_num_pages); ?>
        </small>
        <?php echo $page_subtitle; ?>
    </div>
</div>

<div class="collection <?php echo $post_type; ?>">
    <div id="cuar-js-collection-gallery" class="collection-content" data-type="<?php echo $post_type; ?>">
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
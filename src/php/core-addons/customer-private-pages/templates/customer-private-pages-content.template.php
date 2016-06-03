<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var WP_Query $content_query */ ?>
<?php /** @var string $item_template */ ?>

<?php
$current_addon_slug = 'customer-private-pages';
$current_addon_icon = apply_filters('cuar/private-content/view/icon?addon=' . $current_addon_slug, 'fa fa-book');
$current_addon = cuar_addon($current_addon_slug);
$post_type = $current_addon->get_friendly_post_type();
?>

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
<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php /** @var string $page_subtitle */ ?>

<?php
$current_addon_slug = 'customer-private-files';
$current_addon_icon = apply_filters('cuar/private-content/view/icon?addon=' . $current_addon_slug, 'fa fa-file');
$current_addon = cuar_addon($current_addon_slug);
$post_type = $current_addon->get_friendly_post_type();
?>

<div class="collection panel cuar-empty cuar-empty-category <?php echo $post_type; ?>">
    <div class="panel-heading">
        <span class="panel-icon">
            <i class="<?php echo $current_addon_icon; ?>"></i>
        </span>
        <span class="panel-title">
            <?php echo $page_subtitle; ?>
        </span>
    </div>
    <div class="collection-content panel-body">
        <p class="alert alert-info mn"><?php _e( 'There are no files in that category.', 'cuar' ); ?></p>
    </div>
</div>
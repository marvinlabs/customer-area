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

<?php /** @var string $page_subtitle */ ?>

<?php
$current_addon_slug = 'customer-private-pages';
$current_addon_icon = apply_filters('cuar/private-content/view/icon?addon=' . $current_addon_slug, 'fa fa-book');
$current_addon = cuar_addon($current_addon_slug);
$post_type = $current_addon->get_friendly_post_type();
?>

<div class="panel top cuar-empty <?php echo $post_type; ?>">
    <div class="panel-heading">
        <span class="panel-icon">
            <i class="<?php echo $current_addon_icon; ?>"></i>
        </span>
        <span class="panel-title">
            <?php echo $page_subtitle; ?>
        </span>
    </div>
    <div class="panel-body">
        <p class="mn"><?php _e( 'You currently have no pages.', 'cuar' ); ?></p>
    </div>
</div>
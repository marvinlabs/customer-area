<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var string $page_subtitle */ ?>
<?php /** @var WP_Query $content_query */ ?>
<?php /** @var string $item_template */ ?>

<?php
$current_addon_slug = 'customer-private-files';
$current_addon_icon = apply_filters('cuar/private-content/view/icon?addon=' . $current_addon_slug, 'fa fa-file');
$current_addon = cuar_addon($current_addon_slug);
$post_type = $current_addon->get_friendly_post_type();
?>

<div class="panel top <?php echo $post_type; ?>">
    <div class="panel-heading">
        <span class="panel-icon">
            <i class="<?php echo $current_addon_icon; ?>"></i>
        </span>
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
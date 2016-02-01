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
<?php /** @var string $all_items_url */ ?>
<?php /** @var WP_Query $content_query */ ?>
<?php /** @var string $item_template */ ?>

<?php
$all_items_url = cuar_addon('customer-private-pages')->get_page_url('customer-private-pages');
?>

<div class="panel panel-border panel-default top cuar_private_page">
    <div class="panel-heading">
        <span class="panel-icon">
            <i class="fa fa-book"></i>
        </span>
        <span class="panel-title">
            <?php echo $page_subtitle; ?>
            <span class="widget-menu pull-right">
                <a href="<?php echo esc_attr($all_items_url); ?>" class="btn btn-default btn-xs">
                    <span class="fa fa-eye"></span> <?php _e('View all', 'cuar'); ?>
                </a>
            </span>
        </span>
    </div>
    <div class="panel-body pn">
        <table class="table">
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
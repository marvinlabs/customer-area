<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.1.0 =-
 * - Updated markup
 * - Normalized the extra class filter name
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
global $post;

$is_author = get_the_author_meta('ID') == get_current_user_id();

if ($is_author) {
    $subtitle_popup = __('You published this page', 'cuar');
    $subtitle = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
} else {
    $subtitle_popup = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
    $subtitle = sprintf(__('Published by %s', 'cuar'), get_the_author_meta('display_name'));
}

$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());

$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post);
?>

<div class="cuar-collection-item of-h mix<?php echo $extra_class; ?>">
    <?php if (has_post_thumbnail()) {
        the_post_thumbnail('wpca-thumb', array('class' => 'cuar-collection-thumbnail va-m img-responsive text-center bg-primary light table-layout'));
    } else { ?>
        <div class="cuar-collection-thumbnail img-responsive bg-primary light table-layout">
            <div class="cuar-collection-thumbnail-padder"></div>
            <div class="cuar-collection-thumbnail-icon fa fa-picture-o text-primary dark icon-bg"></div>
        </div>
    <?php } ?>

    <div class="cuar-collection-description va-m">
        <h5 class="cuar-collection-title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?> <span class="small">(<?php echo $title_popup; ?>)</span>
            </a>
        </h5>
        <h6 class="cuar-collection-subtitle">
                <?php echo $subtitle; ?> <span class="small">(<?php echo $subtitle_popup; ?>)</span>
        </h6>
        <p class="cuar-collection-excerpt"><?php echo get_the_excerpt(); ?></p>
    </div>
</div>
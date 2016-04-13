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
    $published = sprintf(__('Published on %s, by yourself, for %s', 'cuar'), get_the_date(), cuar_get_the_owner());
} else {
    $published = sprintf(__('Published on %s, by %s, for %s', 'cuar'), get_the_date(), get_the_author_meta('display_name'), cuar_get_the_owner());
}

$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post);
?>

<div class="collection-item of-h mix<?php echo $extra_class; ?>">
    <?php if (has_post_thumbnail()) { ?>
        <a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail('wpca-thumb', array('class' => 'collection-thumbnail va-m img-responsive text-center bg-primary light table-layout')); ?>
        </a>
    <?php } else { ?>
        <a href="<?php the_permalink(); ?>" class="collection-thumbnail img-responsive bg-primary light table-layout">
            <div class="collection-thumbnail-padder"></div>
        </a>
    <?php } ?>

    <div class="collection-description va-m">
        <h4 class="collection-title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h4>
        <h5 class="collection-subtitle text-muted">
            <?php echo $published; ?>
        </h5>
        <p class="collection-excerpt"><?php echo get_the_excerpt(); ?></p>
    </div>
</div>
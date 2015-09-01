<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.2.0 =-
 * - Compatibility with the new multiple attached files
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

$files = cuar_get_the_attached_files($post->ID);
$files_data = array();
if ($files && is_array($files)) {
    foreach ($files as $file) {
        $files_data[] = array(
            'size' => cuar_get_the_attached_file_size($post->ID, $file),
            'type' => cuar_get_the_attached_file_type($post->ID, $file)
        );
    }
}

$is_author = get_the_author_meta('ID') == get_current_user_id();
if ($is_author) {
    $subtitle_popup = __('You uploaded this file', 'cuar');
    $subtitle = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
} else {
    $subtitle_popup = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
    $subtitle = sprintf(__('Published by %s', 'cuar'), get_the_author_meta('display_name'));
}

$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());

$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post);

$file_count = cuar_get_the_attached_file_count($post->ID);
?>

<div class="cuar-content-block cuar-private-files cuar-item cuar-item-large<?php echo $extra_class; ?>">
    <div class="cuar-title">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>"><?php the_title(); ?></a>
    </div>

    <div class="cuar-subtitle">
        <a href="<?php the_permalink(); ?>"
           title="<?php echo esc_attr($subtitle_popup); ?>"><?php echo $subtitle; ?></a>
    </div>

    <?php if (has_post_thumbnail(get_the_ID())) : ?>
        <div class="cuar-cover">
            <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>">
                <?php the_post_thumbnail('medium', array('class' => "cuar-img-responsive")); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="cuar-badges">
        <a href="<?php the_permalink(); ?>"
           title="<?php echo esc_attr(sprintf(_n('%1$s file attached', '%1$s files attached', $file_count, 'cuar'), $file_count)); ?>">
            <span class="download-badge dashicons dashicons-download dashicon-badge pull-right">
                <?php echo $file_count; ?>
            </span>
        </a>
    </div>
</div>
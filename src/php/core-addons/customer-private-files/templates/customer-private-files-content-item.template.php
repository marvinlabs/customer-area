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

$extensions = apply_filters('cuar/templates/list-item/extensions-icons?post-type=' . get_post_type(), array(
    'none' => 'fa fa-genderless',
    'default' => 'fa fa-file-o',
    'multiple' => 'fa fa-files-o',
    'jpg' => 'fa fa-file-picture-o',
    'png' => 'fa fa-file-picture-o',
    'zip' => 'fa fa-file-zip-o'
), $post);

$file_count = cuar_get_the_attached_file_count($post->ID);
$files = cuar_get_the_attached_files($post->ID);
$files_data = array();
if ($files && is_array($files) && $file_count >= 1) {
    if ($file_count > 1) {
        $files_icon = 'multiple';
    } else {
        if ($file_count > 1 && isset($extensions[cuar_get_the_attached_file_type($post->ID, $files)])) {
            $files_icon = $extensions[cuar_get_the_attached_file_type($post->ID, $files)];
        } else if ($file_count == 1 && isset($files[0]) && isset(array_values($files)[0]) && isset($extensions[cuar_get_the_attached_file_type($post->ID, array_values($files[0]))])) {
            $files_icon = $extensions[cuar_get_the_attached_file_type($post->ID, array_values($files)[0])];
        } else {
            $files_icon = 'default';
        }
    }
} else {
    $files_icon = 'none';
}

$is_author = get_the_author_meta('ID') == get_current_user_id();

if ($is_author) {
    $published = sprintf(__('Published on %s, by yourself, for %s', 'cuar'), get_the_date(), cuar_get_the_owner());
} else {
    $published = sprintf(__('Published on %s, by %s, for %s', 'cuar'), get_the_date(), get_the_author_meta('display_name'), cuar_get_the_owner());
}

$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post);

$file_count = cuar_get_the_attached_file_count($post->ID);
?>

<div class="collection-item of-h mix<?php echo $extra_class; ?>">
    <?php if (has_post_thumbnail()) { ?>
        <a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail('wpca-thumb', array('class' => 'collection-thumbnail va-m img-responsive text-center bg-primary light table-layout')); ?>
        </a>
    <?php } else { ?>
        <a href="<?php the_permalink(); ?>" class="collection-thumbnail img-responsive bg-primary light table-layout">
            <div class="collection-thumbnail-padder">
                <div style="position: absolute; width: 100%; height: 100%; display: inline-block; vertical-align: middle; text-align: center;">
                    <i class="<?php echo $extensions[$files_icon]; ?> mt30 mr5 text-primary dark icon-bg"></i>
                    <h5 class="mn text-center fs20" style="position: relative; top: 50%; margin-top: -10px!important;">
                        <?php
                        if ($file_count == 0) {
                            _e('no file', 'cuar');
                        } else if ($file_count > 1) {
                            _e(sprintf(_n('%1$s file', '%1$s files', $file_count, 'cuar'), $file_count));
                        } else {
                            cuar_the_attached_file_type($post->ID, array_values($files)[0]);
                        }
                        ?>
                    </h5>
                </div>
            </div>
        </a>
    <?php } ?>

    <div class="collection-description va-m">
        <div class="cuar-badges pull-right">
            <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(sprintf(_n('%1$s file attached', '%1$s files attached', $file_count, 'cuar'), $file_count)); ?>">
                <span class="cuar-download-badge fa fa-download small pull-right">
                    <?php echo $file_count; ?>
                </span>
            </a>
        </div>
        <div class="collection-title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </div>
        <div class="collection-subtitle text-muted">
            <?php echo $published; ?>
        </div>

        <p class="collection-excerpt"><?php echo get_the_excerpt(); ?></p>
    </div>
</div>
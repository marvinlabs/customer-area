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

$is_author = get_the_author_meta('ID') == get_current_user_id();
if ($is_author)
{
    $subtitle_popup = __('You uploaded this file', 'cuar');
    $subtitle = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
}
else
{
    $subtitle_popup = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
    $subtitle = sprintf(__('Published by %s', 'cuar'), get_the_author_meta('display_name'));
}

$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());

$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post);

// TODO TLARTAUD PLUS POSSIBLE AVEC LES MULTIPLES FICHIERS ATTACHES
$extensions = apply_filters('cuar/templates/list-item/extensions-icons?post-type=' . get_post_type(), array(
    'default' => 'fa-file-o',
    'jpg'     => 'fa fa-file-picture-o',
    'png'     => 'fa fa-file-picture-o',
    'zip'     => 'fa fa-file-zip-o'
), $post);

$file_count = cuar_get_the_attached_file_count($post->ID);
?>

<div class="cuar-private-file cuar-item cuar-item-wide<?php echo $extra_class; ?> mix label1 folder1">

    <div class="panel p6 bg-light dark">
        <div class="of-h">
            <?php
            if (has_post_thumbnail(get_the_ID()))
            {
                the_post_thumbnail('medium');
            }
            else
            {
                // TODO TLARTAUD FAUT TROUVER AUTRE CHOSE (FICHIERS MULTIPLES POTENTIELLEMENT) + JOB DU ADD-ON ENHANCED FILES
                ?>
                <div class="va-m fs40 text-center bg-info light" style="display: inline-block; height: 200px; width: 100%;">
                    <!-- <i class="<?php if (isset($extensions[cuar_get_the_file_type(get_the_ID())]))
                    {
                        echo $extensions[cuar_get_the_file_type(get_the_ID())];
                    }
                    else echo $extensions['default']; ?> mt30 mr30 text-info dark icon-bg"></i>
                    <h6 class="text-white mn fs100" style="margin-top: 20%!important;z-index: 2;position: relative;">
                        <?php cuar_the_file_type(get_the_ID()); ?>
                    </h6> -->
                    TODO TLARTAUD FAUT TROUVER AUTRE CHOSE (FICHIERS MULTIPLES POTENTIELLEMENT)
                </div>
                <?php
            } ?>
            <div class="row table-layout">
                <div class="cuar-title col-xs-8 va-m pln">
                    <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>">
                        <h6><?php the_title(); ?></h6>
                    </a>

                    <div class="cuar-subtitle">
                        <a href="<?php the_permalink(); ?>"
                           title="<?php echo esc_attr($subtitle_popup); ?>"><?php echo $subtitle; ?></a>
                    </div>

                    <div class="cuar-badges">
                        <a href="<?php the_permalink(); ?>"
                           title="<?php echo esc_attr(sprintf(_n('%1$s file attached', '%1$s files attached', $file_count, 'cuar'), $file_count)); ?>">
                            <span class="cuar-download-badge cuar-dashicons cuar-dashicons-download cuar-dashicon-badge cuar-small cuar-pull-right"></span>
                        </a>
                    </div>
                </div>
                <div class="col-xs-4 text-right va-m prn">
                    <span class="fa fa-eye-slash fs12 text-muted"></span>
                    <span class="fa fa-circle fs10 text-info ml10"></span>
                </div>
            </div>
        </div>
    </div>
</div>
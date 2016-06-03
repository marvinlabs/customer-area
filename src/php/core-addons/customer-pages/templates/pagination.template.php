<?php
/** Template version: 2.0.0
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.1.0 =-
 * - Handle the spacers when too many pages
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<ul class="pagination pagination-sm">
    <?php
    foreach ($page_links as $num => $page_args) :
        if ($page_args['is_current']) :
            ?>
            <li class="active"><span><?php echo $num; ?> <span
                        class="sr-only"><?php _e('(current)', 'cuar'); ?></span></span>
            </li>
        <?php
        elseif (false === $page_args['link']): ?>
            <li><a href="#">&hellip;</a></li>
        <?php
        else: ?>
            <li><a href="<?php echo esc_attr($page_args['link']); ?>"
                   title="<?php printf(esc_attr__('Page %1$s', 'cuar'), $num); ?>"><?php echo $num; ?></a></li>
        <?php
        endif; ?>
    <?php
    endforeach; ?>
</ul>
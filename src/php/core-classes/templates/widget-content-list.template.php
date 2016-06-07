<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Bootstrap support
 *
 * -= 1.0.0 =-
 * - First template version
 *
 */ ?>

<?php
/** @var $posts */ ?>

<div class="cuar-widget-content-list">
    <ul class="list-group">
        <?php
        foreach ($posts as $post) :
            $link = get_permalink($post);
            $title = get_the_title($post);
            ?>
            <li><?php
                // Print the current term
                printf('<a href="%1$s" title="%3$s" class="list-group-item">%2$s</a>',
                    $link,
                    $title,
                    sprintf(esc_attr__('Link to %s', 'cuar'), $title)
                );
                ?>
            </li>
            <?php
        endforeach;
        ?>
    </ul>
</div>
<?php /** Template version: 1.0.0
 *
 * -= 1.0.0 =-
 * - First template version

 */ ?>

<ul>
    <?php
    foreach ($posts as $post) :
        $link = get_permalink($post);
        $title = get_the_title($post);
        ?>
        <li><?php
            // Print the current term
            printf('<a href="%1$s" title="%3$s">%2$s</a>',
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
<?php /** Template version: 1.0.0
 *
 * -= 1.0.0 =-
 * - First template version

 */ ?>

<ul>
    <?php
    foreach ($authors as $id => $display_name) :
        $link = $this->get_link($id);
        ?>
        <li><?php
            // Print the current author
            printf('<a href="%1$s" title="%3$s">%2$s</a>',
                $link,
                $display_name,
                sprintf(esc_attr__('Show all content published by %s', 'cuar'), $display_name)
            );
            ?>
        </li>
    <?php
    endforeach;
    ?>
</ul>
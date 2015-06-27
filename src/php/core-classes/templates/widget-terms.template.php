<?php /** Template version: 1.0.0
 *
 * -= 1.0.0 =-
 * - First template version

 */ ?>

<ul>
    <?php
    foreach ($terms as $term) :
        $link = $this->get_link($term);
        ?>
        <li><?php
            // Print the current term
            $count = "";
            if($show_count){   // only bother if we're showing them
                $objects_in_term = get_objects_in_term( $term->term_id, $this->get_taxonomy());
                $object_count = count($objects_in_term);
                $fmt_string = '<a href="%1$s" title="%4$s">%2$s (%3$s)</a>';
            }
            else{
                $fmt_string = '<a href="%1$s" title="%4$s">%2$s</a>';
            }

            printf( $fmt_string,
                    $link,
                    $term->name,
                    $object_count,
                    sprintf(esc_attr__('Show all content categorized under %s', 'cuar'), $term->name)
            );
            ?>

            <?php
            // Print all child terms in a sublist
            $children = get_terms($this->get_taxonomy(), array(
                'parent'     => $term->term_id,
                'hide_empty' => $hide_empty
            ));
            $count = count($children);
            if ($count > 0)
            {
                $this->print_term_list($children, $hide_empty, $show_count);
            }
            ?>
        </li>
    <?php
    endforeach;
    ?>
</ul>

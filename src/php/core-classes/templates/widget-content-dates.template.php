<?php /** Template version: 1.0.0
 *
 * -= 1.0.0 =-
 * - First template version

 */ ?>

<ul>
    <?php
    foreach ($dates as $year => $months) :
        $link = $this->get_link($year);
        ?>
        <li><?php
            // Print the year
            printf('<a href="%1$s" title="%3$s">%2$s</a>',
                $link,
                $year,
                sprintf(esc_attr__('Show all content published in %s', 'cuar'), $year)
            );

            if (count($months) > 0) :
                ?>
                <ul>
                    <?php
                    foreach ($months as $month) :
                        $link = $this->get_link($year, $month);
                        $month_name = date_i18n("F", mktime(0, 0, 0, $month, 10));
                        ?>
                        <li>
                            <?php
                            // Print the month
                            printf('<a href="%1$s" title="%3$s">%2$s</a>',
                                $link,
                                $month_name,
                                sprintf(esc_attr__('Show all content published in %2$s %1$s', 'cuar'), $year,
                                    $month_name)
                            );
                            ?>
                        </li>
                    <?php
                    endforeach;
                    ?>
                </ul>
            <?php
            endif;
            ?>
        </li>
    <?php
    endforeach;
    ?>
</ul>
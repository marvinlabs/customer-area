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
/** @var $dates */ ?>

<div id="cuar-content-dates-<?php echo $this->id_base; ?>" class="cuar-tree cuar-widget-content-dates panel-body br-n pn">

    <ul class="cuar-tree-depth-0">
        <?php
        foreach ($dates as $year => $months) :
            $link = $this->get_link($year);
            $extra_class = (count($months) > 0) ? ' class="folder"' : '';

            echo '<li' . $extra_class . '>';

            // Print the year
            printf('<a href="%1$s" title="%3$s" target="_self">%2$s</a>',
                $link,
                $year,
                sprintf(esc_attr__('Show all content published in %s', 'cuar'), $year)
            );

            if (count($months) > 0) :
                ?>
                <ul class="cuar-tree-depth-1">
                    <?php
                    foreach ($months as $month) :
                        $link = $this->get_link($year, $month);
                        $month_name = date_i18n("F", mktime(0, 0, 0, $month, 10));
                        ?>
                        <li>
                            <?php
                            // Print the month
                            printf('<a href="%1$s" title="%3$s" target="_self">%2$s</a>',
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

            echo '</li>';

        endforeach; ?>
    </ul>
</div>
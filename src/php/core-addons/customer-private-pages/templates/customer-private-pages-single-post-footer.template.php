<?php
/** Template version: 2.0.0
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.1.0 =-
 * - Updated to new responsive markup
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
global $post;
$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/single-post/footer/extra-class?post-type=' . get_post_type(), $extra_class, $post);

$date = sprintf("<em>%s</em>", get_the_date());
$author = sprintf("<em>%s</em>", get_the_author_meta('display_name'));
$recipients = sprintf("<em>%s</em>", cuar_get_the_owner());
?>


<div class="cuar-single-post-footer cuar-page<?php echo $extra_class; ?>">

    <div class="cuar-meta-category cuar-content-meta">
        <div class="cuar-row">
            <div class="cuar-meta-block cuar-author">
                <h4><span class="cuar-dashicons cuar-dashicons-businessman"></span> <?php _e('Author', 'cuar'); ?></h4>

                <p><?php echo $author; ?></p>
            </div>

            <div class="cuar-meta-block cuar-owner">
                <h4><span class="cuar-dashicons cuar-dashicons-groups"></span> <?php _e('Recipient', 'cuar'); ?></h4>

                <p><?php echo $recipients; ?></p>
            </div>

            <div class="cuar-meta-block cuar-date">
                <h4><span class="cuar-dashicons cuar-dashicons-calendar"></span> <?php _e('Date', 'cuar'); ?></h4>

                <p><?php echo $date; ?></p>
            </div>
        </div>
    </div>

</div>
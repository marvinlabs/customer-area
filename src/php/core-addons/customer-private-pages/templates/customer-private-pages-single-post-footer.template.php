<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
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


<div class="cuar-single-post-footer row br-t pt20 mt5<?php echo $extra_class; ?>">

    <div class="cuar-meta-category cuar-content-meta clearfix">

        <div class="cuar-meta-block cuar-author col-sm-4">
            <div class="panel panel-tile text-primary br-b bw5 br-primary-light">
                <div class="panel-body pn pl20 p5">
                    <i class="fa fa-user icon-bg p5"></i>
                    <h4 class="mt15 lh15">
                        <b><?php echo $author; ?></b>
                    </h4>
                    <h5 class="text-muted"><?php _e('Author', 'cuar'); ?></h5>
                </div>
            </div>
        </div>

        <div class="cuar-meta-block cuar-owner col-sm-4">
            <div class="panel panel-tile text-primary br-b bw5 br-primary-light">
                <div class="panel-body pn pl20 p5">
                    <i class="fa fa-users icon-bg p5"></i>
                    <h4 class="mt15 lh15">
                        <b><?php echo $recipients; ?></b>
                    </h4>
                    <h5 class="text-muted"><?php _e('Recipient', 'cuar'); ?></h5>
                </div>
            </div>
        </div>

        <div class="cuar-meta-block cuar-date col-sm-4">
            <div class="panel panel-tile text-primary br-b bw5 br-primary-light">
                <div class="panel-body pn pl20 p5">
                    <i class="fa fa-calendar icon-bg p5"></i>
                    <h4 class="mt15 lh15">
                        <b><?php echo $date; ?></b>
                    </h4>
                    <h5 class="text-muted"><?php _e('Date', 'cuar'); ?></h5>
                </div>
            </div>
        </div>

    </div>

</div>
<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.3.0 =-
 * - Compatibility with the new multiple attached files
 * - New hooks for attachment items
 *
 * -= 1.2.0 =-
 * - Updated to new responsive markup
 *
 * -= 1.1.0 =-
 * - Added file size
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
global $post;
$date = sprintf("<em>%s</em>", get_the_date());
$author = sprintf("<em>%s</em>", get_the_author_meta('display_name'));
$recipients = sprintf("<em>%s</em>", cuar_get_the_owner());
?>

<div class="cuar-single-post-header row mb-md clearfix">
    <div class="cuar-js-slick-responsive">

            <div class="cuar-author slick-slide">
                <div class="panel panel-tile cuar-panel-meta-tile">
                    <div class="panel-body">
                        <i class="fa fa-user icon-bg"></i>
                        <div class="cuar-meta-label"><?php _e('Author', 'cuar'); ?></div>
                        <div class="cuar-meta-value"><?php echo $author; ?></div>
                    </div>
                </div>
            </div>

            <div class="cuar-owner slick-slide">
                <div class="panel panel-tile cuar-panel-meta-tile">
                    <div class="panel-body">
                        <i class="fa fa-users icon-bg"></i>
                        <div class="cuar-meta-label"><?php _e('Assigned to', 'cuar'); ?></div>
                        <div class="cuar-meta-value"><?php echo $recipients; ?></div>
                    </div>
                </div>
            </div>

            <div class="cuar-date slick-slide">
                <div class="panel panel-tile cuar-panel-meta-tile">
                    <div class="panel-body">
                        <i class="fa fa-calendar icon-bg"></i>
                        <div class="cuar-meta-label"><?php _e('Date', 'cuar'); ?></div>
                        <div class="cuar-meta-value"><?php echo $date; ?></div>
                    </div>
                </div>
            </div>

    </div>
</div>
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
 */ ?>

<?php
global $post;
$date = sprintf("<em>%s</em>", get_the_date());
$author = sprintf("<em>%s</em>", get_the_author_meta('display_name'));
$recipients = sprintf("<em>%s</em>", cuar_get_the_owner());
?>

<div class="cuar-single-post-header row mb-md clearfix">
    <div class="col-xs-12">
        <div class="row clearfix">
            <div class="cuar-author col-sm-4">
                <div class="panel panel-tile cuar-panel-meta-tile">
                    <div class="panel-body">
                        <i class="fa fa-user icon-bg"></i>
                        <h4 class="cuar-meta-value"><?php echo $author; ?></h4>
                        <h5 class="cuar-meta-label"><?php _e('Author', 'cuar'); ?></h5>
                    </div>
                </div>
            </div>

            <div class="cuar-owner col-sm-4">
                <div class="panel panel-tile cuar-panel-meta-tile">
                    <div class="panel-body">
                        <i class="fa fa-users icon-bg"></i>
                        <h4 class="cuar-meta-value"><?php echo $recipients; ?></h4>
                        <h5 class="cuar-meta-label"><?php _e('Assigned to', 'cuar'); ?></h5>
                    </div>
                </div>
            </div>

            <div class="cuar-date col-sm-4">
                <div class="panel panel-tile cuar-panel-meta-tile">
                    <div class="panel-body">
                        <i class="fa fa-calendar icon-bg"></i>
                        <h4 class="cuar-meta-value"><?php echo $date; ?></h4>
                        <h5 class="cuar-meta-label"><?php _e('Date', 'cuar'); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
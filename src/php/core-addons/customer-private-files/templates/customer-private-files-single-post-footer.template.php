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

$attachments = cuar_get_the_attached_files($post->ID);
$attachment_count = count($attachments);
?>

<div class="cuar-single-post-footer">
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
    <div class="row clearfix">
        <div class="col-xs-12">
            <div class="panel panel-border panel-default top cuar-attachments">
                <div class="panel-heading">
                    <span class="panel-title">
                        <?php printf(_n('%d attachment', '%d attachments', $attachment_count, 'cuar'), $attachment_count); ?>
                    </span>
                </div>
                <div class="panel-body pn">
                    <table class="table">
                        <tbody>
                        <?php foreach ($attachments as $file_id => $file) : ?>
                            <tr>
                                <?php do_action('cuar/templates/file-attachment-item/before-caption', $post->ID, $file); ?>
                                <td class="cuar-caption">
                                    <?php cuar_the_attached_file_caption($post->ID, $file); ?>
                                </td>
                                <?php do_action('cuar/templates/file-attachment-item/after-caption', $post->ID, $file); ?>
                                <td class="cuar-size"><?php cuar_the_attached_file_size($post->ID, $file); ?></td>
                                <td class="cuar-actions">
                                    <a href="<?php cuar_the_attached_file_link($post->ID, $file); ?>" title="<?php esc_attr_e('Get file', 'cuar'); ?>" class="btn btn-default btn-sm">
                                        <span class="fa fa-download"></span>&nbsp;<?php _e('Download', 'cuar'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
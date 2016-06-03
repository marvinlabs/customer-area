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
$attachments = cuar_get_the_attached_files($post->ID);
$attachment_count = count($attachments);
?>

<div class="cuar-single-post-footer">
    <div class="panel top cuar-attachments">
        <div class="panel-heading">
            <span class="panel-title">
                <?php printf(_n('%d attachment', '%d attachments', $attachment_count, 'cuar'), $attachment_count); ?>
            </span>
        </div>
        <div class="panel-body pn">
            <table class="table table-hover table-striped">
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
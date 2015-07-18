<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
/** @var string $ftp_dir */
/** @var array $ftp_files */
/** @var string $ftp_operation */
?>

<div class="cuar-ftp-manager">
    <div class="metabox-row">
        <label for="cuar_ftp_copy_file" class="selectit">
            <input name="cuar_copy_file" type="checkbox" id="cuar_copy_file" checked="checked" value="open" class="cuar-copy-file-checkbox">
            <?php _e('Copy the file (do not delete it from the FTP folder)', 'cuar'); ?>
        </label>
    </div>

    <div class="metabox-row">
        <?php if ( !file_exists($ftp_dir)) : ?>
            <p><?php _e("The FTP upload folder does not exist.", 'cuar'); ?></p>
        <?php elseif ($this->is_dir_empty($ftp_dir)) : ?>
            <p><?php _e("The FTP upload folder is empty.", 'cuar'); ?></p>
        <?php else : ?>
            <span class="label"><label for="cuar_ftp_file_selection"><?php _e('Pick a file', 'cuar'); ?></label></span>
            <span class="field">
                <select id="cuar_ftp_file_selection" name="cuar_ftp_file_selection" multiple="multiple" class='cuar-ftp-file-selection'
                        data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <?php foreach ($ftp_files as $filename) : $file_path = $ftp_dir . '/' . $filename; ?>
                        <?php if (is_file($file_path)) : ?>
                            <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <a href="#" class="cuar-ftp-add-files button"><?php _e('Attach selected file(s)', 'cuar'); ?></a>
            </span>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    <!--
    // TODO Get all this in a proper JS file
    jQuery(document).ready(function ($) {
        $('.cuar-ftp-add-files', '.cuar-ftp-manager').click(function (event) {
            event.preventDefault();

            var selectBox = $('.cuar-ftp-file-selection', '.cuar-ftp-manager').first();
            var selectedFiles = selectBox.val();
            var postId = selectBox.data('post-id');
            var copyCheckbox = $('.cuar-copy-file-checkbox', '.cuar-ftp-manager').first();

            if (selectedFiles == null || selectedFiles.length == 0) return;

            var ftpOperation = copyCheckbox.is(':checked') ? 'ftp-copy' : 'ftp-move';

            // Add all selected files using the attachment manager
            for (var i = 0, len = selectedFiles.length; i < len; i++) {
                var filename = selectedFiles[i];
                var attachmentItem = $(document).triggerHandler('cuar:attachmentManager:addItem', [
                    postId,
                    filename,
                    filename
                ]);
                $(document).trigger('cuar:attachmentManager:sendFile', [
                    attachmentItem,
                    'ftp-folder',
                    postId,
                    filename,
                    '',
                    ftpOperation
                ]);
            }
        });

        $(document).on('cuar:attachmentManager:fileAttached', function (event, postId, oldFilename, newFilename, newCaption) {
            var copyCheckbox = $('.cuar-copy-file-checkbox', '.cuar-ftp-manager').first();
            var ftpOperation = copyCheckbox.is(':checked') ? 'ftp-copy' : 'ftp-move';

            if (ftpOperation=='ftp-move') {
                $('.cuar-ftp-file-selection', '.cuar-ftp-manager')
                    .children()
                    .filter(function () {
                        return $(this).val() == oldFilename;
                    })
                    .remove();
            }
        });
    });
    //-->
</script>
<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
/** @var string $ftp_dir */
/** @var array $ftp_files */
/** @var string $ftp_operation */
?>

<input type="hidden" name="ftp_operation" value="<?php echo $ftp_operation; ?>"/>

<div class="metabox-row">
    <?php if ( !file_exists($ftp_dir)) : ?>
        <p><?php _e("The FTP upload folder does not exist.", 'cuar'); ?></p>
    <?php elseif ($this->is_dir_empty($ftp_dir)) : ?>
        <p><?php _e("The FTP upload folder is empty.", 'cuar'); ?></p>
    <?php else : ?>
        <span class="label"><label for="cuar_ftp_<?php echo $ftp_operation; ?>_file_selection"><?php _e('Pick a file', 'cuar'); ?></label></span>
        <span class="field">
            <select id="cuar_ftp_<?php echo $ftp_operation; ?>_file_selection" name="cuar_ftp_<?php echo $ftp_operation; ?>_file_selection" multiple="multiple"
                    class='cuar-ftp-file-selection' data-post-id="<?php echo esc_attr($post->ID); ?>">
                <?php foreach ($ftp_files as $filename) : $file_path = $ftp_dir . '/' . $filename; ?>
                    <?php if (is_file($file_path)) : ?>
                        <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <a href="#" class="cuar-ftp-<?php echo $ftp_operation; ?>-files button"><?php
                if ($ftp_operation == 'copy')
                {
                    _e('Copy selected file(s)', 'cuar');
                }
                else if ($ftp_operation == 'move')
                {
                    _e('Move selected file(s)', 'cuar');
                }
                else
                {
                    _e('Unknown operation', 'cuar');
                }
                ?></a>
        </span>
    <?php endif; ?>
</div>

<script type="text/javascript">
    <!--
    // TODO Get all this in a proper JS file
    jQuery(document).ready(function ($) {
        $('.cuar-ftp-<?php echo $ftp_operation; ?>-files').click(function (event) {
            event.preventDefault();

            var selectBox = $(this).siblings('.cuar-ftp-file-selection').first();
            var selectedFiles = selectBox.val();
            var postId = selectBox.data('post-id');

            if (selectedFiles == null || selectedFiles.length == 0) return;

            // Add all selected files using the attachment manager
            for (var i = 0, len = selectedFiles.length; i < len; i++) {
                var filename = selectedFiles[i];
                $(document).trigger('cuar:attachmentManager:addItem', [
                    postId,
                    filename,
                    filename
                ]);
                $(document).trigger('cuar:attachmentManager:sendFile', [
                    'ftp-<?php echo $ftp_operation; ?>',
                    postId,
                    filename,
                    filename
                ]);
            }
        });

        // When the file has been attached, we remove it from the select box
        $(document).on('cuar:attachmentManager:fileAttached', function(event, postId, oldFilename, newFilename, newCaption) {
            $('.cuar-ftp-file-selection')
                .children()
                .filter(function() {
                    return $(this).val()==oldFilename;
                })
                .remove();
        });
    });
    //-->
</script>
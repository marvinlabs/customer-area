<?php /** Template version: 1.0.0

 */

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
            <select id="cuar_ftp_<?php echo $ftp_operation; ?>_file_selection" name="cuar_ftp_<?php echo $ftp_operation; ?>_file_selection" multiple="multiple" class='cuar-ftp-file-selection' data-post-id="<?php echo esc_attr($post->ID); ?>">
                <?php foreach ($ftp_files as $filename) : $filepath = $ftp_dir . '/' . $filename; ?>
                    <?php if (is_file($filepath)) : ?>
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
    jQuery(document).ready(function ($) {
        $('.cuar-ftp-<?php echo $ftp_operation; ?>-files').live('click', function (event) {
            event.preventDefault();

            var button = $(this);
            var selectBox = button.siblings('.cuar-ftp-file-selection');
            var selectedFiles = selectBox.val();
            var postId = selectBox.data('post-id');

            if (selectedFiles==null) return;

                addPendingFile($, postId, selectedFiles);
            }

//            // Let's go to a state where we cannot do any action anymore
//            actions.hide();
//            progress.show();
//            attachedFileItem.css('opacity', '0.5');
//
            // Post the ajax request
            $.post(
                cuar.ajaxUrl,
                {
                    'action': 'cuar_add_files_from_ftp_folder',
                    'post_id': postId,
                    'filenames': selectedFiles
                },
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data.length > 0) {
                            alert(response.data[0]);
                        }
                        actions.show();
                        progress.hide();
                        attachedFileItem.css('opacity', '1');
                    } else {
                        // Ok. Remove the line
                        attachedFileItem.slideUp(400, function () {
                            attachedFileItem.remove();
                        });
                    }
                }
            );
        });
    });
    //-->
</script>
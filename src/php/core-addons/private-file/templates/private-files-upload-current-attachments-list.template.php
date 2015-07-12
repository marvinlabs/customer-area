<?php /** Template version: 1.0.0

 */

/** @var array $attached_files */
/** @var string $current_attachment_list_item_template */
?>

<div class="cuar-private-file-attachments">
    <?php foreach ($attached_files as $file_index => $attached_file) : ?>
        <?php include($current_attachment_list_item_template) ?>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        $('.cuar-remove-attached-file').live('click', function (event) {
            event.preventDefault();

            if (!confirm("<?php esc_attr_e('Do you really want to remove this file?', 'cuar'); ?>")) return;

            var attachedFileItem = $(this).closest('.cuar-private-file-attachment');
            var actions = $(this).closest('.cuar-actions');
            var progress = actions.siblings('.cuar-progress');
            var postId = attachedFileItem.data('post-id');
            var filename = attachedFileItem.data('file-name');

            // Let's go to a state where we cannot do any action anymore
            actions.hide();
            progress.show();
            attachedFileItem.css('opacity', '0.5');

            // Post the ajax request
            $.post(
                ajaxurl,
                {
                    'action': 'cuar_remove_attached_file',
                    'post_id': postId,
                    'filename': filename
                },
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data.length>0) {
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
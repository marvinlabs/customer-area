<?php /** Template version: 1.0.0

 */

/** @var array $attached_files */
/** @var string $current_attachment_list_item_template */
?>

<div class="cuar-file-attachments">
    <?php
    foreach ($attached_files as $file_id => $attached_file)
    {
        include($current_attachment_list_item_template);
    }
    ?>
</div>

<script type="text/javascript">
    //    <!--
    //    jQuery(document).ready(function ($) {
    //
    //    /**
    //     * Add a list of file names to our attached files panel
    //     *
    //     * @param $ jQuery object
    //     * @param postId The post ID
    //     * @param selectedFiles The files to add
    //     */
    //    function addPendingFile($, postId, selectedFiles) {
    //        var existingElements = $('.cuar-private-file-attachments .cuar-private-file-attachment');
    //
    //        for (var i = 0; i < selectedFiles.length; ++i) {
    //            var filename = selectedFiles[i];
    //            var fileElement = findFileElement($, filename, existingElements);
    //
    //            if (fileElement === undefined) {
    //                fileElement = $('.cuar-private-file-attachment-template>div:first-child').clone();
    //                fileElement.appendTo('.cuar-private-file-attachments');
    //            }
    //
    //            changeFileElementState($, fileElement, postId, filename, 'pending');
    //        }
    //    }
    //
    //    /**
    //     * Change the state of a pending file
    //     *
    //     * @param $
    //     * @param filename
    //     * @param existingElements
    //     */
    //    function findFileElement($, filename, existingElements = null) {
    //        if (existingElements == null) {
    //            existingElements = $('.cuar-private-file-attachments .cuar-private-file-attachment');
    //        }
    //
    //        return existingElements.filter(function () {
    //            return $(this).data('filename') == filename;
    //        });
    //    }
    //
    //    /**
    //     * Change the state of a file
    //     *
    //     * @param $
    //     * @param fileElement
    //     * @param postId
    //     * @param filename
    //     * @param state
    //     * @param progress
    //     */
    //    function changeFileElementState($, fileElement, postId, filename, state, progress=0) {
    //        if (state == 'pending') {
    //            fileElement.data('file-name', filename);
    //            fileElement.data('post-id', postId);
    //            fileElement.children('.cuar-caption').html(filename);
    //            fileElement.children('.cuar-actions').hide();
    //            fileElement.children('.cuar-progress').show();
    //            fileElement.removeClass('cuar-error')
    //                .addClass('cuar-pending')
    //                .removeClass('cuar-ready');
    //        } else if (state == 'ready') {
    //            fileElement.data('file-name', filename);
    //            fileElement.data('post-id', postId);
    //            fileElement.children('.cuar-caption').html(filename);
    //            fileElement.children('.cuar-actions').show();
    //            fileElement.children('.cuar-progress').hide();
    //            fileElement.removeClass('cuar-error')
    //                .removeClass('cuar-pending')
    //                .addClass('cuar-ready');
    //        } else if (state == 'error') {
    //            fileElement.data('file-name', filename);
    //            fileElement.data('post-id', postId);
    //            fileElement.children('.cuar-caption').html(filename);
    //            fileElement.children('.cuar-actions').show();
    //            fileElement.children('.cuar-progress').hide();
    //            fileElement.addClass('cuar-error')
    //                .removeClass('cuar-pending')
    //                .removeClass('cuar-ready');
    //        }
    //    }
    //    //-->
</script>
<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
?>

<div class="metabox-row">
    <div class="cuar-dropzone" id="cuar_dropzone" data-post-id="<?php echo esc_attr($post->ID); ?>">
        <p class="cuar-dropzone-message">
            <span class="dashicons dashicons-upload"></span>
            <br/>
            <?php _e('Drop your files here or click me!', 'cuar'); ?>
        </p>
        <input type="file" name="cuar_file" />
    </div>
</div>

<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        var dropzone = $("#cuar_dropzone");

        dropzone.children('input[type=file]').css({'opacity': 0});

        dropzone.fileupload({
            url: cuar.ajaxUrl,
            dataType: 'json',
            paramName: 'cuar_file',
            formData: {
                'action': 'cuar_attach_file',
                'method': 'classic-upload',
                'post_id': "<?php echo esc_attr($post->ID); ?>"
            },
            dropZone: dropzone,
            add: function (e, data) {
                // Add all selected files using the attachment manager
                for (var i = 0, len = data.files.length; i < len; i++) {
                    var filename = data.files[i].name;
                    data.files[i].attachmentItem = $(document).triggerHandler('cuar:attachmentManager:addItem', [
                        "<?php echo esc_attr($post->ID); ?>",
                        filename,
                        filename
                    ]);                     
                    $(document).trigger('cuar:attachmentManager:updateItemState', [
                        data.files[i].attachmentItem,
                        'pending'
                    ]);
                }
                data.submit();
            },
            done: function (e, data) {
                for (var i = 0, len = data.files.length; i < len; i++) {
                    if (data.result.success) {
                        var newFilename = data.result.data.file;
                        var newCaption = data.result.data.caption;
                        $(document).trigger('cuar:attachmentManager:updateItem', [
                            data.files[i].attachmentItem,
                            "<?php echo esc_attr($post->ID); ?>",
                            newFilename,
                            newCaption
                        ]);
                        $(document).trigger('cuar:attachmentManager:updateItemState', [
                            data.files[i].attachmentItem,
                            'success'
                        ]);
                    }
                    else {
                        if (data.result.data.length > 0) {
                            alert(data.result.data[0]);
                        }

                        $(document).trigger('cuar:attachmentManager:updateItemState', [
                            data.files[i].attachmentItem,
                            'error'
                        ]);
                    }
                }
            },
            fail: function (e, data) {
                for (var i = 0, len = data.files.length; i < len; i++) {
                    alert(data.errorThrown);

                    $(document).trigger('cuar:attachmentManager:updateItemState', [
                        data.files[i].attachmentItem,
                        'error'
                    ]);
                }
            },
            progress: function (e, data) {
                for (var i = 0, len = data.files.length; i < len; i++) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $(document).trigger('cuar:attachmentManager:updateItemProgress', [
                        data.files[i].attachmentItem,
                        progress
                    ]);
                }
            }
        });

        $(document).bind('dragover', function (e) {
            var dropZone = $('#cuar_dropzone');
            var timeout = window.dropZoneTimeout;

            if (!timeout) {
                dropZone.addClass('in');
            } else {
                clearTimeout(timeout);
            }

            var found = false;
            var node = e.target;
            do {
                if (node === dropZone[0]) {
                    found = true;
                    break;
                }
                node = node.parentNode;
            } while (node != null);
            if (found) {
                dropZone.addClass('hover');
            } else {
                dropZone.removeClass('hover');
            }
            window.dropZoneTimeout = setTimeout(function () {
                window.dropZoneTimeout = null;
                dropZone.removeClass('in hover');
            }, 100);
        });
    })
    ;
    //-->
</script>
<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
?>

<div class="metabox-row">
    <div class="dropzone" id="cuar_dropzone" data-post-id="<?php echo esc_attr($post->ID); ?>">
        <input type="file" name="cuar_file" />
    </div>
</div>

<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        var dropzone = $("#cuar_dropzone");
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
                    $(document).trigger('cuar:attachmentManager:addItem', [
                        "<?php echo esc_attr($post->ID); ?>",
                        filename,
                        filename
                    ]);
                    $(document).trigger('cuar:attachmentManager:updateItemState', [
                        filename,
                        'pending'
                    ]);
                }
                data.submit();
            },
            done: function (e, data) {
                for (var i = 0, len = data.files.length; i < len; i++) {
                    var filename = data.files[i].name;
                    if (data.result.success) {
                        var newFilename = data.result.data.file;
                        var newCaption = data.result.data.caption;
                        $(document).trigger('cuar:attachmentManager:updateItem', [
                            filename,
                            "<?php echo esc_attr($post->ID); ?>",
                            newFilename,
                            newCaption
                        ]);
                        $(document).trigger('cuar:attachmentManager:updateItemState', [
                            newFilename,
                            'success'
                        ]);
                    }
                    else {
                        if (data.result.data.length > 0) {
                            alert(data.result.data[0]);
                        }

                        $(document).trigger('cuar:attachmentManager:updateItemState', [
                            filename,
                            'error'
                        ]);
                    }
                }
            },
            fail: function (e, data) {
                for (var i = 0, len = data.files.length; i < len; i++) {
                    alert(data.errorThrown);

                    var filename = data.files[i].name;
                    $(document).trigger('cuar:attachmentManager:updateItemState', [
                        filename,
                        'error'
                    ]);
                }
            },
            progress: function (e, data) {
                for (var i = 0, len = data.files.length; i < len; i++) {
                    var filename = data.files[i].name;
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $(document).trigger('cuar:attachmentManager:updateItemProgress', [
                        filename,
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
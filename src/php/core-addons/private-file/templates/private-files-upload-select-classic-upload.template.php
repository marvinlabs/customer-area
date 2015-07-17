<?php /** Template version: 1.0.0

 */

/** @var WP_Post $post */
?>

<div class="metabox-row">
    <div class="dropzone" id="cuar_dropzone" data-post-id="<?php echo esc_attr($post->ID); ?>">
    </div>
    <div class="dropzone-result">
    </div>
    <div class="dropzone-progress">
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
//                    $(document).trigger('cuar:attachmentManager:addItem', [
//                        'classic-upload',
//                        "<?php //echo esc_attr($post->ID); ?>//",
//                        filename,
//                        filename
//                    ]);
                    $('<p/>').text('started ' + filename).appendTo('.dropzone-result');
                }
                data.submit();
            },
            done: function (e, data) {
                if (data.result.success) {
                    $('<p/>').text('done ' + file.name).appendTo('.dropzone-result');
                } else {
                    $('<p/>').text('error ' + data.result.data).appendTo('.dropzone-result');
                }
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('.dropzone-progress').text(progress + '%');
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

//            url: cuar.ajaxUrl,
//            paramName: 'cuar_file',
//            params: {
//                'action': 'cuar_attach_file',
//                'method': 'classic-upload',
//                'post_id': "<?php //echo esc_attr($post->ID); ?>//"
//            },
//            success: function (file, response) {
//            },
//            error: function (file, response) {
//            }
//        });


//        $('.cuar-ftp-<?php //echo $ftp_operation; ?>//-files').click(function (event) {
//            event.preventDefault();
//
//            var selectBox = $(this).siblings('.cuar-ftp-file-selection').first();
//            var selectedFiles = selectBox.val();
//            var postId = selectBox.data('post-id');
//
//            if (selectedFiles == null || selectedFiles.length == 0) return;
//
//            // Add all selected files using the attachment manager
//            for (var i = 0, len = selectedFiles.length; i < len; i++) {
//                var filename = selectedFiles[i];
//                $(document).trigger('cuar:attachmentManager:addItem', [
//                    'ftp-<?php //echo $ftp_operation; ?>//',
//                    postId,
//                    filename,
//                    filename
//                ]);
//            }
//        });
//
//        // When the file has been attached, we remove it from the select box
//        $(document).on('cuar:attachmentManager:fileAttached', function(event, postId, filename) {
//            $('.cuar-ftp-file-selection')
//                .children()
//                .filter(function() {
//                    return $(this).val()==filename;
//                })
//                .remove();
//        });
    });
    //-->
</script>
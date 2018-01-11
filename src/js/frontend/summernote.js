function bootstrapSummernote($, editorSelector) {
    "use strict";

    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) {
        return;
    }

    function jsError(string) {
        $(editorSelector + ' + .note-editor > .note-toolbar > .cuar-js-manager-errors').hide().empty().append(
            '<div class="alert alert-danger alert-dismissable cuar-js-error-item mbn mt-xs" style="margin-right: 5px; line-height: 1.2em;">' +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
            '<span class="cuar-js-error-content" style="font-weight: lighter!important;">' + string + '</span>' +
            '</div>').show();
    }

    function sendImage(file) {
        if (!file.type.includes('image')) {
            return jsError("The type of file you tried to upload is not an image.");
        }

        var data = new FormData();
        var nonce = $("#cuar_insert_image_nonce").val();
        var type = $("#cuar_post_type").val();

        data.append('file', file);
        data.append('action', 'cuar_insert_image');
        data.append("nonce", nonce);
        data.append("post_type", type);

        $.ajax({
            url: cuar.ajaxUrl,
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'JSON',
            data: data,
            success: function (data, textStatus, jqXHR) {
                if (data.response === "SUCCESS") {
                    $(editorSelector).summernote('insertImage', data.url, data.name);
                }
                else {
                    return jsError(data.error);
                }
            }
        }).fail(function (e) {
            return jsError("We could not get answer from the server, please contact site administrator.");
        });
    }

    var snOptions = {
        toolbar: [
            ['block', ['style']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['table', 'hr', 'picture', 'link']],
            ['view', ['codeview', 'fullscreen']],
            ['tools', ['undo', 'redo', 'help']]
        ],
        callbacks: {
            onInit: function () {
                $('body > .note-popover').appendTo("#cuar-js-content-container");

                $(editorSelector + ' + .note-editor > .note-toolbar').append('<div class="cuar-js-manager-errors" style="display: none;"></div>');
            },
            onImageUpload: function (files) {
                for (var i = 0; i < files.length; i++) {
                    sendImage(files[i]);
                }
            }
        }
    };

    if (typeof cuar !== 'undefined') {
        snOptions.lang = cuar.locale;
    }

    $(editorSelector).summernote(snOptions);
}

jQuery(document).ready(function ($) {
    "use strict";

    if ($('.cuar-form .cuar-js-wizard-section').length > 0) {
        $('#cuar-js-content-container').on('cuar:wizard:initialized', function () {
            bootstrapSummernote($, ".cuar-wizard .cuar-js-richeditor");
        });
    } else {
        bootstrapSummernote($, ".cuar-js-richeditor");
    }
});

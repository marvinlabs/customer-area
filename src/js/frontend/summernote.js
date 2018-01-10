function bootstrapSummernote($, editorSelector) {
    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) return;

    function sendImage(file) {
        if (file.type.includes('image')) {

            var data = new FormData();
            var nonce = $("#cuar_insert_image_nonce").val();

            data.append('file', file);
            data.append('action', 'cuar_insert_image');
            data.append("nonce", nonce );

            $.ajax({
                url: cuar.ajaxUrl,
                type: 'POST',
                contentType: false,
                cache: false,
                processData: false,
                dataType: 'JSON',
                data: data,
                success: function(data, textStatus, jqXHR) {
                    if( data.response === "SUCCESS" ){
                        $(editorSelector).summernote('insertImage', data.url, data.name);
                    }
                    else {
                        console.log(data.error);
                    }
                }
            }).fail(function (e) {
                console.log(e);
            });
        } else {
            console.log("The type of file you tried to upload is not an image");
        }
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
            onInit: function() {
                $('body > .note-popover').appendTo("#cuar-js-content-container");
            },
            onImageUpload: function(files)
            {
                for(var i = 0; i < files.length; i++)
                {
                    sendImage(files[i]);
                }
            }
        }
    };

    if (typeof cuar !== 'undefined') {
        snOptions['lang'] = cuar.locale;
    }

    $(editorSelector).summernote(snOptions);
}

jQuery(document).ready(function ($) {
    if ($('.cuar-form .cuar-js-wizard-section').length > 0) {
        $('#cuar-js-content-container').on('cuar:wizard:initialized', function () {
            bootstrapSummernote($, ".cuar-wizard .cuar-js-richeditor");
        });
    } else {
        bootstrapSummernote($, ".cuar-js-richeditor");
    }
});

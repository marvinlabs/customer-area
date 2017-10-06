function bootstrapSummernote($, editorSelector) {
    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) return;

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

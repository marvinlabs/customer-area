jQuery(document).ready(function ($) {
    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) return;

    var snOptions = {
        toolbar: [
            ['block', ['style']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['table', 'hr', 'picture', 'link', 'video']],
            ['view', ['codeview', 'fullscreen']],
            ['tools', ['undo', 'redo', 'help']]
        ],
        callbacks: {
            onInit: function() {
                $('body > .note-popover').appendTo("#cuar-js-content-container");
            }
        }
    };

    if (typeof cuar != 'undefined') {
        snOptions['lang'] = cuar.locale;
    }

    // Run summernote editor on the elements having that CSS class
    $(".cuar-js-richeditor").summernote(snOptions);
});
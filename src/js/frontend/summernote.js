jQuery(document).ready(function($) {
    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) return;

    // Run summernote editor on the elements having that CSS class
    $(".cuar-js-richeditor").summernote({
        toolbar: [
            ['block', ['style']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['table', 'hr', 'picture', 'link', 'video']],
            ['view', ['codeview', 'fullscreen']],
            ['tools', ['undo', 'redo', 'help']]
        ]
    });
});
jQuery(document).ready(function($) {
    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) return;

    // Run summernote editor on the elements having that CSS class
    $(".cuar-js-richeditor").summernote({
        // Put common SummerNote options here
        lang: cuar.locale
    });
});
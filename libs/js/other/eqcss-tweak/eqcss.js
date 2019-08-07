/*
 * EQCSS Tweak
 * Author: Thomas Lartaud
 *
 * This script allows EQCSS to not read and parse others files than those needed.
 * Speedup site loading.
 * Needs to be loaded on footer.
 *
 */
;
(function ($, window, undefined) {
    "use strict";

    $('link, script, style').each(function(){
        var id = $(this).attr('id');
        if((typeof id !== 'undefined' && id.substr(id.length - 9) !== 'eqcss-css') || typeof id === 'undefined') {
            $(this).attr('data-eqcss-read', true);
        }
    });

})(jQuery, window);

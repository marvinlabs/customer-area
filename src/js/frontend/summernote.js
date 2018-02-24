function bootstrapSummernote($, editorSelector) {
    "use strict";

    // Bail if summernote is not loaded
    if (!$.isFunction($.fn.summernote)) {
        return;
    }

    // Summernote remove button plugin
    (function (factory) {
        if (typeof define === 'function' && define.amd) {
            define(['jquery'], factory);
        } else if (typeof module === 'object' && module.exports) {
            module.exports = factory(require('jquery'));
        } else {
            factory(window.jQuery);
        }
    }(function ($) {
        $.extend(true, $.summernote.lang, {
            'en-US': {
                deleteImage: {
                    tooltip: cuar.ajaxEditorDeleteImg
                }
            }
        });
        $.extend($.summernote.options, {
            deleteImage: {
                icon: '<i class="note-icon-trash"></i>'
            }
        });
        $.extend($.summernote.plugins, {
            'deleteImage': function (context) {
                var ui = $.summernote.ui,
                    $note = context.layoutInfo.note,
                    $editor = context.layoutInfo.editor,
                    $editable = context.layoutInfo.editable,
                    options = context.options,
                    lang = options.langInfo;
                context.memo('button.deleteImage', function () {
                    var button = ui.button({
                        contents: options.deleteImage.icon,
                        tooltip: lang.deleteImage.tooltip,
                        click: function () {
                            var img = $($editable.data('target'));
                            updateImage(img, 'delete', function () {
                                context.invoke('editor.afterCommand');
                            });
                        }
                    });
                    return button.render();
                });
            }
        });
    }));

    function jsError(string) {
        $(editorSelector + ' + .note-editor > .note-toolbar > .cuar-js-manager-errors').hide().empty().append(
            '<div class="alert alert-danger alert-dismissable cuar-js-error-item mbn mt-xs" style="margin-right: 5px; line-height: 1.2em;">' +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
            '<span class="cuar-js-error-content" style="font-weight: lighter!important;">' + string + '</span>' +
            '</div>').show();
    }

    function updateImage(file, method, callback) {
        if (method === 'upload' && !file.type.includes('image')) {
            return jsError(cuar.ajaxEditorImageIsNotImg);
        }

        var data = new FormData(),
            nonce = $("#cuar_insert_image_nonce").val(),
            type = $("#cuar_post_type").val(),
            id = $("#cuar_post_id").val();

        data.append("nonce", nonce);
        data.append("post_type", type);
        data.append("post_id", id);

        if (method === 'upload') {
            data.append('action', 'cuar_insert_image');
            data.append('file', file);

        } else if (method === 'delete') {
            data.append("action", 'cuar_delete_image');
            data.append("name", file.data('filename'));
            data.append("subdir", file.data('subdir'));
            data.append("author", file.data('author'));
            data.append("hash", file.data('hash'));
        }

        $.ajax({
            url: cuar.ajaxUrl,
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'JSON',
            data: data,
            success: function (response, textStatus, jqXHR) {
                if (response.success === true) {
                    if (method === 'upload') {
                        $(editorSelector).summernote('insertImage', response.data.url, function ($image) {
                            $image.css('width', 'auto');
                            $image.css('height', 'auto');
                            $image.css('max-width', '100%');
                            $image.attr('data-subdir', response.data.subdir);
                            $image.attr('data-filename', response.data.name);
                            $image.attr('data-author', response.data.author);
                            $image.attr('data-hash', response.data.hash);
                        });
                    } else if (method === 'delete') {
                        if (file.parent().is('a')) {
                            file.parent().remove();
                        } else {
                            file.remove();
                        }
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                } else {
                    return jsError(response.data);
                }
            }
        }).fail(function (e) {
            return jsError(cuar.ajaxEditorServerUnreachable);
        });
    }

    var snOptions = {
        container: '#cuar-js-content-container',
        toolbar: [
            ['block', ['style']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['table', 'hr', 'picture', 'link']],
            ['view', ['codeview', 'fullscreen']],
            ['tools', ['undo', 'redo', 'help']]
        ],
        popover: {
            image: [
                ['custom', ['imageAttributes']],
                ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                ['float', ['floatLeft', 'floatRight', 'floatNone']],
                ['custom', ['deleteImage']]
            ]
        },
        imageAttributes: {
            icon: '<i class="note-icon-pencil"/>',
            removeEmpty: false,
            disableUpload: true
        },
        callbacks: {
            onInit: function () {
                $(editorSelector + ' + .note-editor > .note-toolbar').append('<div class="cuar-js-manager-errors" style="display: none;"></div>');
            },
            onImageUpload: function (files) {
                for (var i = 0; i < files.length; i++) {
                    updateImage(files[i], 'upload');
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




/*
 * 	Scripts for the private files core add-on
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.classicUploader = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.classicUploader", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.classicUploader.defaultOptions, options);

            // Hide the file input but do not disable it fully
            base._getFileInput().css({'opacity': 0});

            // Enable the dropzone
            var postId = base._getPostId();
            var nonceName = 'cuar_classic-upload_' + postId;

            var formData = {
                'action': 'cuar_attach_file',
                'method': 'classic-upload',
                'post_id': postId
            };
            formData[nonceName] = base._getNonce();

            var dropzone = base._getDropZone();
            dropzone.fileupload({
                url: cuar.ajaxUrl,
                dataType: 'json',
                paramName: 'cuar_file',
                formData: formData,
                dropZone: dropzone,
                add: base._onFileUploadAdd,
                done: base._onFileUploadDone,
                fail: base._onFileUploadFail,
                progress: base._onFileUploadProgress
            });

            // Add some effects to the dropzone
            $(document).bind('dragover', base._onDragOverDropZone);
        };

        /**
         * Dropzone effects on drag over
         */
        base._onDragOverDropZone = function (e) {
            var dropZone = base._getDropZone();
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
        };

        /**
         * File upload callback
         */
        base._onFileUploadAdd = function (e, data) {
            // Add all selected files using the attachment manager
            for (var i = 0, len = data.files.length; i < len; i++) {
                var filename = data.files[i].name;
                data.files[i].attachmentItem = $(document).triggerHandler('cuar:attachmentManager:addItem', [
                    base._getPostId(),
                    filename,
                    filename
                ]);

                if (data.files[i].attachmentItem == null) {
                    return false;
                }

                $(document).trigger('cuar:attachmentManager:updateItemState', [
                    data.files[i].attachmentItem,
                    'pending'
                ]);
            }
            data.submit();
        };

        /**
         * File upload callback
         */
        base._onFileUploadDone = function (e, data) {
            for (var i = 0, len = data.files.length; i < len; i++) {
                if (data.result.success) {
                    var newFilename = data.result.data.file;
                    var newCaption = data.result.data.caption;
                    $(document).trigger('cuar:attachmentManager:updateItem', [
                        data.files[i].attachmentItem,
                        base._getPostId(),
                        newFilename,
                        newCaption
                    ]);
                    $(document).trigger('cuar:attachmentManager:updateItemState', [
                        data.files[i].attachmentItem,
                        'success'
                    ]);
                }
                else {
                    var errorMessage = null;
                    if (data.result.data.length > 0) {
                        errorMessage = data.result.data[0];
                    }

                    $(document).trigger('cuar:attachmentManager:showError', [
                        data.files[i].attachmentItem,
                        data.files[i].name,
                        errorMessage,
                        true
                    ]);
                }
            }
        };

        /**
         * File upload callback
         */
        base._onFileUploadFail = function (e, data) {
            for (var i = 0, len = data.files.length; i < len; i++) {
                $(document).trigger('cuar:attachmentManager:showError', [
                    data.files[i].attachmentItem,
                    data.files[i].name,
                    data.errorThrown,
                    true
                ]);
            }
        };

        /**
         * File upload callback
         */
        base._onFileUploadProgress = function (e, data) {
            for (var i = 0, len = data.files.length; i < len; i++) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $(document).trigger('cuar:attachmentManager:updateItemProgress', [
                    data.files[i].attachmentItem,
                    progress
                ]);
            }
        };

        /** Getter */
        base._getNonce = function () {
            return $('#cuar_classic-upload_' + base._getPostId(), base.el).val();
        };

        /** Getter */
        base._getPostId = function () {
            return base.$el.data('post-id');
        };

        /** Getter */
        base._getDropZone = function () {
            return $(base.options.dropzone, base.el).first();
        };

        /** Getter */
        base._getFileInput = function () {
            return $(base.options.fileInput, base.el).first();
        };

        // Make it go!
        base.init();
    };

    $.cuar.classicUploader.defaultOptions = {
        dropzone: '.cuar-js-dropzone',                         // The dropzone
        fileInput: '.cuar-js-file-input'                       // The fallback file input
    };

    $.fn.classicUploader = function (options) {
        return this.each(function () {
            (new $.cuar.classicUploader(this, options));
        });
    };

})(jQuery);

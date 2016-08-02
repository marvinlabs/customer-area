/*
 * 	Scripts for the private files core add-on
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.ftpUploader = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.ftpUploader", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.ftpUploader.defaultOptions, options);

            // Attach event handler to button click
            base._getAddButton().click(base._onAddButtonClicked);

            // Remove from the list when the move operation is selected
            $(document).on('cuar:attachmentManager:fileAttached', base._removeFileFromSelect);
        };

        /**
         * Remove from the list when the move operation is selected
         *
         * @param event
         * @param postId
         * @param oldFilename
         * @param newFilename
         * @param newCaption
         * @private
         */
        base._removeFileFromSelect = function (event, item, postId, oldFilename, newFilename, newCaption) {
            var ftpOperation = base._getFtpOperation();
            if (ftpOperation == 'ftp-move') {
                base._getSelectBox()
                    .children()
                    .filter(function () {
                        return $(this).val() == oldFilename;
                    })
                    .remove();
            }
        };

        /**
         * Add the selected files
         * @param event
         * @private
         */
        base._onAddButtonClicked = function (event) {
            event.preventDefault();

            var selectedFiles = base._getSelectedFiles();
            var postId = base._getPostId();
            var ftpOperation = base._getFtpOperation();

            if (selectedFiles == null || selectedFiles.length == 0) return;

            // Add all selected files using the attachment manager
            for (var i = 0, len = selectedFiles.length; i < len; i++) {
                var filename = selectedFiles[i];
                var attachmentItem = $(document).triggerHandler('cuar:attachmentManager:addItem', [
                    postId,
                    filename,
                    filename
                ]);
                
                if (attachmentItem!=null) {
                    $(document).trigger('cuar:attachmentManager:sendFile', [
                        attachmentItem,
                        'ftp-folder',
                        postId,
                        base._getNonce(),
                        filename,
                        '',
                        ftpOperation
                    ]);
                }
            }
        };

        /** Getter */
        base._getFtpOperation = function () {
            return base._getCopyCheckbox().is(':checked') ? 'ftp-copy' : 'ftp-move';
        };

        /** Getter */
        base._getNonce = function () {
            return $('#cuar_ftp-folder_' + base._getPostId(), base.el).val();
        };

        /** Getter */
        base._getPostId = function () {
            return base.$el.data('post-id');
        };

        /** Getter */
        base._getSelectedFiles = function () {
            return base._getSelectBox().val();
        };

        /** Getter */
        base._getAddButton = function () {
            return $(base.options.addButton, base.el);
        };

        /** Getter */
        base._getSelectBox = function () {
            return $(base.options.selectBox, base.el).first();
        };

        /** Getter */
        base._getCopyCheckbox = function () {
            return $(base.options.copyCheckbox, base.el).first();
        };

        // Make it go!
        base.init();
    };

    $.cuar.ftpUploader.defaultOptions = {
        addButton: '.cuar-js-ftp-add-files',               // The button to start the upload process
        selectBox: '.cuar-js-ftp-file-selection',          // The select box listing the files
        copyCheckbox: '.cuar-js-copy-file-checkbox'        // The check box to toggle copy/move
    };

    $.fn.ftpUploader = function (options) {
        return this.each(function () {
            (new $.cuar.ftpUploader(this, options));
        });
    };

})(jQuery);

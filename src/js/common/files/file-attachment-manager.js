/*
 * 	Scripts for the private files core add-on
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.fileAttachmentManager = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.fileAttachmentManager", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.fileAttachmentManager.defaultOptions, options);

            // Show the first selector (initially they should all be hidden but the first)
            base._getSelectors().first().show();

            // Change the selector when the combo box value changes
            base._getSelectorInput().change(base._onSelectorInputChanged);

            // Removal of items
            base._getAttachmentList().on('click', base.options.removeAction, base._onRemoveActionClick);

            // Errors
            base._getErrorList().on('click', base.options.dismissAction, base._onDismissError);

            // Bind to our custom events
            $(document).on('cuar:attachmentManager:addItem', base._onAddAttachmentItem);
            $(document).on('cuar:attachmentManager:sendFile', base._onSendFile);
            $(document).on('cuar:attachmentManager:updateFile', base._onUpdateFile);
            $(document).on('cuar:attachmentManager:updateItem', base._onUpdateAttachmentItem);
            $(document).on('cuar:attachmentManager:updateItemProgress', base._onUpdateAttachmentItemProgress);
            $(document).on('cuar:attachmentManager:updateItemState', base._onUpdateAttachmentItemState);
            $(document).on('cuar:attachmentManager:showError', base._onShowError);
        };

        /**
         * When a remove attachment button is clicked, send an AJAX request
         */
        base._onShowError = function (event, item, filename, errorMessage, isRemoveRequired) {
            base._showError(item, filename, errorMessage, isRemoveRequired);
        };

        /**
         * When a remove attachment button is clicked, send an AJAX request
         */
        base._onUpdateAttachmentItem = function (event, item, postId, newFilename, newCaption) {
            if (item.length == 0) {
                console.log('_onUpdateAttachmentItem :: Item for not found');
                return;
            }

            base._updateAttachmentItem(item, postId, newFilename, newCaption);
        };

        /**
         * Show some progress on the item
         */
        base._onUpdateAttachmentItemProgress = function (event, item, progress) {
            if (item.length == 0) {
                console.log('_onUpdateAttachmentItemProgress :: Item not found');
                return;
            }

            base._updateAttachmentItemProgress(item, progress);
        };

        /**
         * Show some progress on the item
         */
        base._onUpdateAttachmentItemState = function (event, item, state) {
            if (item.length == 0) {
                console.log('_onUpdateAttachmentItemState :: Item not found');
                return;
            }

            base._updateAttachmentItemState(item, state);
        };

        /**
         * When the selector input value changes, show the appropriate selector
         */
        base._onSelectorInputChanged = function () {
            var selectors = base._getSelectors();
            var selection = $(this).val();
            var target = selectors.filter(function () {
                return $(this).data('method') == selection;
            });

            // Do nothing if already visible
            if (target.is(":visible")) return;

            // Hide previous and then show new
            var visibleSelectors = selectors.filter(':visible');
            if (visibleSelectors.length <= 0) {
                target.fadeIn();
            } else {
                visibleSelectors.fadeOut("fast", function () {
                    target.fadeIn();
                });
            }
        };

        /**
         * When a remove attachment button is clicked, send an AJAX request
         */
        base._onRemoveActionClick = function (event) {
            event.preventDefault();

            if (!confirm(cuar.confirmDeleteAttachedFile)) return;

            var attachedItem = $(this).closest(base.options.attachmentItem);
            var postId = attachedItem.data('post-id');
            var filename = attachedItem.data('filename');
            var nonceValue = base._getAttachmentListRemoveNonce();

            // Let's go to a state where we cannot do any action anymore
            base._updateAttachmentItemState(attachedItem, 'pending');

            // Post the ajax request
            var ajaxParams = {
                'action': 'cuar_remove_attached_file',
                'post_id': postId,
                'filename': filename,
                'cuar_remove_attachment_nonce': nonceValue
            };

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        var errorMessage = '';
                        if (response.data.length > 0) {
                            errorMessage = response.data[0];
                        }
                        base._showError(attachedItem, filename, errorMessage, false);
                    } else {
                        // Remove item
                        base._removeItem(attachedItem);
                    }
                }
            );
        };

        /**
         * Callback for the event cuar:attachmentManager:sendFile
         * @param event
         * @param method
         * @param postId
         * @param filename
         * @param caption
         * @private
         */
        base._onSendFile = function (event, item, method, postId, nonceValue, filename, caption, extra) {
            var tempCaption = caption;
            if (caption === undefined || caption.trim().length == 0) {
                tempCaption = filename;
            }

            base._updateAttachmentItem(item, postId, filename, tempCaption);
            base._updateAttachmentItemState(item, 'pending');

            // Send some Ajax
            var ajaxParams = {
                'action': 'cuar_attach_file',
                'method': method,
                'post_id': postId,
                'filename': filename,
                'caption': tempCaption,
                'extra': extra
            };

            var nonceName = 'cuar_' + method + '_' + postId;
            ajaxParams[nonceName] = nonceValue;

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        var errorMessage = '';
                        if (response.data.length > 0) {
                            errorMessage = response.data[0];
                        }
                        base._showError(item, filename, errorMessage, true);
                    } else {
                        var newFilename = response.data.file;
                        var newCaption = response.data.caption;

                        base._updateAttachmentItem(item, postId, newFilename, newCaption);
                        base._updateAttachmentItemState(item, 'success');

                        $(document).trigger('cuar:attachmentManager:fileAttached', [
                            item,
                            postId,
                            filename,
                            newFilename,
                            newCaption
                        ]);
                    }
                }
            );
        };

        /**
         * Callback for the event cuar:attachmentManager:updateFile
         * @param event
         * @param item
         * @param postId
         * @param nonceValue
         * @param filename
         * @param caption
         * @private
         */
        base._onUpdateFile = function (event, item, postId, nonceValue, filename, caption) {
            var tempCaption = caption;
            if (caption === undefined || caption.trim().length == 0) {
                tempCaption = filename;
            }

            base._updateAttachmentItemState(item, 'pending');

            // Send some Ajax
            var ajaxParams = {
                'action': 'cuar_update_attached_file',
                'post_id': postId,
                'filename': filename,
                'caption': tempCaption,
                'cuar_update_attachment_nonce': nonceValue
            };

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        var errorMessage = '';
                        if (response.data.length > 0) {
                            errorMessage = response.data[0];
                        }
                        base._showError(item, filename, errorMessage, true);
                    } else {
                        var newFilename = response.data.file;
                        var newCaption = response.data.caption;

                        base._updateAttachmentItem(item, postId, newFilename, newCaption);
                        base._updateAttachmentItemState(item, 'success');
                    }
                }
            );
        };

        /**
         * Callback for the event cuar:attachmentManager:addItem
         * @param event
         * @param postId
         * @param filename
         * @param caption
         * @private
         */
        base._onAddAttachmentItem = function (event, postId, filename, caption, extra) {
            // See if we have more items than allowed
            if (cuar.maxAttachmentCount > 0 && base._getAttachmentItems().length >= cuar.maxAttachmentCount) {
                base._showError(null, filename, cuar.tooManyAttachmentsAlready, true);
                return null;
            }

            var item = base._getAttachmentTemplate().clone();
            item.appendTo(base.options.attachmentList);

            base._updateAttachmentItem(item, postId, filename, caption);
            base._getAttachmentListEmptyMessage().hide();

            return item;
        };

        base._removeItem = function(item) {
            item.fadeOut(400, function () {
                if (base._getAttachmentItems().length <= 1) {
                    base._getAttachmentListEmptyMessage().show();
                }
                item.remove();
            });
        };

        /**
         * Update an attachment item direct properties
         * @param item
         * @param postId
         * @param filename
         * @param caption
         * @private
         */
        base._updateAttachmentItem = function (item, postId, filename, caption) {
            if (caption === undefined || caption.trim().length == 0) {
                caption = filename;
            }

            item.data('post-id', postId);
            item.data('filename', filename);
            item.children('.cuar-js-caption').html(caption);
        };

        /**
         * Change the state of an attachment item (pending, success, error, ...)
         * @param item
         * @param state
         * @private
         */
        base._updateAttachmentItemState = function (item, state) {
            item.removeClass(function (index, css) {
                return (css.match(/(^|\s)cuar-js-state-\S+/g) || []).join(' ');
            });
            item.addClass('cuar-js-state-' + state);

            var actions = item.children('.cuar-js-actions');
            var progress = item.children('.cuar-js-progress');

            switch (state) {
                case 'pending':
                    actions.hide();
                    progress.show();
                    base._updateAttachmentItemProgress(item, 0);
                    break;

                case 'error':
                    actions.show();
                    progress.hide();
                    break;

                case 'success':
                    actions.show();
                    progress.hide();
                    break;
            }

            $(document).trigger('cuar:attachmentManager:itemStateUpdated', [
                item,
                state
            ]);
        };

        /**
         * Change the progress value of an attachment item
         * @param item
         * @param progress
         * @private
         */
        base._updateAttachmentItemProgress = function (item, progress) {
            var progressElt = item.children('.cuar-js-progress');
            var indeterminateElt = progressElt.children('.indeterminate');
            var determinateElt = progressElt.children('.determinate');

            if (progress <= 0) {
                indeterminateElt.show();
                determinateElt.hide();
            } else {
                indeterminateElt.hide();
                determinateElt.show();
                determinateElt.css({'width': progress + '%'});
            }
        };

        /** Getter */
        base._showError = function (item, filename, errorMessage, isRemoveItemRequired) {
            if (item != null) {
                console.log(item);
                console.log(isRemoveItemRequired);
                if (isRemoveItemRequired) {
                    base._removeItem(item);
                } else {
                    base._updateAttachmentItemState(item, 'error');
                }
            }

            if (errorMessage != null && errorMessage.length > 0) {
                if (filename != null && filename.length > 0) errorMessage = '<strong>' + filename + '</strong> - ' + errorMessage;

                var errorItem = base._getErrorTemplate().clone();
                errorItem.find('.cuar-js-message').html(errorMessage);
                errorItem.appendTo(base.options.errorList, base.el);
                errorItem.show();
            }
        };

        base._onDismissError = function (event) {
            $(this).closest('.cuar-js-error').remove();
            event.preventDefault();
        };

        /** Getter */
        base._getErrorList = function () {
            return $(base.options.errorList, base.el);
        };

        /** Getter */
        base._getAttachmentList = function () {
            return $(base.options.attachmentList);
        };

        /** Getter */
        base._getAttachmentListEmptyMessage = function () {
            return base._getAttachmentList().find('.cuar-js-empty-message');
        };

        /** Getter */
        base._getAttachmentItems = function () {
            return base._getAttachmentList().find(base.options.attachmentItem);
            // return $(base.options.attachmentList + ' ' + base.options.attachmentItem);
        };

        /** Getter */
        base._getAttachmentItemByFilename = function (filename) {
            return base._getAttachmentItems().filter(function () {
                return $(this).data('filename') == filename;
            });
        };

        /** Getter */
        base._getAttachmentListRemoveNonce = function () {
            return $('#cuar_remove_attachment_nonce', base._getAttachmentList()).val();
        };

        /** Getter */
        base._getErrorTemplate = function () {
            return $(base.options.errorTemplate, base.el)
                .children('.cuar-js-error')
                .first();                
        };

        /** Getter */
        base._getAttachmentTemplate = function () {
            return $(base.options.attachmentItemTemplate)
                .find(base.options.attachmentItem)
                .first();
        };

        /** Getter */
        base._getSelectorInput = function () {
            return $(base.options.selectorInput, base.el);
        };

        /** Getter */
        base._getSelectors = function () {
            return $(base.options.selectorList + '>' + base.options.selectorItem, base.el);
        };

        // Make it go!
        base.init();
    };

    $.cuar.fileAttachmentManager.defaultOptions = {
        errorList: '.cuar-js-file-attachment-errors',                  // The container for the list of errors
        attachmentList: '.cuar-js-file-attachments',                   // The container for the list of attachments
        attachmentItem: '.cuar-js-file-attachment',                    // An item for the file attachment list
        attachmentItemTemplate: '.cuar-js-file-attachment-template',   // The template for new items
        errorTemplate: '.cuar-js-error-template',                      // The template for errors
        selectorList: '.cuar-js-file-selectors',                       // The container for the selectors
        selectorItem: '.cuar-js-file-selector',                        // An item for the selectors list
        selectorInput: '.cuar-js-file-selector-input',                 // An item for the selectors list
        removeAction: '.cuar-js-remove-action',
        dismissAction: '.cuar-js-dismiss'
    };

    $.fn.fileAttachmentManager = function (options) {
        return this.each(function () {
            (new $.cuar.fileAttachmentManager(this, options));
        });
    };

})(jQuery);
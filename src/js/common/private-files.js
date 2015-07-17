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
            base._getAttachmentList().on('click', '.cuar-remove-action', base._onRemoveActionClick);

            // Bind to our custom events
            $(document).on('cuar:attachmentManager:addItem', base._onAddAttachmentItem);
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
                target.fadeToggle();
            } else {
                visibleSelectors.fadeToggle("fast", function () {
                    target.fadeToggle();
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

            // Let's go to a state where we cannot do any action anymore
            base._updateAttachmentItemState(attachedItem, 'pending');

            // Post the ajax request
            $.post(
                cuar.ajaxUrl,
                {
                    'action': 'cuar_remove_attached_file',
                    'post_id': postId,
                    'filename': filename
                },
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data.length > 0) {
                            alert(response.data[0]);
                        }
                        base._updateAttachmentItemState(attachedItem, 'error');
                    } else {
                        // Ok. Remove the line
                        attachedItem.slideUp(400, function () {
                            attachedItem.remove();
                        });
                    }
                }
            );
        };

        /**
         * Callback for the event cuar:attachmentManager:addItem
         * @param event
         * @param method
         * @param postId
         * @param filename
         * @param caption
         * @private
         */
        base._onAddAttachmentItem = function (event, method, postId, filename, caption, extra) {
            var existingItem = base._getAttachmentItems().filter(function () {
                return $(this).data('filename') == filename;
            });

            if (existingItem.length==0) {
                existingItem = base._getAttachmentTemplate().clone();
                existingItem.appendTo(base.options.attachmentList);
            }

            if (caption===undefined || caption.trim().length==0) {
                caption = filename;
            }

            base._updateAttachmentItem(existingItem, postId, filename, caption);
            base._updateAttachmentItemState(existingItem, 'pending');

            // Send some Ajax
            $.post(
                cuar.ajaxUrl,
                {
                    'action': 'cuar_attach_file',
                    'method': method,
                    'post_id': postId,
                    'filename': filename,
                    'caption': caption,
                    'extra': extra
                },
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data.length > 0) {
                            alert(response.data[0]);
                        }
                        base._updateAttachmentItemState(existingItem, 'error');
                    } else {
                        base._updateAttachmentItemState(existingItem, 'success');

                        $(document).trigger('cuar:attachmentManager:fileAttached', [
                            postId,
                            filename
                        ]);
                    }
                }
            );
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
            item.data('post-id', postId);
            item.data('filename', filename);
            item.children('.cuar-caption').html(caption);
        };

        /**
         * Change the state of an attachment item (pending, success, error, ...)
         * @param item
         * @param state
         * @private
         */
        base._updateAttachmentItemState = function (item, state) {
            item.removeClass (function (index, css) {
                return (css.match (/(^|\s)cuar-state-\S+/g) || []).join(' ');
            });
            item.addClass('cuar-state-' + state);

            var actions = item.children('.cuar-actions');
            var progress = item.children('.cuar-progress');

            switch (state) {
                case 'pending':
                    actions.hide();
                    progress.show();
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
        };

        /** Getter */
        base._getAttachmentList = function () {
            return $(base.options.attachmentList, base.el);
        };

        /** Getter */
        base._getAttachmentItems = function () {
            return $(base.options.attachmentList + '>' + base.options.attachmentItem, base.el);
        };

        /** Getter */
        base._getAttachmentTemplate = function () {
            return $(base.options.attachmentItemTemplate, base.el)
                .children(base.options.attachmentItem)
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
        attachmentList: '.cuar-file-attachments',             // The container for the list of attachments
        attachmentItem: '.cuar-file-attachment',              // An item for the file attachment list
        attachmentItemTemplate: '.cuar-file-attachment-template',     // The template for new items
        selectorList: '.cuar-file-selectors',               // The container for the selectors
        selectorItem: '.cuar-file-selector',                // An item for the selectors list
        selectorInput: '.cuar-file-selector-input'                // An item for the selectors list
    };

    $.fn.fileAttachmentManager = function (options) {
        return this.each(function () {
            (new $.cuar.fileAttachmentManager(this, options));
        });
    };

})(jQuery);
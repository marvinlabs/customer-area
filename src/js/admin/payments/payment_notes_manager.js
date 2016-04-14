/*
 * 	Scripts for the private files core add-on
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.paymentNotesManager = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.paymentNotesManager", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.paymentNotesManager.defaultOptions, options);

            // Removal of items
            base.$el.on('click', '.cuar-js-remove-action', base._onRemoveActionClick);
            base.$el.on('click', '.cuar-js-add-note', base._onAddNoteButtonClick);
            base._getAddNoteMessageInput().keyup(base._onAddNoteMessageChanged);
        };

        /**
         * Submit the form when enter key is pressed
         * @param event
         * @private
         */
        base._onAddNoteMessageChanged = function (event) {
            base._updateAddNoteButtonState();
        };

        //noinspection JSUnusedLocalSymbols
        /**
         * Enable/disable the add note button
         * @param event
         * @private
         */
        base._updateAddNoteButtonState = function (event) {
            if (!base._canAddNote()) {
                base._getAddNoteButton().attr('disabled', 'disabled');
            } else {
                base._getAddNoteButton().removeAttr('disabled');
            }
        };

        /**
         * Return true if the note can really be added
         * @returns {boolean}
         * @private
         */
        base._canAddNote = function () {
            return base._getAddNoteMessage().length > 0;
        };

        /**
         * Add a note
         * @param event
         * @private
         */
        base._onAddNoteButtonClick = function (event) {
            event.preventDefault();

            var paymentId = base._getNoteList().data('payment-id');
            var message = base._getAddNoteMessage();
            var nonceValue = base._getNoteListAddNonce();

            // Post the ajax request
            var ajaxParams = {
                'action': 'cuar_add_payment_note',
                'payment_id': paymentId,
                'message': message,
                'cuar_add_payment_note_nonce': nonceValue
            };

            base._getAddNoteButton().attr('disabled', 'disabled');

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data) {
                            alert(response.data);
                        }
                        base._updateAddNoteButtonState();
                    } else {
                        // Clear form
                        base._getAddNoteMessageInput().val('');
                        base._updateAddNoteButtonState();

                        // Add item
                        var item = base._getNoteTemplate().clone();
                        base._updateNoteItem(item, response.data.id, response.data.timestamp, response.data.author, response.data.message);
                        item.insertAfter(base._getNoteList().children('.cuar-js-add-note-form'));

                        // Hide empty message
                        base._getEmptyMessage().hide();
                    }
                }
            );
        };

        /**
         * When a remove note button is clicked, send an AJAX request
         */
        base._onRemoveActionClick = function (event) {
            event.preventDefault();

            if (!confirm(cuar.confirmDeletePaymentNote)) return;

            var noteItem = $(this).closest(base.options.noteItem);
            var paymentId = base._getNoteList().data('payment-id');
            var noteId = noteItem.data('note-id');
            var nonceValue = base._getNoteListRemoveNonce();

            // Post the ajax request
            var ajaxParams = {
                'action': 'cuar_delete_payment_note',
                'payment_id': paymentId,
                'note_id': noteId,
                'cuar_delete_payment_note_nonce': nonceValue
            };

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data) {
                            alert(response.data);
                        }
                    } else {
                        // Ok. Remove the line
                        noteItem.slideUp(400, function () {
                            noteItem.remove();

                            if (base._getNoteCount() <= 0) {
                                base._getEmptyMessage().show();
                            } else {
                                base._getEmptyMessage().hide();
                            }
                        });
                    }
                }
            );
        };

        /**
         * Update an note item direct properties
         * @param item
         * @param id
         * @param timestamp
         * @param author
         * @param message
         */
        base._updateNoteItem = function (item, id, timestamp, author, message) {
            item.data('note-id', id);
            item.children('.cuar-js-timestamp').html(timestamp);
            item.children('.cuar-js-author').html(author);
            item.children('.cuar-js-message').html(message);
        };

        /** Getter */
        base._getNoteCount = function () {
            var notes = base._getNoteList()
                .children(base.options.noteItem)
                .not(base.options.addNoteForm)
                .not(base.options.emptyMessage)
                .not(base.options.noteItemTemplate);

            return notes.length;
        };

        /** Getter */
        base._getEmptyMessage = function () {
            return $(base.options.emptyMessage, base.el);
        };

        /** Getter */
        base._getNoteList = function () {
            return $(base.options.noteList, base.el);
        };

        /** Getter */
        base._getAddNoteMessage = function () {
            return $('.cuar-js-new-note-message', base.el).val();
        };

        /** Getter */
        base._getAddNoteMessageInput = function () {
            return $('.cuar-js-new-note-message', base.el);
        };

        /** Getter */
        base._getAddNoteButton = function () {
            return $('.cuar-js-add-note', base.el);
        };

        /** Getter */
        base._getNoteListEmptyMessage = function () {
            return $('.cuar-js-empty-message', base.el);
        };

        /** Getter */
        base._getNoteListAddNonce = function () {
            return $('#cuar_add_note_nonce', base.el).val();
        };

        /** Getter */
        base._getNoteListRemoveNonce = function () {
            return $('#cuar_remove_note_nonce', base.el).val();
        };

        /** Getter */
        base._getNoteTemplate = function () {
            return $(base.options.noteItemTemplate, base.el)
                .children(base.options.noteItem)
                .first();
        };

        /** Getter */
        base._getNoteForm = function () {
            return $(base.options.addNoteForm, base.el)
                .children(base.options.noteItem)
                .first();
        };

        // Make it go!
        base.init();
    };

    $.cuar.paymentNotesManager.defaultOptions = {
        noteList: '.cuar-js-payment-notes',                   // The container for the list of notes
        noteItem: '.cuar-js-payment-note',                    // An item for the file note list
        noteItemTemplate: '.cuar-js-payment-note-template',   // The template for new items
        addNoteForm: '.cuar-js-add-note-form',
        emptyMessage: '.cuar-js-empty-message'
    };

    $.fn.paymentNotesManager = function (options) {
        return this.each(function () {
            (new $.cuar.paymentNotesManager(this, options));
        });
    };

})(jQuery);
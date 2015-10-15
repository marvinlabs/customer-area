/*
 * 	Scripts to handle the billing address fields
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.mediaInputControl = function (el, options) {
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Are we busy
        base.isBusy = false;
        base.fileFrame = null;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.mediaInputControl", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.mediaInputControl.defaultOptions, options);

            base.$el.on('click', '.cuar-upload-button', base._onUploadButtonClicked);
        };

        /**
         * When country is changed, we should refresh the state control with that country's states
         */
        base._onUploadButtonClicked = function (e) {
            e.preventDefault();

            var button = $(this);

            // If the media frame already exists, reopen it.
            if ( base.fileFrame ) {
                base.fileFrame.open();
                return;
            }

            // Create the media frame.
            base.fileFrame = wp.media.frames.file_frame = wp.media({
                frame: 'post',
                state: 'insert',
                title: button.data( 'uploader_title' ),
                button: {
                    text: button.data( 'uploader_button_text' )
                },
                multiple: false
            });

            base.fileFrame.on( 'menu:render:default', function( view ) {
                // Store our views in an object.
                var views = {};

                // Unset default menu items
                view.unset( 'library-separator' );
                view.unset( 'gallery' );
                view.unset( 'featured-image' );
                view.unset( 'embed' );

                // Initialize the views in our view object.
                view.set( views );
            } );

            // When an image is selected, run a callback.
            base.fileFrame.on( 'insert', function() {
                var selection = base.fileFrame.state().get('selection');
                selection.each( function( attachment, index ) {
                    attachment = attachment.toJSON();
                    base._getUrlInputField().val(attachment.url);
                });
            });

            // Finally, open the modal
            base.fileFrame.open();
        };

        /** Getter */
        base._getUrlInputField = function () {
            return $(base.options.urlInputSelector, base.el);
        };

        // Make it go!
        base.init();
    };

    $.cuar.mediaInputControl.defaultOptions = {
        urlInputSelector: '.cuar-upload-input'
    };

    $.fn.mediaInputControl = function (options) {
        return this.each(function () {
            (new $.cuar.mediaInputControl(this, options));
        });
    };

})(jQuery);

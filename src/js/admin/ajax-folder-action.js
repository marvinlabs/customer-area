/*
 * 	Scripts for operations on folders
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.ajaxFolderAction = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.ajaxFolderAction", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.ajaxFolderAction.defaultOptions, options);

            // Hide the file input but do not disable it fully
            base._getButton().click(base._onButtonClicked);
        };

        base._onButtonClicked = function (event) {
            event.preventDefault();

            // Send some Ajax
            var ajaxParams = {
                'action': 'cuar_folder_action',
                'folder_action': base._getAction(),
                'path': base._getPath(),
                'extra': base._getExtra()
            };

            base.$el.css('opacity', '0.5');
            base._getButton().bind('click', function(e){ e.preventDefault(); });

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    base.$el.css('opacity', '1');

                    // Not ok. Alert
                    if (response.success == false) {
                        if (response.data!=null && response.data.length > 0) {
                            alert(response.data);
                        }
                        base._getButton().click(base._onButtonClicked);
                    } else {
                        var msg = base._getSuccessMessage();
                        if (msg!=null && msg.length>0) {
                            base.$el
                                .removeClass('cuar-error')
                                .addClass('cuar-info')
                                .html(msg);
                        } else {
                            base.$el.remove();
                        }
                    }
                }
            );
        }

        /** Getter */
        base._getButton = function () {
            return $('.button', base.el);
        };

        /** Getter */
        base._getPath = function () {
            return base.$el.data('path');
        };

        /** Getter */
        base._getAction = function () {
            return base.$el.data('action');
        };

        /** Getter */
        base._getExtra = function () {
            return base.$el.data('extra');
        };

        /** Getter */
        base._getSuccessMessage = function () {
            return base.$el.data('success-message');
        };

        // Make it go!
        base.init();
    };

    $.fn.ajaxFolderAction = function (options) {
        return this.each(function () {
            (new $.cuar.ajaxFolderAction(this, options));
        });
    };

})(jQuery);

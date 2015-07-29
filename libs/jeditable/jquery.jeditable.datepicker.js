/*
 * Datepicker for Jeditable
 *
 * Copyright (c) 2011 Piotr 'Qertoip' W?odarek
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Depends on jQuery UI Datepicker
 *
 * Project home:
 *   http://github.com/qertoip/jeditable-datepicker
 *
 */

// add :focus selector

jQuery.expr[':'].focus = function (elem) {
    return elem === document.activeElement && ( elem.type || elem.href );
};

(function ($) {
    $.editable.addInputType('datepicker', {

        /* set the date using the data attribute */
        content: function (string, settings, original) {
            var rawDate = $(original).data('raw-date');

            // Convert to the human date format using the settings provided
            var datepickerDefaults = {
                altFormat: 'yy-mm-dd',
            };
            if (settings.datepicker) {
                datepickerDefaults = jQuery.extend({}, datepickerDefaults, settings.datepicker);
            }

            var parsedDate = $.datepicker.parseDate(datepickerDefaults.altFormat, rawDate, datepickerDefaults);
            var formattedDate = $.datepicker.formatDate(datepickerDefaults.dateFormat, parsedDate, datepickerDefaults);

            this.find('.formatted-input').val(formattedDate);
            this.find('.raw-input').val(rawDate);
        },

        /* Set the data attribute from the raw format */
        submit: function (settings, original) {
            var rawDate = this.find('.raw-input').val();
            $(original).data('raw-date', rawDate);
        },

        /* create input element */
        element: function (settings, original) {
            var form = $(this);

            var hidden = $('<input />');
            hidden.attr('type', 'hidden');
            hidden.addClass('raw-input');
            form.append(hidden);

            var input = $('<input />');
            input.attr('autocomplete', 'off');
            input.addClass('formatted-input');
            form.append(input);

            return hidden;
        },

        /* attach jquery.ui.datepicker to the input element */
        plugin: function (settings, original) {
            var form = this;
            var input = form.find(".formatted-input");

            // Don't cancel inline editing onblur to allow clicking datepicker
            settings.onblur = 'nothing';

            var datepickerDefaults = {
                altField: $(".raw-input", this),
                altFormat: 'yy-mm-dd',
                showButtonPanel: true,
                onSelect: function () {
                    // clicking specific day in the calendar should
                    // submit the form and close the input field
                    form.submit();
                },

                onClose: function () {
                    var forceSubmit = false;
                    var event = arguments.callee.caller.caller.arguments[0];
                    // If "Clear" gets clicked, then really clear it
                    if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
                        $(this).val('');
                        $('.raw-input', original).val('');
                        forceSubmit = true;
                    }

                    setTimeout(function () {
                        if (forceSubmit) {
                            form.submit();
                        } else if (!input.is(':focus')) {
                            // input has NO focus after 150ms which means
                            // calendar was closed due to click outside of it
                            // so let's close the input field without saving
                            original.reset(form);
                        } else {
                            // input still HAS focus after 150ms which means
                            // calendar was closed due to Enter in the input field
                            // so lets submit the form and close the input field
                            form.submit();
                        }

                        // the delay is necessary; calendar must be already
                        // closed for the above :focus checking to work properly;
                        // without a delay the form is submitted in all scenarios, which is wrong
                    }, 250);
                }
            };

            if (settings.datepicker) {
                datepickerDefaults = jQuery.extend({}, datepickerDefaults, settings.datepicker);
            }

            input.datepicker(datepickerDefaults);
        }
    });

})(jQuery);
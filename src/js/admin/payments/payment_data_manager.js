/*
 * 	Scripts to handle the payment data fields
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.paymentDataManager = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.paymentDataManager", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.paymentDataManager.defaultOptions, options);

            var datePickerHolder = $(".cuar-datepicker-control input[type=hidden]", base.el);
            var datePickerInput = $(".cuar-datepicker-control .cuar-datepicker-input", base.el);
            datePickerInput.datepicker({
                dateFormat: base.options.datePickerOptions.dateFormat,
                altFormat: base.options.rawDateFormat,
                altField: datePickerHolder
            });

            datePickerInput.datepicker('setDate', base._formatDate(datePickerHolder.val()));
            datePickerInput.val(base._formatDate(datePickerHolder.val()));
        };

        /**
         * Format a raw date
         * @param rawDate
         * @returns {*}
         * @private
         */
        base._formatDate = function(rawDate)
        {
            var parsedDate = $.datepicker.parseDate(base.options.rawDateFormat, rawDate , base.options.datePickerOptions);
            return $.datepicker.formatDate(base.options.datePickerOptions.dateFormat, parsedDate , base.options.datePickerOptions);
        };

        // Make it go!
        base.init();
    };

    $.cuar.paymentDataManager.defaultOptions = {
        rawDateFormat: 'yy-mm-dd',
        datePickerOptions: {
            dateFormat: cuar.datepickerDateFormat,
            closeText: cuar.datepickerCloseText,
            currentText: cuar.datepickerCurrentText,
            monthNames: cuar.datepickerMonthNames,
            monthNamesShort: cuar.datepickerMonthNamesShort,
            monthStatus: cuar.datepickerMonthStatus,
            dayNames: cuar.datepickerDayNames,
            dayNamesShort: cuar.datepickerDayNamesShort,
            dayNamesMin: cuar.datepickerDayNamesMin,
            firstDay: cuar.datepickerFirstDay,
            isRTL: cuar.datepickerIsRTL
        }
    };

    $.fn.paymentDataManager = function (options) {
        return this.each(function () {
            (new $.cuar.paymentDataManager(this, options));
        });
    };

})(jQuery);

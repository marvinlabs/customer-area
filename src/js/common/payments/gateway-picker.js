/*
 * 	Scripts for the private files core add-on
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.gatewayPicker = function (el, options) {

        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.gatewayPicker", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.gatewayPicker.defaultOptions, options);

            // Attach event handler to button click
            base._getSelectors().on('change', base._onGatewaySelected);
        };

        base._onGatewaySelected = function(event) {
            event.preventDefault();

            var selected = $(this).data('gateway');
            base._getGatewayForms().hide(0);
            base._getGatewayForm(selected).show(0);
        };

        /** Getter */
        base._getGatewayForms = function (gateway) {
            return $(base.options.gatewayForm, base.el);
        };

        /** Getter */
        base._getGatewayForm = function (gateway) {
            return $(base.options.gatewayForm + "[data-gateway=" + gateway + "]", base.el);
        };

        /** Getter */
        base._getSelectors = function () {
            return $(base.options.gatewaySelector, base.el);
        };

        // Make it go!
        base.init();
    };

    $.cuar.gatewayPicker.defaultOptions = {
        gatewaySelector: '.cuar-js-gateway-selector',   // What allows us to select a gateway
        gatewayForm: '.cuar-js-gateway-form',           // Form corresponding to a gateway
    };

    $.fn.gatewayPicker = function (options) {
        return this.each(function () {
            (new $.cuar.gatewayPicker(this, options));
        });
    };

})(jQuery);

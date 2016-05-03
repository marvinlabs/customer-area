/*
 * 	Scripts to handle the billing address fields
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.bindCountryStateInputs = function (el, options) {
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Are we busy
        base.isBusy = false;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.bindCountryStateInputs", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.bindCountryStateInputs.defaultOptions, options);

            // Select2 should be enabled
            base._getCountryField().select2(base.options.select2);
            base._getStateField().select2(base.options.select2);

            // Do something when country is updated
            base._getCountryField().on('change', base._onCountryChanged);
        };

        /**
         * When country is changed, we should refresh the state control with that country's states
         */
        base._onCountryChanged = function () {
            var countryField = base._getCountryField();
            var stateField = base._getStateField();
            var stateFieldGroup = base._getStateFieldContainer();

            var selectedCountry = countryField.val();
            if (selectedCountry==null || selectedCountry.length == '' || (typeof selectedCountry == 'undefined')) {
                stateFieldGroup.hide();
                return;
            }

            var ajaxParams = {
                'action': 'cuar_get_country_states',
                'country': selectedCountry,
                'address_id': base._getAddressId(),
                'cuar_nonce': base._getNonce()
            };

            base._setAddressBusy(true);

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    base._setAddressBusy(false);
                    if (response.success == false) {
                        alert(response.data);
                        return;
                    }

                    if (response.data.states != null) {
                        stateField.html(response.data.htmlOptions).select2(base.options.select2);
                        stateField.val(stateField.data('pending-value')).trigger('change');
                        stateFieldGroup.show();
                    } else {
                        stateField.val('').trigger('change');
                        stateFieldGroup.hide();
                    }
                    stateField.data('pending-value', '');
                }
            );
        };

        /** Is the address disabled */
        base._isAddressBusy = function () {
            return base._getFieldContainer().triggerHandler('cuar:address:setBusy');
        };

        /** Set the address as disabled for some time */
        base._setAddressBusy = function (isBusy) {
            base._getFieldContainer().trigger('cuar:address:setBusy', [isBusy]);
        };

        /** The control we belong to */
        base._getFieldContainer = function () {
            return base.$el.parents(base.options.fieldContainer);
        };

        /** Getter */
        base._getNonce = function () {
            return base._getFieldContainer().triggerHandler('cuar:address:getNonce');
        };

        /** Getter */
        base._getAddressId = function () {
            return base._getFieldContainer().triggerHandler('cuar:address:getAddressId');
        };

        /** Getter */
        base._getCountryField = function () {
            return $(base.options.countrySelector, base.el).find('select');
        };

        /** Getter */
        base._getStateFieldContainer = function () {
            return $(base.options.stateSelector, base.el);
        };

        /** Getter */
        base._getStateField = function () {
            return $(base.options.stateSelector, base.el).find('select');
        };

        // Make it go!
        base.init();
    };

    $.cuar.bindCountryStateInputs.defaultOptions = {
        fieldContainer: '.cuar-js-address',
        countrySelector: '.cuar-js-address-country',
        stateSelector: '.cuar-js-address-state',
        select2: {
            width: '100%',
            allowClear: true,
            placeholder: "",
            dropdownParent: $('body').hasClass('wp-admin') ? $('body') : $('#cuar-js-content-container')
        }
    };

    $.fn.bindCountryStateInputs = function (options) {
        return this.each(function () {
            (new $.cuar.bindCountryStateInputs(this, options));
        });
    };

})(jQuery);

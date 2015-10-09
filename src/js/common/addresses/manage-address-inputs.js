/*
 * 	Scripts to handle the billing address fields
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.manageAddressInputs = function (el, options) {

        var base = this;

        // Address fields we have to handle
        base.fields = ['name', 'company', 'line1', 'line2', 'zip', 'city', 'country', 'state'];
        base.isBusy = false;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.manageAddressInputs", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.manageAddressInputs.defaultOptions, options);

            base.$el.on('cuar:address:clear', base._onClearAddress);
            base.$el.on('cuar:address:set', base._onSetAddress);
            base.$el.on('cuar:address:get', base._onGetAddress);
            base.$el.on('cuar:address:getNonce', base._onGetNonce);
            base.$el.on('cuar:address:isEmpty', base._onIsAddressEmpty);
            base.$el.on('cuar:address:setBusy', base._onSetBusy);
            base.$el.on('cuar:address:isBusy', base._onIsBusy);

            base.$el.on('click', '.cuar-action.cuar-reset', base._onResetAddressAction);
        };

        /**
         * Handle the default action to reset the fields
         * @param event
         * @returns {boolean}
         * @private
         */
        base._onResetAddressAction = function (event) {
            event.preventDefault();
            if (base._onIsBusy(null)) return false;

            if (!confirm(cuar.addressConfirmResetAddress)) return false;

            base._onClearAddress(null);
        };

        /**
         * Clear the address fields
         * @param event
         * @private
         */
        base._onClearAddress = function (event) {
            base._onSetAddress(null, {
                'name': '',
                'company': '',
                'line1': '',
                'line2': '',
                'zip': '',
                'city': '',
                'country': '',
                'state': ''
            });
        };

        /**
         * Are controls disabled?
         *
         * @param isBusy
         * @private
         */
        base._onIsBusy = function (event) {
            return base.isBusy;
        };

        /**
         * Set controls as disabled or enabled
         *
         * @param isBusy
         * @private
         */
        base._onSetBusy = function (event, isBusy) {
            base.isBusy = isBusy;

            if (isBusy) {
                base._getProgressIndicator().show();
                base._getActions().addClass('button-disabled');
                base._getInputFields().attr('disabled', 'disabled');
                base._getInputContainers().addClass('disabled');
            } else {
                base._getProgressIndicator().hide();
                base._getActions().removeClass('button-disabled');
                base._getInputFields().removeAttr('disabled');
                base._getInputContainers().removeClass('disabled');
            }
        };

        /** Getter */
        base._onIsAddressEmpty = function (event) {
            for (var i = 0; i < base.fields.length; i++) {
                var field = base.fields[i];
                var value = base._getField(field).val();
                if (value.length > 0) return false;
            }

            return true;
        };

        /** Getter */
        base._onGetAddress = function (event) {
            var address = {};

            for (var i = 0; i < base.fields.length; i++) {
                var field = base.fields[i];
                address[field] = base._getField(field).val();
            }

            return address;
        };

        /** Getter */
        base._onSetAddress = function (event, address) {
            for (var i = 0; i < base.fields.length; i++) {
                var field = base.fields[i];
                var elt = base._getField(field);
                if (field == 'country') {
                    elt.select2('val', address[field]);
                    elt.change();
                } else if (field == 'state') {
                    elt.select2('val', address[field]);
                    elt.data('pending-value', address[field]);
                } else {
                    elt.val(address[field]);
                }
            }
        };

        /** Getter */
        base._onGetNonce = function () {
            return $('input[name=cuar_nonce]', base.el).val();
        };

        /** Getter */
        base._getField = function (name) {
            return $('.cuar-address-' + name, base.el).find('.cuar-address-field:input');
        };

        /** Getter */
        base._getActions = function () {
            return $('.cuar-action', base.el);
        };

        /** Getter */
        base._getProgressIndicator = function () {
            return $('.cuar-progress', base.el);
        };

        /** Getter */
        base._getInputFields = function () {
            return $('.form-control', base.el);
        };

        /** Getter */
        base._getInputContainers = function () {
            return $('.form-group', base.el);
        };

        // Make it go!
        base.init();
    };

    $.cuar.manageAddressInputs.defaultOptions = {};

    $.fn.manageAddressInputs = function (options) {
        return this.each(function () {
            (new $.cuar.manageAddressInputs(this, options));
        });
    };

})(jQuery);

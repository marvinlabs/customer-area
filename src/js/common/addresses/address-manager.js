/*
 * 	Scripts to handle the billing address fields
 *  By Vincent Mimoun-Prat / MarvinLabs (www.marvinlabs.com)
 *  Released under GPL License
 */
(function ($) {
    if (!$.cuar) {
        $.cuar = {};
    }

    $.cuar.addressManager = function (el, options) {

        var base = this;

        // Address fields we have to handle
        base.fields = ['name', 'company', 'line1', 'line2', 'zip', 'city', 'country', 'state'];
        base.isBusy = false;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Add a reverse reference to the DOM object
        base.$el.data("cuar.addressManager", base);

        /**
         * Initialisation
         */
        base.init = function () {
            // Merge default options
            base.options = $.extend({}, $.cuar.addressManager.defaultOptions, options);

            base.$el.on('cuar:address:clear', base._onClearAddress);
            base.$el.on('cuar:address:set', base._onSetAddress);
            base.$el.on('cuar:address:get', base._onGetAddress);
            base.$el.on('cuar:address:getNonce', base._onGetNonce);
            base.$el.on('cuar:address:getAddressId', base._onGetAddressId);
            base.$el.on('cuar:address:isEmpty', base._onIsAddressEmpty);
            base.$el.on('cuar:address:setBusy', base._onSetBusy);
            base.$el.on('cuar:address:isBusy', base._onIsBusy);
            base.$el.on('cuar:address:loadFromOwner', base._onLoadAddressFromOwner);
            base.$el.on('cuar:address:saveForOwner', base._onSaveAddressForOwner);

            base.$el.on('click', '.cuar-action.cuar-reset', base._onResetAddressAction);
        };

        /**
         * Load the address from an owner preferences
         * @param event
         * @param owner
         * @returns {boolean}
         * @private
         */
        base._onLoadAddressFromOwner = function (event, owner) {
            if (base._onIsBusy()) return false;

            if (owner == null) {
                alert(cuar.addressMustPickOwnerFirst);
                return;
            }

            var isAddressEmpty = base._onIsAddressEmpty();
            if (!isAddressEmpty && !confirm(cuar.addressConfirmLoadAddressFromOwner)) return false;

            var ajaxParams = {
                'action': 'cuar_load_address_from_owner',
                'cuar_nonce': base._onGetNonce(),
                'owner': owner,
                'address_id': base._onGetAddressId()
            };

            base._onSetBusy(null, true);

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    base._onSetBusy(null, false);

                    if (response.success == false) {
                        alert(response.data);
                        return;
                    }

                    if (response.data.address != null) {
                        base._onSetAddress(null, response.data.address);
                    } else {
                        alert(cuar.addressNoAddressFromOwner);
                    }
                }
            );
        };

        /**
         * Save the address to the owner preferences
         * @param event
         * @param owner
         * @returns {boolean}
         * @private
         */
        base._onSaveAddressForOwner = function (event, owner) {
            if (base._onIsBusy()) return false;

            if (owner == null) {
                alert(cuar.addressMustPickOwnerFirst);
                return;
            }

            if (!confirm(cuar.addressConfirmSaveAddressForOwner)) return false;

            var ajaxParams = {
                'action': 'cuar_save_address_for_owner',
                'cuar_nonce': base._onGetNonce(),
                'owner': owner,
                'address_id': base._onGetAddressId(),
                'address': base._onGetAddress()
            };

            base._onSetBusy(null, true);

            $.post(
                cuar.ajaxUrl,
                ajaxParams,
                function (response) {
                    base._onSetBusy(null, false);

                    if (response.success == false) {
                        alert(response.data);
                        return;
                    }
                }
            );
        };

        /**
         * Handle the default action to reset the fields
         * @param event
         * @returns {boolean}
         * @private
         */
        base._onResetAddressAction = function (event) {
            event.preventDefault();
            if (base._onIsBusy()) return false;

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
                if (value!=null && value.length > 0) return false;
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
        base._onGetAddressId = function () {
            return base.$el.data('address-id');
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

    $.cuar.addressManager.defaultOptions = {};

    $.fn.addressManager = function (options) {
        return this.each(function () {
            (new $.cuar.addressManager(this, options));
        });
    };

})(jQuery);

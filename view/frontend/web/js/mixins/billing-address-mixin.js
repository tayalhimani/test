define([
    'jquery',
    'Magento_Checkout/js/model/quote',
],
    function($, quote){
        'use strict';
        return function(BillingAddress) {
            return BillingAddress.extend({
               /**
                 * @return {exports.initObservable}
                 */
                initObservable: function () {
                    this._super();
                    quote.billingAddress.subscribe(function (newAddress) {
                        if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                            var postCodePrefix = window.checkoutConfig.prefix_postal_code;
                            var postCode = newAddress.postcode;
                            if (postCodePrefix && postCode && postCode.indexOf(postCodePrefix) === -1) {
                                newAddress.postcode = postCodePrefix + '-' + postCode;
                            }
                            this.saveInAddressBook(newAddress.saveInAddressBook);
                        } else {
                            this.saveInAddressBook(1);
                        }
                        this.isAddressDetailsVisible(true);
                    }, this);
                    return this;
                },

                /**
                 * Update address action
                 */
                updateAddress: function () {
                    var addressData = this.source.get(this.dataScopePrefix);

                    var postCodePrefix = window.checkoutConfig.prefix_postal_code;
                    var postCode = addressData.postcode;
                    if (postCodePrefix && postCode && postCode.indexOf(postCodePrefix) === -1) {
                        addressData.postcode = postCodePrefix + '-' + postCode;
                    }
                    this.source.set(this.dataScopePrefix, addressData);
                    this._super();
                }
            });
        }
});

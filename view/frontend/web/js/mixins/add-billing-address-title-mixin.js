define(['jquery'], function($){
    'use strict';
    return function(BillingAddress){
        return BillingAddress.extend({
            billingTitleEnabled: window.checkoutConfig.billingTitleEnabled,
            billingTitle: window.checkoutConfig.billingTitle,
            telephonePrefix: window.checkoutConfig.dialcode['dialcode'],
            dialcodePrefixEnabled: window.checkoutConfig.dialCodeEnabled,
            postalCodePrefix: window.checkoutConfig.prefix_postal_code
        });
    };
});

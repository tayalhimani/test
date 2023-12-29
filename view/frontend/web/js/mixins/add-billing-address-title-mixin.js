define(['jquery'], function($){
    'use strict';
    return function(BillingAddress){
        return BillingAddress.extend({
            billingTitleEnabled: window.checkoutConfig.billingTitleEnabled,
            billingTitle: window.checkoutConfig.billingTitle
        });
    };
});

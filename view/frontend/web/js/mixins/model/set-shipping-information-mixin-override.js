/**
 * Copyright © Vaimo Group. All rights reserved.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Adyen_Payment/js/model/adyen-payment-service',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($,
    wrapper,
    adyenPaymentService,
    fullScreenLoader
) {
    'use strict';

    return function (shippingInformationAction) {

        return wrapper.wrap(shippingInformationAction, function (originalAction) {
            return originalAction().then(function (result) {
                return result;
            });
        });

    };
});

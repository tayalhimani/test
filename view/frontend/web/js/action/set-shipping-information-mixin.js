define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function ($, wrapper, quote, shippingFields) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, container) {

            var shippingAddress = quote.shippingAddress(),
                shippingCity = $("#shipping-new-address-form [name = 'city'] option:selected"),
                shippingCityValue = shippingCity.text();
            if (shippingCityValue) {
                shippingAddress.city = shippingCityValue;
            }

            return originalAction(container);
        });
    };
});
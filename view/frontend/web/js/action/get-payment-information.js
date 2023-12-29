/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/payment-service'
], function ($, quote, urlBuilder, storage, errorProcessor, customer, methodConverter, paymentService) {
    'use strict';

    return function (deferred, messageContainer) {
        var serviceUrl;

        deferred = deferred || $.Deferred();
        if(window.checkoutConfig.paymentTypesAvailablePushDataLayer) {
            if (dataLayer === undefined) var dataLayer = [];
        }

        /**
         * Checkout for guest and registered customer.
         */
        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/payment-information', {
                cartId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
        }

        return storage.get(
            serviceUrl, false
        ).done(function (response) {
            quote.setTotals(response.totals);
            paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
            deferred.resolve();

            if(window.checkoutConfig.paymentTypesAvailablePushDataLayer) {
                //pushing "paymentTypesAvailable" to datalayer - start
                var index = 0;
                var titles = [];
                $.each(response['payment_methods'], function (index, paymentMethod) {
                    titles.push(paymentMethod.title);
                });
                $.each(window.dataLayer, function (i) {
                    if (window.dataLayer[i]['checkout'] != undefined && window.dataLayer[i]['checkout']['paymentTypesAvailable'] != undefined) {
                        index = i;
                    }
                });
                if (index != 0) {
                    var paymentTypesAvailable = [];
                    _.each(window.dataLayer[index]['checkout']['paymentTypesAvailable'], function (name) {
                        paymentTypesAvailable.push(name);
                    });

                    if (JSON.stringify(paymentTypesAvailable) != JSON.stringify(titles)) {
                        window.dataLayer.push({checkout: {"paymentTypesAvailable": titles}});
                    }
                }
                //pushing "paymentTypesAvailable" to datalayer - end
            }

        }).fail(function (response) {
            errorProcessor.process(response, messageContainer);
            deferred.reject();
        });
    };
});

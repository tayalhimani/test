/**
 * @api
 */

define([
    'jquery',
    'uiComponent',
    'mage/url',
], function ($, Component, url) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Dyson_SinglePageCheckout/checkout/summary/edit-order'
        },
        baseUrl:  url.build('checkout/cart'),
        goToCart: "onclick=location.href='"+checkoutConfig.cartUrl+"'",
    });
});

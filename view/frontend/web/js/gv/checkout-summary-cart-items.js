/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/view/summary/cart-items'
    ],
    function (ko, totals, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Dyson_SinglePageCheckout/gv/checkout-summary-cart-items'
            },

        });
    }
);

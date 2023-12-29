/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/grand-total',
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Dyson_AmastyCheckoutExtension/gv/checkout-summary-grand-total'
        }
    });
});

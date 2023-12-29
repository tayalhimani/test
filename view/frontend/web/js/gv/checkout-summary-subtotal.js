/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/subtotal',
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Dyson_AmastyCheckoutExtension/gv/checkout-summary-subtotal'
        }
    });
});

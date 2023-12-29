/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/view/summary/cart-items',
    ],
    function (ko, totals, quote, priceUtils, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Dyson_AmastyCheckoutExtension/gv/checkout-summary-cart-items'
            },
            checkoutItemsCount: ko.observable(parseFloat(totals.totals['items_qty'])),

            initialize: function() {
                this._super();
                this.template = 'Dyson_AmastyCheckoutExtension/gv/checkout-summary-cart-items';

                quote.totals.subscribe(function(quoteData){this.checkoutItemsCount(quoteData.items_qty)}.bind(this));
            },
            getSubTotal: function(){
                    if (totals.totals()) {
                    var subtotal = parseFloat(totals.totals()['base_grand_total']);
                    return priceUtils.formatPrice(subtotal);
                }
            },
        });
    }
);

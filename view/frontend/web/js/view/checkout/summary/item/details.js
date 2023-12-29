define([
    'jquery',
    'Amasty_CheckoutCore/js/view/checkout/summary/item/details',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
], function ($, Component, quote, priceUtils) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Dyson_SinglePageCheckout/checkout/summary/item/details'
        },
        initialize: function() {
          this._super();
        },

        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        /**
         * @modification: get shipping text from checkoutConfig
         *
         * @param {Object} quoteItem
         * @return {String}
         */
        getShippingText: function (quoteItem) {

            var shipping_text = '';
            var quoteItemData = window.checkoutConfig.quoteItemData;
            $.each(quoteItemData, function(key) {
                if (quoteItemData.length > 0) {
                    if (key in quoteItemData) {
                        if (quoteItemData[key].item_id == quoteItem.item_id) {
                            shipping_text = quoteItemData[key].shipping_text
                        }
                    }
                }
            });
            return shipping_text;
        }

    });
});

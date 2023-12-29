/**
 * Checkout Summary item Details view.
 */
 define([
     'jquery',
     'Magento_Checkout/js/model/quote',
     'Magento_Catalog/js/price-utils'
 ],
 function($, quote, priceUtils) {
     'use strict';

     return function(CheckoutSummaryItemDetails){

         return CheckoutSummaryItemDetails.extend({
             isShowBvIdTag: window.checkoutConfig.isShowBvIdTag,

             initialize: function() {
                 this._super();
                 this.template = 'Dyson_SinglePageCheckout/checkout/summary/item/details';
             },

             getFormattedPrice: function (price) {
                 let currencyFormatEnabled = window.checkoutConfig.currencyFormatEnabled;
                 if(currencyFormatEnabled){
                     /* To format the price correctly (dots and commas) */
                     return priceUtils.formatPriceLocale(price, quote.getPriceFormat());
                 } else {
                     return priceUtils.formatPrice(price, quote.getPriceFormat());
                 }
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
             },

             /**
              * get bvId text from checkoutConfig
              *
              * @param {Object} quoteItem
              * @return {String}
              */
             getBvProductId: function (quoteItem) {
                 var bvId = '';
                 var quoteItemData = window.checkoutConfig.quoteItemData;
                 $.each(quoteItemData, function(key) {
                     if (quoteItemData.length > 0) {
                         if (key in quoteItemData) {
                             if (quoteItemData[key].item_id == quoteItem.item_id) {
                                 bvId = quoteItemData[key].bv_product_id;
                             }
                         }
                     }
                 });
                 return bvId;
             }
         });
     };
 });

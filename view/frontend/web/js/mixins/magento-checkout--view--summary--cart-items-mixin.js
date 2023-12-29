define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/lib/view/utils/dom-observer'
],
function($, ko, totals, priceUtils, quote, $do) {
    'use strict';

    $(document).ready(function() {
        $(document).on('click keypress', '.checkout-block__summary__header', function(){
            $('.am-opc-summary').toggleClass('am-opc-summary--open')
            if( $('.am-opc-summary').hasClass('am-opc-summary--open') ) {
                $('.checkout-block__summary__header').attr("aria-expanded","true");
            }
            else {
                $('.checkout-block__summary__header').attr("aria-expanded","false");
            }
        });
        $do.get('.am-opc-summary', function(elem){
            if ($(window).width() < 1024) {
                if($('.am-opc-summary') && $('.am-opc-main') ){
                    let summary = $('.am-opc-summary').detach();
                    summary.insertBefore('.am-opc-main');
                }
            }
        });
        $(window).on('resize', function () {
            if ($(window).width() < 1024) {
                let summary = $('.am-opc-summary').detach();
                summary.insertBefore('.am-opc-main');
            }
            else {
                let summary = $('.am-opc-summary').detach();
                summary.insertAfter('.am-opc-main');
            }
        });
    });

    return function(CheckoutSummaryCartItems){

        return CheckoutSummaryCartItems.extend({
            checkoutItemsCount: ko.observable(parseFloat(totals.totals['items_qty'])),
            initialize: function() {
                this._super();
                this.template = 'Dyson_SinglePageCheckout/checkout/summary/cart-items';

                // Please pardon this jquery.
                var siteHeaderHeight = $('header.page-header').outerHeight();
                $(window).scroll(function() {
                    if ($(this).scrollTop() > siteHeaderHeight) {
                        $('.am-opc-summary').addClass('am-opc-summary--stick');
                    } else {
                        $('.am-opc-summary').removeClass('am-opc-summary--stick');
                    }
                });
                quote.totals.subscribe(function(quoteData){this.checkoutItemsCount(quoteData.items_qty)}.bind(this));
            },
            getSubTotal: function(){
                if (totals.totals()) {
                    var subtotal = parseFloat(totals.totals()['base_grand_total']);
                    return priceUtils.formatPrice(subtotal);
                }
            },
            toggleCartOpen: function() {
                // Toggle class on el to trigger draw open/show.
                $('.am-opc-summary').toggleClass('am-opc-summary--open');
            }
        });
    };
});

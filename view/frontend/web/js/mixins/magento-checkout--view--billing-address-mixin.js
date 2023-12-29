define(['jquery'],
function($){
    'use strict';

    return function(BillingAddress){
        return BillingAddress.extend({
            /**
             * Init component
             */
            initialize: function () {
                this._super();
                this.template = 'Dyson_SinglePageCheckout/checkout/billing-address';
            },

            updateAddress: function() {
                //add code for MP-15833
                if(!jQuery('.payment-method-title.choice.payment-method-title--active').parent().parent().hasClass('allow active'))
                {
                    jQuery('.payment-method-title.choice.payment-method-title--active').parent().parent().addClass('allow active');
                }
                this._super();

                // If form aint validationed then move focus to the first
                // invalid input.
                if (this.source.get('params.invalid')) {
                    $('.checkout-billing-address .field._error').first().find('input, select, textarea').focus();
                }
            }
        });
    };
});

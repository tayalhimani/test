define(
    [
        'Magento_Ui/js/form/element/abstract',
        'uiRegistry',
        'jquery',
        'ko',
        'mage/validation'
    ],
    function (
        Component,
        registry,
        $,
        ko,
        validation
    ) {
        'use strict';

        if (window.checkoutConfig.defaultCountryId == "SG" ) {
            $("body").on("focusout",".form-shipping-address .input-label-overlay span",function(){
                let updated_dial =  $(this).text();
                let custom_attributes_dialcode = $(".form-shipping-address").find("div[name='shippingAddress.custom_attributes.dialcode'] input[name='custom_attributes[dialcode]']");

                custom_attributes_dialcode.prop('value', updated_dial).change();

            });
            $("body").on("click",".form-shipping-address .input-label-overlay span",function(){
                $(this).attr( 'contenteditable',true);
            });

            $("body").on("focusout",".payment-method-billing-address .input-label-overlay span",function(){
                let updated_dial =  $(this).text();
                let custom_attributes_dialcode = $(".payment-method-billing-address").find("input[name='custom_attributes[dialcode]']");
                custom_attributes_dialcode.prop('value', updated_dial).change();
            });
            $("body").on("click",".payment-method-billing-address .input-label-overlay span",function(){
                $(this).attr( 'contenteditable',true);
            });
        }


        return Component.extend({
            /**
             * Set default region value
             */
            initialize: function (config) {
                this._super();
                this.telephoneMessageEnabled = window.checkoutConfig.telephoneMessageEnabled;
                this.telephoneMessage = window.checkoutConfig.telephoneMessage;
                return this;
            }

        });
    }
);

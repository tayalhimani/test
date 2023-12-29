/**
 * This was a proof of concept mixin taht we can dynamically add validation for
 * the shipping step's 'continue to payment' button component. Leaving it here
 * because it's quite a useful reference.
 */
define([
        'Dyson_SinglePageCheckout/js/model/shipping-step-validation'
    ],
    function(shippingStepValidation){
        'use strict';

        return function(ContinueBtn){

            return ContinueBtn.extend({

                initialize: function() {
                    var self = this;
                    this._super();

                    shippingStepValidation.validationFunctions.push('myLovelyValidationFunction');
                },

                myLovelyValidationFunction: function() {
                    console.dir('It ran!!!!');
                    return true;
                }

            });
        };
    });
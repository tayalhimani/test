define([
    'ko',
    './../model/checkout-steps'
],
function(ko, checkoutSteps){
    'use strict';

    return function(AmastyOnepage){

        return AmastyOnepage.extend({

            // Returns the observable array els as a classes string.
            dysonAdditionalClasses: function() {
                return checkoutSteps.checkoutContainerAdditionalClasses();
            }

        });
    };
});
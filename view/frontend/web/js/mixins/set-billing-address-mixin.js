define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, _, wrapper, quote) {
    'use strict';

    return function (setBillingAddressAction) {

        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            let billingAddress = quote.billingAddress();

            if (billingAddress != null) {

                if (billingAddress['extensionAttributes'] === undefined) {
                    billingAddress['extensionAttributes'] = {};
                }

                // you can extract value of extension attribute from any place (in this example using customAttributes approach)
                _.each(billingAddress.customAttributes, function (value, key) {
                    if (key === 'dialcode') { // Condition applicable for Magento 2.2.7 and lower version. Need to remove code after all market upgraded to Magento 2.3.6 and higher version.
                        if (window.checkoutConfig.dialcode) {
                            billingAddress['extensionAttributes'][key] = window.checkoutConfig.dialcode['dialcode'];
                        } else {
                            billingAddress['extensionAttributes'][key] = value;
                        }
                    }
                    else if (value.attribute_code === 'dialcode') { // Condition applicable for Magento 2.3.6 and higher version
                        if (window.checkoutConfig.dialcode) {
                            billingAddress['extensionAttributes'][value.attribute_code] = window.checkoutConfig.dialcode['dialcode'];
                        } else {
                            billingAddress['extensionAttributes'][value.attribute_code] = value.value;
                        }
                        
                    }
                });
            }

            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction(messageContainer);
        });
    };
});

define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, _, wrapper, quote) {
    'use strict';

    return function (setBillingAddressAction) {

        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            var billingAddress = quote.billingAddress();
            var attr = '';

            if(billingAddress != undefined) {

                if (billingAddress['extensionAttributes'] === undefined) {
                    billingAddress['extensionAttributes'] = {};
                }

                // Condition applicable for Magento 2.3.6 and higher version
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }

                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes, function (key, value) {

                        if($.isPlainObject(value)){
                            attr = value['attribute_code'];
                            value = value['value'];
                        }

                        if (attr != '') { // Condition applicable for Magento 2.3.6 and higher version
                            if(!attr.includes('custom_field')) {
                                billingAddress['extension_attributes'][attr] = value;
                            }
                        } else {
                            billingAddress['extensionAttributes'][key] = value;
                        }
                    });
                }

            }
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction(messageContainer);
        });
    };
});

define(
    [
        'Magento_Ui/js/form/element/abstract',
        'uiRegistry'
    ],
    function (
        Component,
        registry
    ) {
        'use strict';

        return Component.extend({
            /**
             * Set default region value
             */
            initialize: function (config) {
                this._super();
                this.shippingTitle = window.checkoutConfig.shippingTitle;
                return this;
            }
        });
    }
);

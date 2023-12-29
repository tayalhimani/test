define(
    [
        'Magento_Ui/js/form/element/abstract',
        'uiRegistry',
        'jquery',
        'ko'
    ],
    function (
        Component,
        registry,
        $,
        ko
    ) {
        'use strict';
        var message = "";

        return Component.extend({
            /**
             * Set default region value
             */
            initialize: function (config) {
                this._super();
                this.deliveryMessage()
                return this;
            },
            estimateDeliveryDate : ko.observable(''),
            deliveryMessage: function () {
                var self = this;
                self.estimateDeliveryDate(window.localStorage.getItem('deliveryDate'));
                $(document).ready(function () {
                    var postcodevalue = "";
                    if ($('input[name="postcode"]').length) {
                        postcodevalue = $('input[name="postcode"]').val();
                    }
                    if(postcodevalue==""){
                        window.localStorage.setItem('deliveryDate','');
                        self.estimateDeliveryDate("");
                    }
                });
            }

        });
    }
);

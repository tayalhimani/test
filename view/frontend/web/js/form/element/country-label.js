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

            initialize: function (config) {
                this._super();
                return this;
            }

        });
    }
);

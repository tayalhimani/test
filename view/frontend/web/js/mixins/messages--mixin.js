define(['jquery'],
function($){
    'use strict';
    return function(Messages){

        // set global variable so we know if scrollToError() has been called yet.
        let hasScrolled = false;

        return Messages.extend({

            /**
             * Init
             */
            initialize: function() {
                let self = this;
                this._super();
            },

            // Scroll to selector
            scrollToError: function(selector) {
                if (!hasScrolled) {
                    hasScrolled = true;

                    // scroll to error message to user can see what went wrong
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $(selector).offset().top
                    }, 1500);
                }

                // reset variable so this method can be called successfully again without a page reload
                setTimeout(function() { hasScrolled = false; }, 2000);

            },

            // Check if single page checkout is enabled by seeig if the SPC specific header is present
            singlePageCheckoutEnabled: function() {
                return $('.page-header--single-page-checkout').length;
            },

            /**
             * Ovveride for onHiddenChange in Magento_Ui/js/view/messages.js
             * This is a TR specific change, and moves checkout error messages to a more user friendly location, then scrolls to it.
             * @param isHidden
             */
            onHiddenChange: function (isHidden) {
                var self = this;
                let checkout_messages = $("[data-role='checkout-messages']");

                // Hide message block if needed
                // Set timeout for 7s, message will disappear after that
                if(isHidden) {
                    setTimeout(function () {
                        $(self.selector).hide('blind', {}, 500);
                    }, 7000);
                }

                // check that are on the checkout
                if(typeof checkout_messages !== "undefined") {
                    if(self.singlePageCheckoutEnabled() === 0) {
                        $.each(checkout_messages, function (index, val) {

                            if($(".checkout-billing-address").find(checkout_messages).length === 0 && $(val).find('div.message-error').length)
                            {
                                // move error message to just below the billing address details
                                $(val).appendTo(".checkout-billing-address");
                            }
                        });
                    }
                    self.scrollToError(".billing-address-same-as-shipping-block");

                    // else we are not on the checkout and do not want to mess with functionality, so call original method.
                } else {
                    this._super();
                }
            }
        });
    };
});

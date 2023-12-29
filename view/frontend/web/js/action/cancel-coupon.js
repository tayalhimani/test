/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment-service'
], function ($, quote, urlManager, errorProcessor, messageContainer, storage, getPaymentInformationAction, totals, $t,
             fullScreenLoader , paymentService
) {
    'use strict';

    return function (isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getCancelCouponUrl(quoteId),
            message = $t('Your coupon was successfully removed.');

        messageContainer.clear();
        fullScreenLoader.startLoader();

        return storage.delete(
            url,
            false
        ).done(function () {
            var deferred = $.Deferred();

            //remove payment method
            quote.paymentMethod(null);
            paymentService.setPaymentMethods([]);

            var $collapsible = $('[data-collapsible="true"]');
            // Check if the collapsible widget is already initialized
            if ($collapsible.hasClass('accordion-heading')) {
                // If it is initialized, destroy the widget
                $("#payment-element").accordion('destroy');
            }

            totals.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                isApplied(false);
                totals.isLoading(false);
                fullScreenLoader.stopLoader();

                const clickOnContinueButton = () => {
                    setTimeout(() => {
                        const checkoutBlock = $('<div class="checkout-block__tabs">' +
                            '<div class="checkout-block__tabs__header"></div>' +
                            '<div class="checkout-block__tabs__content"></div>' +
                            '</div>');
                        $('.checkout-block .payment-methods').prepend(checkoutBlock);

                        $('.payment-method').each((index, element) => {
                            const paymentMethodTitle = $(element).find('.payment-method-title');
                            const paymentMethodContent = $(element).find('.payment-method-content');

                            paymentMethodTitle.attr('data-tab', index + 1);
                            paymentMethodContent.attr('data-tab-content', index + 1);

                            if (index === 0) {
                                $(element).addClass('_active');

                                if (!$('.dyson-tr').length) {
                                    $(element).find('button.checkout').prop('disabled', false);
                                }
                            }
                        });

                        const isEnabled = window.checkoutConfig.accordion_payment_enabled;
                        // Copy existing markup to our tabs structure
                        if (!isEnabled) {
                          $('.payment-method .payment-method-title').each((index, element) => {
                              $(element).clone().appendTo('.checkout-block__tabs__header');
                          });
                        }

                        $('.checkout-block__tabs__header')
                            .find('.payment-method-title:first-of-type')
                            .addClass('payment-method-title--active')
                            .find('input')
                            .prop('checked', true);

                    }, 1000);
                };

                const checkoutBlockTabs = $(".checkout-block__tabs").length;
                const isAccordionEnabled = window.checkoutConfig.accordion_payment_enabled;

                if (!isAccordionEnabled && checkoutBlockTabs < 1) {
                    clickOnContinueButton();
                } else if (isAccordionEnabled) {
                    setTimeout(() => {
                        const paymethodNumber = $("#payment-element .accordion-heading").length;
                        $("#payment-element").hide();

                        if (paymethodNumber > 1) {
                            setTimeout(() => {
                                $("#payment-element").accordion({
                                    'openedState': 'active',
                                    'collapsible': true,
                                    'active': false
                                });
                            }, 200);
                        } else {
                            $(".payment-tabs__header").addClass('no-accordion');
                            $(".no-accordion .accordion-content ").show();
                            $(".no-accordion .accordion-heading").addClass('active cursor-effect');
                        }

                        $("#payment-element").show();
                        $("#discount-form #discount-code").focus();

                    }, 1000);
                }
            });
            messageContainer.addSuccessMessage({
                'message': message
            });
            cancelCouponDataLayer(message);
        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
        });
    };

    /**
     * Display cancel coupon message to the datalayer
     *
     * @param message
     */
    function cancelCouponDataLayer(message) {
        window.dataLayer.push({
            'event': 'displayed_message',
            'messaging': {
                "message": message.toLowerCase(),
                "message_category": "information",
                "message_type": "success",
                "message_reference": "coupon_submission"
            }
        });
    }

});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Adyen_Payment/js/model/adyen-payment-service',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Ui/js/model/messageList',
    'accordion'
], function (ko, $, quote, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
             totals, fullScreenLoader , adyenPaymentService, paymentService, messageList
) {
    'use strict';

    /** @inheritdoc */
    $(document).on("click", "#discount-form .action-apply", function () {
        let couponField = $("#discount-code").val(),
            couponStatus = '',
            errorMessage = $t('This is a required field.'),
            add_error_accesibility = () => {
                setTimeout(() => {
                    $("#discount-form #discount-code-error").attr("aria-live", "assertive");
                }, 1300);
            }

        if (!couponField) {
            couponFieldDataLayer(errorMessage);
            messageContainer.clear();
        }

        add_error_accesibility();

    });

    $(document).on("click", "#block-discount-heading", function () {
        var inputcode = $.trim($("#discount-code").val());
        if (inputcode != '') {
            $(".message-error").show();
        } else{
            $(".message-error").hide();
            $('input').removeClass('mage-error');
        }
    });

    return function (couponCode, isApplied) {

        var retrievePaymentMethods = function (){
            fullScreenLoader.startLoader();
            adyenPaymentService.retrievePaymentMethods().done(function(paymentMethods) {
                try {
                    paymentMethods = JSON.parse(paymentMethods);
                } catch(error) {
                    console.log(error);
                    paymentMethods = null;
                }
                adyenPaymentService.setPaymentMethods(paymentMethods);
                fullScreenLoader.stopLoader();

                const clickOnContinueButton = () => {
                    setTimeout(() => {
                        $("#discount-form .action-cancel").focus();
                    }, 1300);
                };

                const checkoutBlockTabs = $(".checkout-block__tabs").length;
                const isAccordionEnabled = window.checkoutConfig.accordion_payment_enabled;

                if ((!isAccordionEnabled && checkoutBlockTabs < 1) || !isAccordionEnabled) {
                    clickOnContinueButton();
                } else {
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
                            }, 1000);
                        } else {
                            $(".payment-tabs__header").addClass('no-accordion');
                            $(".no-accordion .accordion-content ").show();
                            $(".no-accordion .accordion-heading").addClass('active cursor-effect');
                        }
                        $("#payment-element").show();
                        $("#discount-form .action-cancel").focus();

                    }, 1300);
                }

            }).fail (function() {
                console.log('Fetching the payment methods failed!');
            });
        };

        var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyCouponUrl(couponCode, quoteId),
            couponStatus = '',
            message = $t("We have applied your code");

        fullScreenLoader.startLoader();

        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            var response = JSON.parse(response);
            var deferred;

            //remove payment method
            quote.paymentMethod(null);

            if (response) {
                deferred = $.Deferred();

                //remove payment method
                quote.paymentMethod(null);


                var $collapsible = $('[data-collapsible="true"]');
                // Check if the collapsible widget is already initialized
                if ($collapsible.hasClass('accordion-heading')) {
                    // If it is initialized, destroy the widget
                    $("#payment-element").accordion('destroy');
                }
                // Interchanged the lines to fix the issue with the destroying of collapsible widget via paymentService
                paymentService.setPaymentMethods([]);
                isApplied(true);
                totals.isLoading(true);

                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                    const retrievePaymentMethods_ = () => {
                        setTimeout(() => {
                            retrievePaymentMethods();
                        }, 300);
                    }
                    retrievePaymentMethods_();

                });
                var enableFocus = () => {
                    setTimeout(() => {
                        $("#discount-form .action-cancel").focus();
                    }, 1300);
                }
                enableFocus();
            }
            couponStatusDataLayer(couponCode, couponStatus = 'valid');
            couponMessageTypeDataLayer(message + ' ' + couponCode, couponStatus = 'success');
            messageContainer.clear();
            if(response.couponType == 'free_gift_by_dyson'){
                messageContainer.addErrorMessage({
                    'message': response.msg
                });
            }

        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
            couponStatusDataLayer(couponCode, couponStatus = 'invalid');
            couponMessageTypeDataLayer(message = response.responseJSON.message, couponStatus = 'error');
            var addCustomError = () => {
                setTimeout(() => {
                    $("#discount-form .input-text").addClass("mage-error");
                }, 1000);
            }
            addCustomError();
        });
    };

    /**
     * Display coupon code status to the datalayer
     *
     * @param couponCode
     * @param couponStatus
     */
    function couponStatusDataLayer(couponCode, couponStatus) {
        window.dataLayer.push({
            'event': 'voucherCodeSubmitted',
            'checkout': {
                'voucherCode': couponCode,
                'voucherCodeStatus': couponStatus
            }
        });
    }

    /**
     * Display success and error message for coupon code field to the datalayer
     *
     * @param message
     * @param couponStatus
     */
    function couponMessageTypeDataLayer(message, couponStatus) {
        window.dataLayer.push({
            'event': 'displayed_message',
            'messaging': {
                "message": message.toLowerCase(),
                "message_category": "information",
                "message_type": couponStatus,
                "message_reference": "coupon_submission"
            }
        });
    }

    /**
     * Display error message for coupon code field to the datalayer
     *
     * @param errorMessage
     */
    function couponFieldDataLayer(errorMessage) {
        window.dataLayer.push({
            "event": "displayed_message",
            "messaging": {
                "input_name": "discount_code",
                "message": errorMessage.toLowerCase(),
                "message_category": "form_field_validation",
                "message_type": "error"
            }
        })
    }

});

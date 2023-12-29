define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/get-payment-information',
    'Adyen_Payment/js/model/adyen-payment-service',
    'Magento_Checkout/js/model/full-screen-loader',
    'accordion'
],
function($, _, paymentMethods, paymentService , getPaymentInformationAction , adyenPaymentService , fullScreenLoader){
    'use strict';

    return function(PaymentList){

        return PaymentList.extend({

            isLoading: paymentService.isLoading,

            initialize: function() {
                var self = this;
                this._super();

                    window.localStorage.removeItem('accordion_payment_group_title');

                    $(document).on('click', '.checkout-block__progress-button:not(".sorted-pro-api-call") .button', function() {
                      if (window.checkoutConfig.accordion_payment_enabled) {
                        var deferred = $.Deferred();
                        var $collapsible = $('[data-collapsible="true"]');
                        // Check if the collapsible widget is already initialized
                        if ($collapsible.hasClass('accordion-heading')) {
                            // If it is initialized, destroy the widget
                            $("#payment-element").accordion('destroy');
                        }
                        paymentService.setPaymentMethods([]);
                        getPaymentInformationAction(deferred);
                        $.when(deferred).done(function () {
                            const retrievePaymentMethods_ = () => {
                                setTimeout(() => {
                                    retrievePaymentMethods();
                                }, 300);
                            }
                            retrievePaymentMethods_();

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

                                  let _reloadAccordion = () => {


                                    let paymethodNumber = $("#payment-element .accordion-heading").length;
                                    console.log("no of payment method " + paymethodNumber);
                                    if (paymethodNumber > 1) {
                                        console.log("configured");

                                        $(".no-accordion .accordion-heading").removeClass('active cursor-effect');
                                        $(".payment-tabs__header").removeClass('no-accordion');
                                        $("#payment-element").accordion({
                                            'openedState': 'active',
                                            'collapsible': true,
                                            'active': false
                                        });
                                    } else if (paymethodNumber == 1) {

                                        $(".payment-tabs__header").addClass('no-accordion');
                                        $(".accordion-heading").trigger('click');
                                        $(".no-accordion .accordion-content ").show();
                                        $(".no-accordion .accordion-heading").addClass('active cursor-effect');
                                    }

                                  }

                                  setTimeout(() => {
                                    _reloadAccordion();
                                  }, 1400);

                                  fullScreenLoader.stopLoader();

                              }).fail (function() {
                                  console.log('Fetching the payment methods failed!');
                              });
                            };

                        });
                      }
                    });

                // Subscribe to the paymentMethods observable array, NOT
                // self.paymentGroupsList, as it changes more currently. Either
                // way the subscriber will only fire when the shipping address
                // has been populated without validation errors.
                paymentMethods.subscribe(function(value) {
                    var methods;

                    _.each(self.paymentGroupsList(), function (group) {
                        // Iterate through all payment methods in the region.
                        methods = self.getRegion(group.displayArea);

                        // If at least one method is available..
                        if (methods().length) {
                            self.selectFirstPaymentMethodIfNoneSelected(methods());
                        }
                    });
                });
            },

                /**
                 * @param {Array} methods
                 */
                selectFirstPaymentMethodIfNoneSelected: function (methods) {
                    var method_selected = false;
                    var method_selected_title = '';

                    // Iterate through each payment method available.
                    _.each(methods, function (method) {
                        // Each payment_method class extends
                        // Magento_Checkout/js/view/payment/default, fyi.

                        // Check if any of these methods is already selected.
                        if (method.isChecked() === method.getCode()) {
                            method_selected = method.getCode();
                            method_selected_title = method.getTitle();
                        }
                    });

                    // If no method selected then select the first as a default.
                    if (method_selected === false) {
                        methods[0].selectPaymentMethod();
                        method_selected_title = methods[0].getTitle();
                    }

                    //pushing paymentType selected by customer to datalayer - start
                    if (typeof window.dataLayer !== 'undefined' && method_selected != 'adyen_hpp') {
                        var method_title = window.checkoutConfig.active_ootb_payment_method;
                        var isEnabled = window.checkoutConfig.accordion_payment_enabled;
                        if(isEnabled){
                            $.each( method_title, function( key, value ) {
                                if(key == method_selected){
                                    method_selected_title = value;
                                }
                            });
                        }else if(method_title.paypal_express == "PayPal Express") {
                            method_selected_title = method_title.paypal_express;
                        }
                        window.dataLayer.push({checkout: {"paymentType" : method_selected_title}});
                    }
                    //pushing paymentType selected by customer to datalayer - end
                },



            isAccordionPaymentEnabled : function (){
              var isEnabled = window.checkoutConfig.accordion_payment_enabled;
              if(isEnabled)
              $('body').addClass('accordion-payment-layout');
              return isEnabled;
            },

            getPaymentGroupTitle : function (method){
                    var result = '';
                    var IsPaymentGroupTitle = window.checkoutConfig.accordion_payment_group_title;

                    let modifiedArray = [];
                    let isExistValue = [];
                    let afternativePay = [];
                    let adyen_hpp_group = '';
                    IsPaymentGroupTitle.forEach(element => {
                        if (element[1] == "adyen_hpp") {
                            adyen_hpp_group = element[0];
                        }
                        if (adyen_hpp_group == element[0]){
                            afternativePay.push(element);
                        }
                        if (!isExistValue.includes(element[0])) {
                            isExistValue.push(element[0]);
                            modifiedArray.push(element);
                        }
                    });
                    let adyen_threshold_value = window.checkoutConfig.adyen_threshold_value;
                    let all_adyen_active_payment_group = window.checkoutConfig.accordion_payment_adyen_active;
                    let grand_total = adyen_threshold_value[1]['cart_grandtotal'];
                    let adyen_threshold_ = Object.values(adyen_threshold_value[0]);
                    let adyen_threshold_max = Math.max(...adyen_threshold_);
                    let adyen_hpp_index = modifiedArray.findIndex(arr => arr.includes("adyen_hpp"));

                    if (adyen_threshold_max > 0) {
                        if (adyen_threshold_max < grand_total) {
                            let adyen_hpp_Index = modifiedArray.findIndex(e => e[1] == "adyen_hpp");
                            if (adyen_hpp_Index >= 0 && afternativePay.length == 1) {
                                modifiedArray.splice(adyen_hpp_Index,1);
                            }
                            all_adyen_active_payment_group = all_adyen_active_payment_group.filter(function (element) {
                                return element !== "adyen_hpp~afterpaytouch" && element !== "adyen_hpp~zip";
                            });
                        }
                    }
                    if(all_adyen_active_payment_group.includes("adyen_hpp~applepay") && !this.isSafariBrowser()) {
                        all_adyen_active_payment_group = all_adyen_active_payment_group.filter(function (element) {
                            return element !== "adyen_hpp~applepay";
                        });
                    }
                    if ((adyen_hpp_index >= 0 && afternativePay.length == 1) && all_adyen_active_payment_group.length == 0) {
                        modifiedArray.splice(adyen_hpp_index,1);
                    }

                    $.each( modifiedArray, function(key, val) {
                        if(method == val[1]){
                            result = val[2];
                        }
                    });
                    return result;
                },
                getPaymentImage : function (method){
                    var result = '';
                    var IsPaymentGroupTitle = window.checkoutConfig.accordion_payment_image;
                    $.each( IsPaymentGroupTitle, function(key, val) {
                        if(method == val[0]){
                            result = val[1];
                        }

                    });
                    return result;
                },
                getPaymentMessage : function (method){
                    var result = '';
                    var IsPaymentGroupTitle = window.checkoutConfig.accordion_payment_message;
                    $.each( IsPaymentGroupTitle, function(key, val) {
                        if(method == val[0]){
                            result = val[1];
                        }

                    });
                    return result;
                },

                /**
                 * Detects browser and return true for Safari
                 */
                isSafariBrowser: function () {
                    var ua = window.navigator.userAgent;
                    var iOS = !!ua.match(/iP(ad|od|hone)/i);
                    var macOS = !!ua.match(/(Mac)/i);
                    if (iOS || macOS) { //detecting Safari in IOS mobile browsers
                        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                        if (isSafari) {
                            return true;
                        }
                    }
                    return false;
                },
                /**
                 * Get All Enbale Payment Method
                 */
                paymentMethods: function (result) {
                    let newPaymentMethodArray = new Array();
                    let hppPaymentMethod = window.checkoutConfig.accordion_payment_adyen_active;
                    let OthersPaymentMethod = Object.keys(window.checkoutConfig.active_ootb_payment_method);
                    //adyen_hpp value remove from array
                    OthersPaymentMethod = OthersPaymentMethod.filter(item => item !== 'adyen_hpp');
                    //merge two array for get all payment method in one array
                    newPaymentMethodArray = OthersPaymentMethod.concat(hppPaymentMethod);
                    return newPaymentMethodArray;
                },
                /**
                 * Get Accordian Value For Payment Method
                 */
                accordianValue: function() {
                    let paymentMethod = this.paymentMethods();
                    let paymentMethodLength = paymentMethod.length;
                    let isSafariBrowser = this.isSafariBrowser();

                    // conditons for safari and non safari browser for accordion
                    if(paymentMethodLength == 1){
                        return false;
                    }else if((isSafariBrowser && paymentMethodLength > 1)){
                        return true;
                    }else if(!isSafariBrowser && paymentMethodLength >= 2){
                        if((paymentMethodLength > 2 && paymentMethod.indexOf('adyen_hpp~applepay') >= 0) || (paymentMethodLength >= 2 && paymentMethod.indexOf('adyen_hpp~applepay') < 0)) {
                            return true;
                        }else{
                            return false;
                        }
                    }else{
                        return false;
                    }
                }
            });
        };
    });

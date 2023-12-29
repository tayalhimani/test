define([
  'jquery',
  'Magento_Checkout/js/model/payment/method-list',
  'Magento_Checkout/js/model/payment-service',
  'Magento_Checkout/js/action/get-payment-information',
  'Magento_Checkout/js/model/full-screen-loader',
  'accordion'
],
function($, paymentMethods, paymentService , getPaymentInformationAction , fullScreenLoader){
    'use strict';

    return function(PaymentList){

        return PaymentList.extend({

            initialize: function() {
                var self = this;
                this._super();
                window.localStorage.removeItem('accordion_payment_group_title');

                $(document).on('click', '.checkout-block__progress-button:not(".sorted-pro-api-call") .button', function() {
                    if (window.checkoutConfig.accordion_payment_enabled) {
                      var deferred = $.Deferred();
                      paymentService.setPaymentMethods([]);
                      getPaymentInformationAction(deferred);
                      $.when(deferred).done(function () {
                                  fullScreenLoader.stopLoader();
                                  let _reloadAccordion = () => {

                                  let paymethodNumber = $("#payment-element .accordion-heading").length;
                                  console.log("no of payment method " + paymethodNumber);
                                  if (paymethodNumber > 1) {
                                      console.log("configured");
                                      var $collapsible = $('[data-collapsible="true"]');
                                      // Check if the collapsible widget is already initialized
                                      if ($collapsible.hasClass('accordion-heading')) {
                                          // If it is initialized, destroy the widget
                                          $("#payment-element").accordion('destroy');
                                      }
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
            selectFirstPaymentMethodIfNoneSelected: function(methods) {
                var method_selected = false;
                var method_selected_title = '';

                // Iterate through each payment method available.
                _.each(methods, function(method) {
                    // Each payment_method class extends
                    // Magento_Checkout/js/view/payment/default, fyi.

                    // Check if any of these methods is already selected.
                    if (method.isChecked() === method.getCode()) {
                        method_selected = method.getCode();
                        if (method_selected && method_selected == 'ipay88') {
                            method_selected_title = 'iPay88';
                        } else {
                            method_selected_title = method.getTitle();
                        }
                    }
                });

                // If no method selected then select the first as a default.
                if (method_selected === false) {
                    methods[0].selectPaymentMethod();
                    if (methods[0].getCode() == 'ipay88') {
                        method_selected_title = 'iPay88';
                    } else {
                        method_selected_title = methods[0].getTitle();
                    }
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

                let modifiedArray = new Array();
                var isExistValue = new Array();

                IsPaymentGroupTitle.forEach(element => {
                    if (!isExistValue.includes(element[0])) {
                        isExistValue.push(element[0]);
                        modifiedArray.push(element);
                    }
                });

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
            }
        });
    };
});

define([
    'jquery',
    'Magento_Checkout/js/model/payment-service',
    'mage/validation'
], function ($, paymentService) {

    return function(config, element) {

        var $document = $(document);
        var $documentBody = $('body');
        var $checkoutPanel = $('.checkout-block');
        var $detailsPanel = $('.checkout-block--details');
        var $paymentPanel = $('.checkout-block--payment');

        var checkExist = setInterval(function() {
            if ($('.checkout-block--details').length) {
                startCheckout();
                clearInterval(checkExist);
            }
        }, 100);

        $document.on('click', '[data-index="continue_to_payment"]', continueSection);
        $document.on('click', '.checkout-block--complete', editSection);
        $document.on('click', '.payment-method-content .action-update', updateAddress);
        $document.on('change', '.field[name="shippingAddress.city"] select', cityOnChange);
        $document.on('change', '.field[name="shippingAddress.region"] select', regionOnChange);

        function regionOnChange(){
            let regionVal = $(this).val();
            if (regionVal != "") {
                if ($(this).next(".field-error").length) {
                    $(this).next(".field-error").remove();
                }
             $(this).closest(".field").removeClass("_error");
         }
            if($(this).hasClass('required-entry')){
                $(this).next(".region-error").remove();
                $(this).removeClass('required-entry');

            }
        }

        function cityOnChange () {
            if ($('.dyson-sa').length || $('.dyson-th').length) {
                let cityVal = $(this).val();
                if (cityVal != "") {
                    if ($(this).next(".field-error").length) {
                        $(this).next(".field-error").remove();
                    }
                    $(this).closest(".field").removeClass("_error");
                }
            }
            return;
        }

        function startCheckout() {
            // When loaded, open our first checkout panel
            checkoutAmends();
            openPanel('1');
        }

        function editSection() {
            // Edit a previously completed section
            resetPanels();
            $(this).removeClass('checkout-block--complete');
            var editPanelNum = $(this).data('order');
            openPanel(editPanelNum);
        }

        function continueSection() {

            if ($('.dyson-sa').length ) {
                let cityValue = $('.field[name="shippingAddress.city"] select').val();
                let cityElem = $('.field[name="shippingAddress.city"]');
                if (cityValue == '') {
                    let errorDiv = `<div class="field-error">
                                        <span>${$.mage.__("This is a required field.")}</span>
                                    </div>`;
                    setTimeout(function () {
                        if (cityElem.find(".select").next(".field-error").length) {
                            cityElem.find(".select").nextAll(".field-error").remove();
                        }
                        cityElem.addClass('_error');
                        $(errorDiv).insertAfter(cityElem.find(".control .select"));
                    }, 3000);
                }
            }
            /*
             * Hack for validation issue with IN pincode check
             * Check for whichever module is available.
             */
            if ($('.dyson-in').length) {
                $('.form-shipping-address .in-error').each(function() {
                    $(this).removeClass('in-error');
                });
                var billingAddressPayuCity = $('.field[name="billingAddresspayusubvention.city"]');

                if (billingAddressPayuCity.length === 0) {
                    billingAddressPayuCity = $('.field[name="billingAddresspayu.city"]');
                }

                if (billingAddressPayuCity.length) {
                    billingAddressPayuCity.addClass('in-error');
                }
            };

            // IF validation comes back true
            if(validateCustomerFields())
            {
                renderPaymentMethods();
                successPanel();
            }
        }

        function openPanel(targetPanel) {
            // Open the specific section based on the data-order attribute and update the data-status attribute

            var $openTarget = $(".checkout-block[data-order='" + targetPanel +"']");

            if (( targetPanel == '1') && ($(window).width() < 1024)) {
              // No animtion for mobile first open panel
            }
            else {
              $('html, body').animate({
                  scrollTop: $('.checkout-block--details').offset().top - 30
              }, '10');
            }

            window.setTimeout(function(){
                $openTarget.addClass('checkout-block--active');
                $openTarget.attr('data-status','open');

                // Grab the current data-order value of the target section
                var panelOrder = $openTarget.data('order');

                // Push the relevant event and virtualPagePath to the GTM dataLayer
                if (panelOrder == '1') {
                    if (typeof window.dataLayer !== 'undefined') {
                        window.dataLayer.push({
                            event:'virtualPageView',
                            virtualPagePath: '/checkout'
                        });
                    }
                }
                else if (panelOrder == '2') {
                    if (typeof window.dataLayer !== 'undefined') {
                        window.dataLayer.push({
                            event:'virtualPageView',
                            virtualPagePath: '/checkout/payment'
                        });
                    }
                }
            }, 10);
        }

        function successPanel() {
            // Add completed state to current open panel and open the next panel

            var currentPanelNumber = $(".checkout-block[data-status=open]").data('order');
            var nextPanelNumber = currentPanelNumber + 1;

            $(".checkout-block[data-status=open]").addClass('checkout-block--complete');
            resetPanels();
            openPanel(nextPanelNumber);
        }

        function resetPanels() {
            // Reset all sections to closed state

            $('.checkout-block').removeClass('checkout-block--active');
            $('.checkout-block').attr('data-status','closed');
        }

        function updateAddress() {
            if ( $('.checkout-billing-address ._required._error').length ) {
                $('.checkout-billing-address .field._error:first').each(function() {
                    if($(this).find('.input-text').length !== 0) {
                        $(this).find('.input-text').focus();
                    }
                    else if ($(this).find('select.select').length !== 0) {
                        $(this).find('select.select').focus();
                    }
                });
            }
            else {
                $('html, body').animate({
                  scrollTop: $('.checkout-block--details').offset().top - 30
                }, '10');
                $('.checkout-block--payment').removeClass('checkout-block--billing-mandatory');
            }

            if ($('.dyson-in').length) {
                var billingAddressPayuCity = $('.field[name="billingAddresspayusubvention.city"]');

                if (billingAddressPayuCity.length === 0) {
                    billingAddressPayuCity = $('.field[name="billingAddresspayu.city"]');
                }

                if (billingAddressPayuCity.length) {
                    billingAddressPayuCity.removeClass('in-error');
                }
            }
        }

        function checkoutAmends() {
            // IN
            if ($('.dyson-in').length) {
              var checkElementExists = setInterval(function() {
                  if ($('.field[name="shippingAddress.telephone"] input.input-text').length && $('.field[name="shippingAddress.postcode"] input.input-text').length) {
                      $('.field[name="shippingAddress.telephone"]').find('.control').prepend('<label class="input-label-overlay">+91</label>');
                      $('.field[name="shippingAddress.postcode"]').removeClass('_error');
                      $('.field[name="shippingAddress.postcode"] .field-error').remove();
                      clearInterval(checkElementExists);
                  }
              }, 100);
            }

           // PL
           if ($('.dyson-pl').length) {
             var checkElementExists = setInterval(function() {
               if ($('.field[name="shippingAddress.postcode"] input.input-text').length) {
                   $('.field[name="shippingAddress.postcode"]').addClass('_required');
                   clearInterval(checkElementExists);
               }
             }, 100);
           }

           // TR
            if ($('.dyson-tr').length) {
                var checkElementExists = setInterval(function() {
                    if ($('.field[name="shippingAddress.street.0"] input.input-text').length) {
                        $('.field[name="shippingAddress.street.0"]').addClass('_required');
                        $('.field[name="shippingAddress.street.1"]').addClass('_required');
                        $('.field[name="shippingAddress.region"]').addClass('_required');
                        $('.field[name="shippingAddress.city"]').addClass('_required');
                        clearInterval(checkElementExists);
                    }
                }, 100);
            }

            // NZ
            if ($('.dyson-nz').length) {
                var checkElementExists = setInterval(function() {
                    if ($('.field[name="shippingAddress.city"] input.input-text').length) {
                        $('.field[name="shippingAddress.city"]').appendTo('fieldset.field');
                        clearInterval(checkElementExists);
                    }
                }, 100);
            }
        }

        //trick the form fields into having their validation checked
        function validateCustomerFields() {

            var ret = true;

            $('.form-shipping-address ._required').each(function() {

                var fieldSelector = $(this).attr('name');

                var field =  $("[name='" + fieldSelector + "']").find("input");
                var val = field.val();

                if(typeof val === 'undefined') //catch the fields which are select options and not text inputs
                {
                    field =  $("[name='" + fieldSelector + "']").find("select");
                    val = field.children("option:selected").val();
                }

                provokeValidation(field, val);
                focusFirstError();
            });

            // Check that all required checkboxes are checked
            $('.form-shipping-address .field--checkboxes._required').each(function() {
                if ($(this).find('input[type="checkbox"]').prop('checked') == false) {
                    var checkbox = $(this).find('input[type="checkbox"]');

                    checkbox[0].click();
                    checkbox.click();
                    checkbox.prop('checked', false);
                    ret = false;
                }
            });

            if($('.form-shipping-address ._required._error').length) {
                ret = false;
            }

            if($('.dyson-in').length) {
                if (!$('[name="shippingAddress.postcode"] .complete').length) {
                    ret = false;
                }
            }

            var customerEmailFields = $('input#customer-email');

            if(!validateEmail(customerEmailFields.val()))
            {
                ret = false;
                provokeValidation(customerEmailFields, customerEmailFields.val());
                customerEmailFields.focus();
                $('.form.form-login[data-role=email-with-possible-login]').submit();
            }
            // apply custom validation for region selector
            var regionField = $('.field[name="shippingAddress.city"], .field[name="shippingAddress.region"]').find('select');
            var regionVal =  regionField.val();
            var regionpostcode = $('.field[name="shippingAddress.postcode"]').find('input').val();

            if(regionVal == "" && regionpostcode ){
                var invalidAria = regionField.attr('aria-invalid');
                if(invalidAria == "false"){
                    regionField.addClass('required-entry');

                    $.validator.validateSingleElement(regionField,{errorElement:'div',errorClass:'field-error region-error'});
                    regionField.parents('.field-city').addClass('_error');

                }
                ret = false;
            }
            return ret;

        }

        function validateEmail(email) {
            var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
            return filter.test(email);
        }

        function provokeValidation(field, val) {
            field.focus();
            field.keyup();

            if (field.attr('type') == 'number') {
                field.val(val + "0");
            } else if(val == "") {
                field.val(val + " ");
            }

            field.keyup();
            field.val(val);
            field.keyup();
            field.blur();
        }

        // Input focus on the first error
        function focusFirstError() {
            $('.form-shipping-address .field._error:first').each(function() {
                if($(this).find('.input-text').length !== 0) {
                    $(this).find('.input-text').focus();
                }
                else if ($(this).find('select.select').length !== 0) {
                    $(this).find('select.select').focus();
                }
            });
        }

        function renderPaymentMethods() {

          $('.payment-methods').loader();
          $('.payment-methods').loader("show");

          var checkExist = setInterval(function() {
            var paymentMethodsCount = paymentService.getAvailablePaymentMethods().length;
            var loadedPaymentMethods = $('.payment-method .payment-method-title');

            if (paymentMethodCount = loadedPaymentMethods) {
              $('.payment-methods').loader("hide");
              buildTabs();
              clearInterval(checkExist);
            }
          }, 100);
        }

        function buildTabs() {
          if ( $('.checkout-block__tabs').length ) {
              $('.checkout-block__tabs').remove();
              $('.payment-method').removeClass('_active');
          }

          $('.checkout-block .payment-methods').prepend(
              '<div class="checkout-block__tabs">'+
              '<div class="checkout-block__tabs__header">'+
              '</div>'+
              '<div class="checkout-block__tabs__content">'+
              '</div>'+
              '</div>'
          );

          // Grab each available payment method
          var paymentMethod = $('.payment-method');

          // Loop through the available payment types and add unique data attributes for our tab functionality
          var paymentMethodCount = 0;
          paymentMethod.each(function () {
              paymentMethodCount++;
              $(this).find('.payment-method-title').attr('data-tab', paymentMethodCount);
              $(this).find('.payment-method-content').attr('data-tab-content', paymentMethodCount);

              if (paymentMethodCount == "1") {

                  $(this).addClass('_active');

                  //we actually don't wanna do this for TR because it voids the validation
                  if (!$('.dyson-tr').length) {
                      $(this).find('button.checkout').prop("disabled", false);
                  }

              }
          });
          var isEnabled = window.checkoutConfig.accordion_payment_enabled;
          // Copy existing markup to our tabs structure
          if (!isEnabled) {
            $('.payment-method .payment-method-title').each(function( index ) {
                $(this).clone().appendTo('.checkout-block__tabs__header');
            });
          }


          // Add active state to the first option
          $('.checkout-block__tabs__header').find('.payment-method-title:first-of-type').addClass('payment-method-title--active');
          $('.checkout-block__tabs__header').find('.payment-method-title:first-of-type input').prop("checked", true);

          // Add active state to first selected tab
          var currentActiveTab = $('.checkout-block__tabs__header .payment-method-title input:radio:checked').closest('.payment-method-title').data('tab');
          $(".checkout-block__tabs__header .payment-method-title[data-tab='" + currentActiveTab +"']").addClass('payment-method-title--active');
          $(".payment-method-content[data-tab-content='" + currentActiveTab +"']").addClass('payment-method-content--active');

          // Loop through our payment options and show or hide depending on radio button status
          $('.checkout-block__tabs__header .payment-method-title input:radio').change(function() {

              $('.checkout-block__tabs__header .payment-method-title input:radio').each(function () {
                  var $this = $(this);
                  var currentTab = $(this).closest('.payment-method-title').data('tab');

                  if ($(this).prop('checked')) {
                      $(".checkout-block__tabs__header .payment-method-title[data-tab='" + currentTab +"']").addClass('payment-method-title--active');
                      $(".payment-method .payment-method-title[data-tab='" + currentTab +"']").find("input:radio").prop("checked", true);
                      $(".payment-method .payment-method-title[data-tab='" + currentTab +"']").find("input:radio").click();
                      $(".payment-method-content[data-tab-content='" + currentTab +"']").closest('.payment-method').addClass('_active');
                      $(".payment-method-content[data-tab-content='" + currentTab +"']").addClass('payment-method-content--active');

                      // TODO - Needs a better way of doing this
                      if ($('.dyson-pl .checkout-block--billing-mandatory').length) {
                          if (currentTab == '1' || currentTab == '3') {
                              $('.checkout-billing-address .billing-address-same-as-shipping-block input[name="billing-address-same-as-shipping"]')[0].click();
                              $('.checkout-billing-address .billing-address-same-as-shipping-block input[name="billing-address-same-as-shipping"]').prop('checked', false);
                          }
                      }
                  }
                  else {
                      $(".checkout-block__tabs__header .payment-method-title[data-tab='" + currentTab +"']").removeClass('payment-method-title--active');
                      $(".payment-method .payment-method-title[data-tab='" + currentTab +"']").find("input:radio").prop("checked", false);
                      $(".payment-method-content[data-tab-content='" + currentTab +"']").closest('.payment-method').removeClass('_active');
                      $(".payment-method-content[data-tab-content='" + currentTab +"']").removeClass('payment-method-content--active');
                  }
              });
          });

            //_active class on payment-method title needs to be taken off when there are 2 on the page

            if (($('.dyson-sg div.payment-method._active').length) == 2) {
                // console.log('there were 2 _active elements on the page');
                $('.payment-method-content[data-tab-content="2"]').closest('.payment-method').removeClass('_active');
            } else {
                // console.log('there is one element with _active on the page');
            }

        }
    }
});

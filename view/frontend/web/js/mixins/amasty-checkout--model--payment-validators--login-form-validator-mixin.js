define([
        'jquery',
        'Magento_Customer/js/model/customer'
    ],
    function($, customer){
        'use strict';

        return function(LoginFormValidator){

            /**
             * @modification: Avoids bug in Amasty_Checkout.
             * Prevents email validation running if there is no email entered.
             * Validation on blur and moving to payment step still works.
             *
             * @returns {boolean}
             */
            LoginFormValidator.validate = function(){

                // @modification: START
                if ($('#customer-email').val() === "" && !customer.isLoggedIn()) {
                    return false;
                }
                // @modification: END

                // Below if copied from login-form-validator.js in Amasty_Checkout
                var loginForm = 'form[data-role=email-with-possible-login]',
                    password = $(loginForm).find('#customer-password'),
                    createAcc = window.checkoutConfig.quoteData.additional_options.create_account;

                if (customer.isLoggedIn() || createAcc !== '2') {
                    return true;
                }

                if (password.val()) {
                    return $(loginForm).validation() && $(loginForm).validation('isValid');
                }

                return true;
            };

            return LoginFormValidator;
        };
    });

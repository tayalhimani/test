define([
    'jquery',
    'ko',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/button',
    'Dyson_SinglePageCheckout/js/model/shipping-step-validation',
    'Dyson_SinglePageCheckout/js/model/checkout-steps',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'mage/validation'
], function ($, ko, _, registry, Component , shippingStepValidation, checkoutStepsModel, checkoutTotals, $t) {



    return Component.extend({

        // Storing the functions to call in an observable array provides us with
        // a nice way to dynamically add and remove further validation methods
        // as required.
        // This just references the observable array on the
        // shippingStepValidation model in order to dynamically store the
        // methods called when validating. Say for example you wanted 2 continue
        // buttons on the page - you could extend this component twice and
        // override this property with your own observable arrays of validation
        // functions for each button. If your needs aren't that complex then
        // storing on a model is useful for modifying the validation functions
        // via mixins.
        validationFunctions: shippingStepValidation.validationFunctions([
            'validateEmailField',
            'validateShippingAddress'
        ]),

        initialize: function() {
            var self = this;

            this._super();
            this.template = 'Dyson_SinglePageCheckout/onepage/shipping/shipping-continue-btn';
            this.disabled(false);

            this.buttonClasses = '';
            this.isVisible = ko.observable(true);
            this.disableButtonBecauseLoading = ko.observable(false);

            var button_text = $t('Continue to payment');
            this.title = ko.observable(button_text);

            // Let's disable the continue button while totals are loading, it's
            // sensible as the payment methods have to do things before we
            // should be able to continue.
            checkoutTotals.isLoading.subscribe(function(totals_are_loading) {
                self.disableButtonBecauseLoading(totals_are_loading);
            });

            // Disable the continue button and change the text when disableButtonBecauseLoading is
            // set to true.
            this.disableButtonBecauseLoading.subscribe(function(isLoading) {
                self.disabled(isLoading);
                if (isLoading) {
                    self.title($t('Loading...'));
                }
                else {
                    self.title(button_text)
                }
            });
        },

        action: function() {
            if (this.stepIsValid()) checkoutStepsModel.currentStep(checkoutStepsModel.currentStep() + 1);
        },

        stepIsValid: function() {
            var result = false;
            if (this.validationResults()) {
                result = true;
            } else {
                this.focusFirstError();
            }
            return result;
        },

        /**
         *
         * @returns {boolean}
         */
        validationResults: function() {
            var self = this;
            // We collect the validation result of each method in the
            // states_of_validity object, keyed by the method name with boolean
            // value for valid/invalid.
            var states_of_validity = {};

            // Run through each method in the observable array, adding the bool
            // result to the states_of_validation obj for each.
            _.each(shippingStepValidation.validationFunctions(), function(function_name) {
                states_of_validity[function_name] = self.executeFunctionByName(function_name, self);
            });
            //console.dir(states_of_validity);

            // All we're doing here is filtering the states_of_validity obj by
            // any values of false (i.e. not valid).
            var failed_validation_items =_.filter(states_of_validity, function(value) { return !!!value; });

            // If the resultant array created by filter has length then we know
            // at least one thing is not valid.
            return !failed_validation_items.length;
        },



        validateEmailField: function() {
            var valid = true;
            // Validate customer email field which is in its own form.
            var $email_form = $('form[data-role="email-with-possible-login"]');
            if ($email_form.length && $email_form.is(':visible')) {
                valid = $email_form.validation() && $email_form.validation('isValid');
            }
            return valid;
        },

        validateShippingAddress: function() {
            // Validate shipping address form.
            var valid = true;
            registry.get('index = shippingAddress', function(component) {
                if ($('.form-shipping-address').is(':visible')) {
                    component.source.set('params.invalid', false);
                    component.triggerShippingDataValidateEvent();
                    valid = !component.source.get('params.invalid');
                }
            });
            return valid;
        },

        focusFirstError: function() {
            // The email component handles focus to errored field quite nicely
            // already, so let it handle that.
            if ($('#customer-email-fieldset #customer-email.mage-error').length) {
                return;
            }

            // The rest of the shipping fields now.
            $('.checkout-shipping-address input.mage-error, select.mage-error._error:first, .checkout-shipping-address .field._error:first').each(function (i, el) {
                if ($(el).find('.input-text').length) {
                    $(el).find('.input-text').focus();
                } else if ($(el).find('select.select').length) {
                    $(el).find('select.select').focus();
                }
            });
        },

        // Basically this calls a method on a given context for us. Stole it from
        // SO because it avoids using 'eval'. Usage:
        // e.g. executeFunctionByName(validateEmailField, this).
        executeFunctionByName: function(functionName, context /*, args */) {
            var args = Array.prototype.slice.call(arguments, 2);
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            for(var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            return context[func].apply(context, args);
        }



    });


});

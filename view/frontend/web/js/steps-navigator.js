/**
 *
 * Main steps navigator for the accordion for Amasty SPC checkout.
 *
 * Ideally this should be a component in its own right that we extend for the
 * checkout steps component. That way we can leave this to do what it does best
 * and extend the methods on offer to provide custom functionality that pertains
 * only to the checkout. That's the plan.
 *
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Magento_Checkout/js/model/payment-service',
    'Dyson_SinglePageCheckout/js/model/checkout-steps',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/checkout-loader',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/validation'
], function ($, ko, Component, registry, paymentService, checkoutSteps, checkoutTotals, checkoutLoader, checkoutDataResolver) {

    function doConsoleLog(msg) {
        // It's useful to have some verbose logging for this component.
        // We can turn all the logs on or off from here:
        // TODO: Maybe switch on/off debug mode depending on env setting from env.php
        // console.log(msg);
    }

    return Component.extend({

        // By default loading is happening on page load.
        isLoading: ko.observable(true),

        initialize: function() {
            var self = this;

            // Crude check to see if checkout is loaded. Not proud of this. With
            // more time we should extend checkout-loader to provide us
            // potentially with an event to observe.
            var checkCheckoutIsLoaded = setInterval(function() {
                if (!$('#checkout-loader').length) {
                    self.isLoading(false);
                    clearInterval(checkCheckoutIsLoaded);
                }
            }, 100);


            this._super();


            // Subscribe to isLoading observable property for setting up
            // first load.
            var initCheckoutWhenLoaded = this.isLoading.subscribe(function(is_loading) {
                if (is_loading === false) {
                    // Checkout is loaded. We are safe to proceed with changing
                    // to the first step.
                    checkoutSteps.currentStep.subscribe(self.processChangeStep.bind(self));
                    checkoutSteps.currentStep(1);
                }
            });
            // Bin the first load subscriber when isLoading is false.
            this.isLoading.subscribe(function(is_loading) {
                if (!is_loading) initCheckoutWhenLoaded.dispose();
            });


            // Subscribe to completed steps observable array.
            checkoutSteps.completedSteps.subscribe(self.completedStepsActions.bind(self));

            // Subscribe to checkout summary totals is loading, update our
            // isLoading in case we want to prevent change step etc till
            // finished.
            // checkoutTotals.isLoading.subscribe(function(summary_is_loading) {
            //     doConsoleLog('Totals loading ' + summary_is_loading);
            //     self.isLoading(summary_is_loading);
            // });
        },

        /**
         * Synchronously call the methods responsible for changing the step.
         * @param step_id
         */
        processChangeStep: function(step_id) {
            var self = this;
            self.beforeChangeStep(step_id, function(){
                self.changeStep(step_id, function(){
                    self.afterChangeStep(step_id, function(){});
                });
            });
        },

        beforeChangeStep: function(step_id, callback) {
            var self = this;
            doConsoleLog('Before change step');
            // Validation methods can go here, now is the time to block the
            // changing of the step and preventing the callback if required. We
            // probs need to initialise a loader to give the user feedback if
            // there's a delay between button click and perceived step change.
            if (this.isLoading()) {
                doConsoleLog('Waiting to change step.');
                //$('body').loader().show();

                // Wait till loading is done before we make our callback.
                this.isLoading.subscribe(function(is_loading) {
                    if (!is_loading) {
                        doConsoleLog('Can go ahead now...');
                        //$('body').loader().hide();
                        callback();
                    }
                });
            }
            else {
                callback();
            }
        },

        changeStep: function(step_id, callback) {
            doConsoleLog('Changing step to ' + step_id);

            // Do changey step things.
            $('.checkout-block').removeClass('checkout-block--active').attr('data-status','closed');

            var $openTarget = $(".checkout-block[data-order='" + step_id +"']");
            $openTarget.addClass('checkout-block--active');
            $openTarget.attr('data-status','open');


            window.setTimeout(function(){
                // Grab the current data-order value of the target section
                var panelOrder = $openTarget.data('order');

                if (step_id === 1)
                // Push the relevant event and virtualPagePath to the GTM dataLayer
                if (typeof window.dataLayer !== 'undefined') {
                    window.dataLayer.push({
                        event:'virtualPageView',
                        virtualPagePath: '/checkout'
                    });
                }

                else if (panelOrder === '2') {
                    if (typeof window.dataLayer !== 'undefined') {
                        window.dataLayer.push({
                            event:'virtualPageView',
                            virtualPagePath: '/checkout/payment'
                        });
                    }
                }
            }, 10);

            // Add the previous step ID to the observable array of completed
            // steps.
            if (step_id > 1) checkoutSteps.completedSteps.push(step_id - 1);

            // Now callback after the specified css animation time.
            callback();
        },

        afterChangeStep: function(step_id, callback) {
            var self = this;
            doConsoleLog('After change step');
            // Things to do after the step has changed fully, e.g. animate to
            // section.

            var $openTarget = $(".checkout-block[data-order='" + step_id +"']");
            $openTarget.addClass('checkout-block--active');
            $openTarget.attr('data-status','open');

            var panelOrder = $openTarget.data('order');

            if (step_id === 1) {
                if (typeof window.dataLayer !== 'undefined') {
                    window.dataLayer.push({
                        event:'virtualPageView',
                        virtualPagePath: '/checkout'
                    });
                }
            }

            if (panelOrder === 2) {
                if (typeof window.dataLayer !== 'undefined') {
                    window.dataLayer.push({
                        event:'virtualPageView',
                        virtualPagePath: '/checkout/payment'
                    });
                }
            }

            // No animation for mobile first open panel.
            if ($(window).width() < 1023 && step_id === 1) {
                callback();
            }
            else {
                this.animateCheckoutTop(callback());
            }

        },

        completedStepsActions: function(completed_step_ids) {
            var self = this;
            completed_step_ids.forEach(function(step_id) {
                doConsoleLog('Step ' + step_id + ' completed!');
                $('.checkout-block[data-order='+step_id+']').addClass('checkout-block--complete');
                $('.checkout-block[data-order='+step_id+'] .checkout-block__header').on('click', self.editStep);
            });

        },

        editStep: function() {
            var self = this;
            doConsoleLog('Current step: ' + checkoutSteps.currentStep());

            // Poor - make better by passing ID of step we wish to edit.
            var prev_step_id = checkoutSteps.currentStep() - 1;
            doConsoleLog('We\'re going to edit step ' + prev_step_id + ' now....');
            $('.checkout-block[data-order='+ prev_step_id +']').removeClass('checkout-block--complete');
            $('.checkout-block[data-order='+ prev_step_id +'] .checkout-block__header').off('click');
            // Remove the step we're changing back to from the completedSteps
            // array.
            checkoutSteps.completedSteps.remove(prev_step_id);
            // Change the step.
            checkoutSteps.currentStep(prev_step_id);
        },


        animateCheckoutTop: function(callback) {
            if ($(".checkout-block--details")) {
                $('html, body').animate({
                    scrollTop: $('.checkout-block--details').offset().top - 30
                }, '10', callback);
            }
        },

    });
});

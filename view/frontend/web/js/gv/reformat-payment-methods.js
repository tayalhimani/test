define([
    'jquery',
    'Magento_Checkout/js/model/payment-service',
], function (
    $,
    paymentService
) {
    // Ensure checkout JS is fully loaded

    var $documentBody = $('body');

    // var checkExist = setInterval(function() {
    //     // Checking pincode field is complete before rendering payment methods.
    //     if($('.dyson-in').length && $('.payment-method').length && $('[name="shippingAddress.postcode"] .complete').length) {
    //         renderPaymentMethods();
    //         clearInterval(checkExist);
    //     } else if (!$('.dyson-in').length && $('.payment-method').length) {
    //         // If not india, render payment methods as normal.
    //         renderPaymentMethods();
    //         clearInterval(checkExist);
    //     }
    // }, 100);

    //
    // var checkExist = setInterval(function() {
    //     // Checking pincode field is complete before rendering payment methods.
    //     if($('.dyson-in').length && $('.payment-method').length && $('[name="shippingAddress.postcode"] .complete').length) {
    //         renderPaymentMethods();
    //         clearInterval(checkExist);
    //     } else if (!$('.dyson-in').length && $('.payment-method').length) {
    //         // If not india, render payment methods as normal.
    //         renderPaymentMethods();
    //         clearInterval(checkExist);
    //     }
    // }, 100);


    // $document.on('click', '[name="continue_to_payment"]', renderPaymentMethods);


});

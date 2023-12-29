define([
    'jquery'
], function ($) {

    // Check that the checkout summary actually exists first
    var checkExist = setInterval(function() {
        if ($('.checkout-block__summary__header').length) {
            initFunctions();
            clearInterval(checkExist);
        }
    }, 100);

    function initFunctions() {
        var siteHeaderHeight = $('header.page-header').outerHeight();

        $(window).scroll(function() {
            if ($(this).scrollTop() > siteHeaderHeight) {
                $('.am-opc-summary').addClass('am-opc-summary--stick');
            } else {
                $('.am-opc-summary').removeClass('am-opc-summary--stick');
            }
        });

        $(document.body).on('keypress click', '.checkout-block__summary__header', function(e){
            e.stopPropagation(); e.preventDefault();
            $('.am-opc-summary').toggleClass('am-opc-summary--open');
            if( $('.am-opc-summary').hasClass('am-opc-summary--open') ) {
                $('.checkout-block__summary__header').attr("aria-expanded","true");
            }
            else {
                $('.checkout-block__summary__header').attr("aria-expanded","false");
            }
            return false;
        });
    }
});

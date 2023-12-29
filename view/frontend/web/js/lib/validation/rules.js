define([
    'jquery',
    'underscore',
    'moment',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/lib/validation/utils'
], function ($, _, moment,translate,customerdata,utils) {
    'use strict';

    var telephone_validate_length = window.checkoutConfig.telephone_validate_length;
    var validate_massage;
    var countryData = customerdata.get('directory-data');
    let countryObj = countryData();
    let countryId = Object.keys(countryObj);
    var dialCode_shipping = '';
    var dialCode_billing = '';

    if (telephone_validate_length) {
        if (countryId[0] == "MX") {
            validate_massage = $.mage.__(`Ingresa tu teléfono a ${telephone_validate_length} dígitos`)
        } else if (countryId[0] == "TR") {
            validate_massage = $.mage.__(`Lütfen ${telephone_validate_length} karakter girin.`);
        } else if (countryId[0] == "SA"){
            validate_massage = $.mage.__(`Please insert a symbol made of ${telephone_validate_length} digits only`);
        } else if (countryId[0] == "MY"){
            validate_massage = $.mage.__(`Please enter a valid mobile number`);
        } else if (countryId[0] == "VN"){
            validate_massage = $.mage.__('Please enter a valid mobile number');
        } else if (countryId[0] == "SG"){
            validate_massage = $.mage.__(`Please enter ${telephone_validate_length} digits only.`);
        }
    } else {
        if (countryId[0] == "TR") {
            validate_massage = $.mage.__(`Lütfen 9 karakter girin.`);
        } else if (countryId[0] == "MX") {
            validate_massage = $.mage.__(`Ingresa tu teléfono a 10 dígitos`);
        } else if (countryId[0] == "SA"){
            validate_massage = $.mage.__(`Please insert a symbol made of 8 digits only`);
        } else if (countryId[0] == "MY"){
            validate_massage = $.mage.__(`Please enter a valid mobile number`);
        } else if (countryId[0] == "VN"){
            validate_massage = $.mage.__('Please enter a valid mobile number');
        } else if (countryId[0] == "SG"){
            validate_massage = $.mage.__('Please enter 8 digits only.');
        }
    }
    if(!countryId[0]){
        var defaultCountryId = window.checkoutConfig.defaultCountryId;
            validate_massage = $.mage.__('Please enter valid digits only.');
    }

    return function (validator) {
        var validators = {
            'telephone-sa': [
                function (value) {
                    var validate_length = 8;
                    if (telephone_validate_length) {
                        validate_length = telephone_validate_length;
                    }
                    return !value || (value.length == validate_length &&
                        !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                },
                validate_massage
            ],
            'telephone-sg': [
                function (value) {
                    var validate_length = 8;
                    if (window.checkoutConfig.dialcode.dialcode) {
                        dialCode_shipping = $("body").find(".form-shipping-address .input-label-overlay span").text().toString();
                        dialCode_billing  = $("body").find(".payment-method-billing-address .input-label-overlay span").text().toString();
                        let dialcode = window.checkoutConfig.dialcode.dialcode.toString();
                        let match_ship = dialCode_shipping.includes(dialcode);
                        let match_bill = dialCode_billing.includes(dialcode);

                        if (telephone_validate_length) {
                            validate_length = telephone_validate_length;
                        }
                        if (match_ship) {
                            return !value || (value.length == validate_length &&
                                !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                        } else {
                            return true;
                        }
                    } else {
                        return !value || (value.length == validate_length &&
                            !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                    }
                },
                validate_massage
            ],
            'telephone-sg-bill': [
                function (value) {
                    var validate_length = 8;
                    if (window.checkoutConfig.dialcode.dialcode) {
                        dialCode_shipping = $("body").find(".form-shipping-address .input-label-overlay span").text().toString();
                        dialCode_billing  = $("body").find('.payment-method-billing-address .fieldset:visible .input-label-overlay span').text().toString();
                        let dialcode = window.checkoutConfig.dialcode.dialcode.toString();
                        let match_ship = dialCode_shipping.includes(dialcode);
                        let match_bill = dialCode_billing.includes(dialcode);
                        if (telephone_validate_length) {
                            validate_length = telephone_validate_length;
                        }
                        if (match_bill) {
                            return !value || (value.length == validate_length &&
                                !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                        } else {
                            return true;
                        }
                    } else {
                        return !value || (value.length == validate_length &&
                            !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                    }

                },
                validate_massage
            ],
            'telephone-my': [
                function (value) {
                    var validate_length = 16;
                    if (telephone_validate_length) {
                        validate_length = telephone_validate_length;
                    }
                    return !value || (value.length < validate_length &&
                    !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                },
                validate_massage
            ],
            'telephone-vn': [
                function (value) {
                    var validate_length = '';
                    if (telephone_validate_length) {
                        validate_length = telephone_validate_length;
                    }
                    if (validate_length) {
                        return utils.isEmptyNoTrim(value) || (value.length < validate_length && !isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                    } else {
                        return utils.isEmptyNoTrim(value) || (!isNaN(utils.parseNumber(value)) && /^\d+$/.test(value));
                    }
                },
                validate_massage
            ]
        };

        validators = _.mapObject(validators, function (data) {
            return {
                handler: data[0],
                message: data[1]
            };
        });

        return $.extend(validator, validators);
    };
});

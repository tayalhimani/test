define([
    'ko'
], function (ko) {
    return {
        checkoutContainerAdditionalClasses: ko.observableArray(['dyson--spc-enabled']),
        currentStep: ko.observable(null),
        completedSteps: ko.observableArray([]),
    }
});
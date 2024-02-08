define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Biztech_Ausposteparcel/js/model/shipping-rates-validator',
        'Biztech_Ausposteparcel/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        ausposteParcelShippingRatesValidator,
        ausposteParcelShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('ausposteParcel', ausposteParcelShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('ausposteParcel', ausposteParcelShippingRatesValidationRules);
        return Component;
    }
);
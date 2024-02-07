define([
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/action/select-shipping-method',
    'mage/utils/wrapper'
], function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor, selectShippingMethodAction, wrapper) {
        'use strict';
        return function (target) {

            target.getRates = wrapper.wrapSuper(target.getRates, function (address) {
                var cache;

                console.log('shipping-rate-processor (customer-address): ');
                console.log(address);

                var isResidential = address.customAttributes.find(function(cust) { return cust.attribute_code === 'is_residential' });
                if (isResidential) {
                    quote.customData = {
                        is_residential: isResidential
                    };
                }

                if(!address.postcode || !address.regionId || !address.countryId) {
                    console.log('Postcode, RegionID, or CountryID is invalid. Do not trigger estimate-shipping-methods');

                    console.log('Clear selected shipping method');
                    selectShippingMethodAction(null);
                    return this._super(address);
                }

                shippingService.isLoading(true);
                cache = rateRegistry.get(address.getKey());

                if (cache) {
                    shippingService.setShippingRates(cache);
                    shippingService.isLoading(false);
                } else {
                    storage.post(
                        resourceUrlManager.getUrlForEstimationShippingMethodsByAddressId(),
                        JSON.stringify({
                            addressId: address.customerAddressId
                        }),
                        false
                    ).done(function (result) {
                        rateRegistry.set(address.getKey(), result);
                        shippingService.setShippingRates(result);
                    }).fail(function (response) {
                        shippingService.setShippingRates([]);
                        errorProcessor.process(response);
                    }).always(function () {
                        shippingService.isLoading(false);
                    }
                    );
                }

                return this._super(address);
            });

            return target;
        }
    }
);
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/url',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-service',
], function ($, url, wrapper, quote, rateRegistry, checkoutData, selectShippingAddressAction, selectShippingMethodAction, shippingService) {
    'use strict';

    function setAddressType(shippingAddress) {

        // if quote got customer stored address already no need to set this anymore
        // and shipping address sometimes does not have customAttributes
        if (quote.customData) {
            return shippingAddress;
        }

        // if empty then lets create it
        if (!shippingAddress.customAttributes) {
            shippingAddress.customAttributes = [];
        }

        var addressType = ["Residential", "Business"];
        var addressTypeId = jQuery('.is-business-residential input:checked').val();
        var isResidentialCustAttrIdx = shippingAddress.customAttributes.findIndex(function(custArr) { custArr.attribute_code === '_is_residential' })
        var customLabel = "Address Type: " + addressType[addressTypeId - 1];

        if (isResidentialCustAttrIdx < 0) {
            shippingAddress.customAttributes.push({
                attribute_code: "is_residential",
                label: customLabel,
                value: customLabel
            });
        } else {
            shippingAddress.customAttributes[isResidentialCustAttrIdx].label = customLabel;
            shippingAddress.customAttributes[isResidentialCustAttrIdx].value = customLabel;
        }

        return shippingAddress;
    }

    $(function () {

        var looking_up = false;
        var lookupCtr = 0;
        var initLookupVal = '';
        var isInternational = false;

        function init() {
            console.log("init");

            // turnoff autocomplete
            $('.field.address-lookup-input-field input').attr('autocomplete', 'one-time-code');

            var address = quote.shippingAddress();

            if (address && address.customAttributes) {

                var isResidential = address.customAttributes.find(function(cust) { return cust.attribute_code === 'is_residential' });

                if (isResidential) {
                    changeIsResidential(isResidential.value == 1);
                }
            } else {
                changeIsResidential($('.is-business-residential input[type="radio"]:checked').val() == 1);
            }

        }

        function clearShippingAddressFields(element) {
            element.closest('form').find('input[name=postcode]').val('').trigger('change');
            element.closest('form').find('input[name=city]').val('').trigger('change');
            element.closest('form').find('select[name=region_id]').val('').trigger('change');
        }

        function refreshShippingMethods() {
            var address = quote.shippingAddress();

            rateRegistry.set(address.getKey(), null);
            rateRegistry.set(address.getCacheKey(), null);

            quote.shippingAddress(address);
        }

        function changeIsResidential(isResidential) {
            var linkUrl = url.build('fusedship/index/checkoutaddresstype');

            $.ajax({
                type: 'GET',
                url: linkUrl,
                data: {
                    is_residential: isResidential
                },
                success: function (response) {
                    console.log('success response', response);


                    // refresh only when theres an update happened
                    if (response && response.updated) {
                        refreshShippingMethods();
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function buildAddressSelection(data) {
            var optionsHtml = '<option>Please select</option>';

            for(var i=0; i < data.length; i++) {

                var magentoRegionData = data[i].magentoRegionData;

                var region_id = null;
                var region_code = null;
                var region_name = null;

                if(magentoRegionData) {
                    region_id = magentoRegionData.region_id;
                    region_code = magentoRegionData.code;
                    region_name = magentoRegionData.name;
                }

                var selected = '';


                if(initLookupVal != '' && lookupCtr == 1) {
                    selected = data[i].description == initLookupVal ? 'selected' : '';
                }

                optionsHtml += '<option value="'+ data[i].id +'" data-postcode="'+ data[i].postcode +'" data-suburb="'+ data[i].suburb +'" data-country="'+ data[i].country.code2 +'" data-state-code="'+ region_code +'" data-state-name="'+ region_name +'" data-region-id="'+ region_id +'" '+ selected +'>' + data[i].description + '</option>';
            }

            return optionsHtml;
        }

        function addressLookup(elem, q) {
            if(looking_up) return;

            var linkUrl = url.build('fusedship/index/addresslookup');

            var result_select = $(elem).closest('form').find('.field.address-lookup-results-field select');

            looking_up = true;

            $(result_select).closest('.field').hide();

            clearShippingAddressFields(elem);

            $.ajax({
                type: 'GET',
                url: linkUrl,
                data: {
                    q: q
                },
                success: function (response) {
                    looking_up = false;
                    var data = response.data;

                    var optionsHtml = buildAddressSelection(data);

                    $(result_select).closest('.field').insertAfter($(elem).closest('.field'));

                    result_select.html(optionsHtml);

                    if(optionsHtml != '') {
                        $(result_select).closest('.field').show();

                        $(document).find('.field.address-lookup-results-field select').trigger('change');
                    }


                    $(elem).removeAttr('disabled');
                },
                error: function (err) {
                    looking_up = false;
                    $(elem).removeAttr('disabled');
                    console.log(err);
                }
            });
        }

        function checkIsInternational() {

            isInternational = $('select[name="country_id"]').val() !== 'AU';

            // hide/show, enable/disabled some of the fields needed if its international or not
            if (!isInternational) {
                $('.field.address_lookup_input_field input').show();
                $('.address-lookup-input-field').show();
                $('.is-business-residential').show();
            } else {
                $('.field.address_lookup_input_field input').hide();
                $('.address-lookup-input-field').hide();
                $('.is-business-residential').hide();
            }

            $('#checkout-step-shipping_method').fadeIn();
        }

        function delay(callback, ms) {
            var timer = 0;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    callback.apply(context, args);
                }, ms || 0);
            };
        }



        // ON CHANGE EVENTS ---------------------------------------------------

        $(document).on('change', '.field.address-lookup-results-field select', function (e) {

            if($(this).val() == 'Please select') return;

            var postcode = $(this).find('option:selected').data('postcode');
            var country_code = $(this).find('option:selected').data('country');
            var region_id = $(this).find('option:selected').data('regionId');
            var city = $(this).find('option:selected').data('suburb');

            var selected_text = $(this).find('option:selected').text();


            $(this).closest('form').find('.field.address-lookup-input-field input').val(selected_text).trigger('change');


            $(this).closest('form').find('input[name=postcode]').val(postcode).trigger('change');
            $(this).closest('form').find('input[name=city]').val(city).trigger('change');
            $(this).closest('form').find('select[name=country_id]').val(country_code);
            $(this).closest('form').find('select[name=region_id]').val(region_id).trigger('change');

            $(this).closest('form').find('.field.address-lookup-results-field select').closest('.field').hide();


        });

        $(document).on('change', '[name="country_id"]', function() {
            console.log("on change country init");

            checkIsInternational();
        });


        $(document).on('click', '.is-business-residential input[type="radio"]', function (e) {
            changeIsResidential($(this).val() == 1);
        });


        $(document).on('click', '.action.action-select-shipping-item', function() {
            console.log("select shipping item");

            // init shipping address / items
            init();
        });


        // ON KEY UP EVENTS ---------------------------------------------------

        $(document).on('keyup', '.field.address-lookup-input-field input', function (e) {


            if (isInternational) {
                return;
            }

            var q = $(this).val();

            // TODO NEED TO UPDATE THIS TRIGGER CHARACTER COUNT
            if(q.length <= 3)  return;

            lookupCtr++;

            addressLookup($(this), q);
        });



        setTimeout(function () {
            console.log('set-shipping-information-mixin.js: initLiveRates()');
            init();
            checkIsInternational();
        }, 3000);
    });

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {

            var shippingAddress = quote.shippingAddress();

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            if (shippingAddress.customAttributes) {
                shippingAddress['extension_attributes']['address_lookup_results_field'] = shippingAddress.customAttributes['address_lookup_results_field'];
                shippingAddress['extension_attributes']['address_lookup_input_field'] = shippingAddress.customAttributes['address_lookup_input_field'];
            }

            shippingAddress = setAddressType(shippingAddress);


            rateRegistry.set(shippingAddress.getKey(), null);
            rateRegistry.set(shippingAddress.getCacheKey(), null);

            quote.shippingAddress(shippingAddress);

            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});
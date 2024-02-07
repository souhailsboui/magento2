define([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-service'
],
function ($, url, rateRegistry, quote, selectShippingMethodAction, shippingService) {
    'use strict';

    $(function () {

        var isLookingUp = false;
        var lookupCtr = 0;
        var initLookupVal = '';
        var isResidential = null;
        var isInternational = false;

        function init() {
            console.log("init");

            // turnoff autocomplete
            $('.field.address-lookup-input-field input').attr('autocomplete', 'one-time-code');
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

        }

        function refreshShippingMethods() {
            var address = quote.shippingAddress();

            rateRegistry.set(address.getKey(), null);
            rateRegistry.set(address.getCacheKey(), null);

            quote.shippingAddress(address);
        }


        function addressLookup(elem, q) {

            if (isLookingUp) return;

            var linkUrl = url.build('fusedship/index/addresslookup');

            var result_select = $(elem).closest('form').find('.field.address-lookup-results-field select');

            isLookingUp = true;

            $(result_select).closest('.field').hide();

            clearShippingAddressFields(elem);

            $.ajax({
                type: 'GET',
                url: linkUrl,
                data: {
                    q: q
                },
                success: function (response) {
                    isLookingUp = false;
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
                    isLookingUp = false;
                    $(elem).removeAttr('disabled');
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

        function changeIsResidental() {


            var linkUrl = url.build('fusedship/index/checkoutaddresstype');

            $.ajax({
                type: 'GET',
                url: linkUrl,
                data: {
                    is_residential: isResidential
                },
                success: function (response) {
                    console.log('success response', response);

                    refreshShippingMethods();
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        // ON CHANGE EVENTS ---------------------------------------------------

        $(document).on('change', '.field.address-lookup-results-field select', function (e) {

            if($(this).val() == 'Please select') return;

            var postcode = $(this).find('option:selected').data('postcode');
            var country_code = $(this).find('option:selected').data('country');
            var location_id = $(this).val();
            var region_id = $(this).find('option:selected').data('regionId');
            var city = $(this).find('option:selected').data('suburb');
            var region_code = $(this).find('option:selected').data('stateCode');
            var region_name = $(this).find('option:selected').data('stateName');

            var selected_text = $(this).find('option:selected').text();


            $(this).closest('form').find('.field.address-lookup-input-field input').val(selected_text).trigger('change');


            $(this).closest('form').find('input[name=postcode]').val(postcode).trigger('change');
            $(this).closest('form').find('input[name=city]').val(city).trigger('change');
            $(this).closest('form').find('select[name=country_id]').val(country_code);
            $(this).closest('form').find('select[name=region_id]').val(region_id).trigger('change');

            $(this).closest('form').find('.field.address-lookup-results-field select').closest('.field').hide();

        });

        function clearShippingAddressFields(element) {
            element.closest('form').find('input[name=postcode]').val('').trigger('change');
            element.closest('form').find('input[name=city]').val('').trigger('change');
            element.closest('form').find('select[name=region_id]').val('').trigger('change');
        }


        $(document).on('click', '.is-business-residential input[type="radio"]', function (e) {
            isResidential = $(this).val() == 1;
            changeIsResidental();
        });

        $(document).on('change', '[name="country_id"]', function() {
            console.log("on change country init");

            checkIsInternational();
        });


        // ON KEY UP EVENTS ---------------------------------------------------

        $(document).on('keyup', '.field.address-lookup-input-field input', function (e) {


            var q = $(this).val();

            if(q.length <= 3)  return;

            lookupCtr++;

            addressLookup($(this), q);
        });


        // init load
        setTimeout(function() {
            init();
            checkIsInternational();
            console.log('shipping-estimation.js: initLiveRates()');
        }, 3000);

    });

    return function(originalShippingEstimation){
        return originalShippingEstimation.extend({

        });
    };
});

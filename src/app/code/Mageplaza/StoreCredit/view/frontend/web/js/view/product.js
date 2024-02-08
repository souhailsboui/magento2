/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(['jquery', 'Magento_Catalog/js/price-utils'], function ($, priceUtils) {
    "use strict";

    var information = window.storeCreditInformation;

    /**
     * Format price
     */
    var formatPrice = function (amount) {
        return priceUtils.formatPrice(amount, information.priceFormat);
    };

    /**
     * Get credit price
     */
    var getPriceFromAmount = function (amount) {
        return formatPrice(amount * information.creditRate);
    };

    /**
     * Convert price
     */
    var convertPrice = function (amount, toBase) {
        if (typeof toBase === 'undefined') {
            return parseFloat(amount) * information.currencyRate;
        }

        return parseFloat(amount) / information.currencyRate;
    };

    /**
     * Apply change for input credit amount
     */
    var validateAmount = function (amount) {
        if (information.minCredit && amount < information.minCredit) {
            amount = information.minCredit;
        }

        if (information.maxCredit && amount > information.maxCredit) {
            amount = information.maxCredit;
        }

        $('.credit-amount-convert').val(amount);
        $('.credit-amount').val(convertPrice(amount, true));

        return getPriceFromAmount(amount);
    };

    $('.credit-amount-convert').on('change', function () {
        var price = validateAmount($(this).val());

        $('#product-price-' + information.productId).find('.price').text(price);
        $('.credit-amount').val(convertPrice(amount, true));

        $('[data-role=priceBox]').trigger('updatePrice');
    });

    if ($('.box-tocart').hasClass('update')) {
        $('#product-price-' + information.productId).find('.price').text(formatPrice(information.rangerUpdate));
        $('.credit-amount-convert').val(information.rangerUpdate / information.creditRate);
        $('.credit-amount').val(convertPrice(information.rangerUpdate / information.creditRate, true));

        $('[data-role=priceBox]').trigger('updatePrice');
    }
});

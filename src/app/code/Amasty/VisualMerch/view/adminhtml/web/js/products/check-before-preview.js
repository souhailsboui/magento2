define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    var merchUiProductsMixin = {
        /**
         * @returns {void}
         */
        updateProductsByConditionsAction: function () {
            if (window.allProductsCount > 5000) {
                alert({
                    title: $.mage.__('Attention'),
                    content: $.mage.__('The products preview process requires indexing once changing or creating new conditions.' +
                        'It will start automatically when you save a rule or according to the schedule.')
                });
            } else {
                this._super({'force_reset': 1});
            }
        }
    };

    return function (targetWidget) {
        $.widget('mage.ammerchuiProducts', targetWidget, merchUiProductsMixin);

        return $.mage.ammerchuiProducts;
    };
});

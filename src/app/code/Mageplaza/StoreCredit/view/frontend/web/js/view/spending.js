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

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Mageplaza_StoreCredit/js/action/update-credit',
    'mage/translate'
], function (ko, $, Component, customerData, updateCreditAction) {
    'use strict';

    var config = customerData.get('customer'),
        resolved = false,
        isReload = true;

    return Component.extend({
        defaults: {
            template: 'Mageplaza_StoreCredit/container/spending'
        },
        spending: ko.observable(),
        label: ko.computed(function () {
            return $.mage.__('Spend your Store Credit') + ' (' + config().convertedBalance + ')';
        }),

        initialize: function () {
            this._super();

            var self = this;

            if (isReload) {
                customerData.reload(['customer'], false).done(function () {
                    config = customerData.get('customer');

                    self.spending(config().isSpendingCredit);

                    resolved = true;
                });

                isReload = false;
            }
        },

        initObservable: function () {
            this._super();

            this.spending.subscribe(function (value) {
                if (resolved) {
                    updateCreditAction(value ? config().balance : 0);
                }
            });

            return this;
        }
    });
});
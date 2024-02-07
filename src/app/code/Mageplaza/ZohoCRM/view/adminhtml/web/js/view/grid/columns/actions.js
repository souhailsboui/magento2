/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Ui/js/grid/columns/actions',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, Column, $t) {
    'use strict';

    return Column.extend({
        modal: {},

        /**
         * @param actionIndex
         * @param recordId
         * @param action
         */
        defaultCallback: function (actionIndex, recordId, action) {
            var row = this.rows[action.rowIndex];

            this.modal[action.rowIndex] = $(row.popup_content).modal({
                type: 'slide',
                title: $t('Queue #') + row.queue_id,
                modalClass: 'mpzoho-modal-queue',
                innerScroll: true,
                buttons: []
            });

            this.modal[action.rowIndex].trigger('openModal');
        }
    });
});

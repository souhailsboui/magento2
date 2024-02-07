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
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        /**
         * @param record
         * @returns {*}
         */
        getLabel: function (record) {
            var label = this._super(record),
                options = this.options;

            switch (record[this.index]) {
                case options[1].value:
                    label = this.createLabelElement('notice', label);
                    break;
                case options[2].value:
                    label = this.createLabelElement('critical', label);
                    break;
                case options[0].value:
                    label = this.createLabelElement('minor', label);
                    break;
            }

            return label;
        },

        /**
         * @param classCss
         * @param label
         * @returns {string}
         */
        createLabelElement: function (classCss, label) {
            return '<span class="grid-severity-' + classCss + '"><span>' + label + '</span></span>';
        }
    });
});

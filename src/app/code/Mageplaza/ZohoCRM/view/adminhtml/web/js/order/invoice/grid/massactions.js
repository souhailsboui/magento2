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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/grid/massactions',
], function (_, utils, Component) {
    'use strict';

    return Component.extend({

        /**
         * Fix bug on 2.3.3
         * @param action
         * @param data
         */
        defaultCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            /**
             * 2.3.3 removed
             */
            if (itemsType === 'excluded' && data.selected && data.selected.length) {
                itemsType = 'selected';
                data[itemsType] = _.difference(data.selected, data.excluded);
            }

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            utils.submit({
                url: action.url,
                data: selections
            });
        },
    });
});

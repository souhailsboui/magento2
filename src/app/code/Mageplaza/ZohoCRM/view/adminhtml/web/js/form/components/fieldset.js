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
    'Magento_Ui/js/form/components/fieldset',
], function (Component) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();

            if (
                !this.source.data ||
                (this.source.data.product && !this.source.data.product.current_product_id)
            ) {
                this.visible(false);
            }
        },
    });
});

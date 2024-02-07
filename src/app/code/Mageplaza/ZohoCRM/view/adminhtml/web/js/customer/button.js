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
    'jquery',
    'prototype'
], function ($) {

    return function (config, element) {
        config = config || {};
        $(element, '#add_to_zoho-button').on('click', function () {
            window.location.href = config.url;
        });
        $('#add_to_zoho-button').on('click', function () {
            if (config.default) {
                window.location.href = config.url;
            }
        });
    };
});

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
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/alert',
    'uiRegistry'
], function ($, Component, alert, uiRegistry) {
    'use strict';

    return Component.extend({
        currentResult: {},
        totalSync: 0,

        /**
         * Applies specified action.
         *
         * @param actionIndex
         */
        applyAction: function (actionIndex) {
            var data = this.getSelections();

            if (!data.total) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }

            if (actionIndex === 'sync') {
                if (data.excludeMode) {
                    var self = this;

                    this.currentResult = {};
                    this.disableElement(true);
                    $.ajax({
                        url: window.ZohoCRM.estimateUrl,
                        type: 'post',
                        dataType: 'json',
                        data: {
                            type: 'all',
                            excluded: data.excluded,
                            selected: data.selected,
                            filters: data.params.filters,
                            namespace: data.params.namespace
                        },
                        success: function (result) {
                            if (result.status) {
                                self.currentResult = result;
                                $("#mp-sync .message").hide();

                                if (self.currentResult.total > 0) {
                                    $("#sync-percent").text('0%');
                                    $(".progress-bar").removeAttr('style');
                                    self.currentResult.percent = 0;
                                    $("#progress-content").show();
                                    self.totalSync = 0;
                                    self.syncData(0);
                                } else {
                                    self.showMessage('message-notice', result.message);
                                    self.disableElement(false);
                                }
                            } else {
                                self.showMessage('message-error', result.message);
                                self.disableElement(false);
                            }

                        },
                        error: function () {
                            self.showMessage('message-error', window.ZohoCRM.estimateErrorMessage);
                            self.disableElement(false);
                        }
                    });
                } else {
                    $("#sync-percent").text('0%');
                    $(".progress-bar").removeAttr('style');
                    this.currentResult         = data;
                    this.currentResult.ids     = data.selected;
                    this.currentResult.percent = 0;
                    this.totalSync             = 0;
                    this.syncData(0);
                    $("#progress-content").show();
                }

            } else {
                /**
                 * Fix the issue can't delete all item when select all item on mass action
                 */
                if (actionIndex === 'delete' && data.excludeMode) {
                    data.selected = [];
                }

                var action   = this.getAction(actionIndex),
                    callback = this._getCallback(action, data);

                action.confirm ? this._confirm(action, callback) : callback();

                return this;
            }
        },

        /**
         * @param start
         */
        syncData: function (start) {
            var end  = start + 100,
                ids  = this.currentResult.ids.slice(start, end),
                self = this;

            $.ajax({
                url: window.ZohoCRM.syncUrl,
                type: 'post',
                dataType: 'json',
                data: {ids: ids},
                success: function (result) {
                    if (result.status) {
                        var percent = ids.length / self.currentResult.total * 100;

                        self.totalSync += result.total;
                        percent = percent.toFixed(2);

                        self.currentResult.percent += parseFloat(percent);
                        if (self.currentResult.percent > 100) {
                            self.currentResult.percent = 100;
                            self.disableElement(false);
                        }

                        var percentText = self.currentResult.percent.toFixed(2) + '%';

                        $(".progress-bar").css('width', percentText);
                        $("#sync-percent").text(percentText);
                        if (end < self.currentResult.total) {
                            self.syncData(end);
                        } else {
                            var messageText = window.ZohoCRM.successMessage.replace('#1', self.totalSync);

                            self.reloadGrid();
                            self.showMessage('message-success', messageText);
                        }
                    } else {
                        self.showMessage('message-error', result.message);
                        self.disableElement(false);
                        self.reloadGrid();
                    }

                },
                error: function () {
                    self.showMessage('message-error', window.ZohoCRM.errorMessage);
                    self.disableElement(false);
                }
            });
        },
        reloadGrid: function () {
            var grid = uiRegistry.get('mpzoho_queue_listing.mpzoho_queue_listing_data_source');

            // flagReload not use to filter, it's use to affect change value to reload on grid
            grid.params.flagReload = Date.now();
            grid.reload();
        },

        /**
         * @param status
         * @returns {*}
         */
        disableElement: function (status) {
            var loaderElement = $("#container .admin__data-grid-loading-mask");

            return status ? loaderElement.show() : loaderElement.hide();
        },

        /**
         * @param classCss
         * @param message
         */
        showMessage: function (classCss, message) {
            var messageElement = $("#mp-sync .message");

            messageElement.removeClass('message-error message-success message-notice');
            $("#mp-sync .message-text strong").text(message);
            messageElement.addClass(classCss).show();
        }
    });
});

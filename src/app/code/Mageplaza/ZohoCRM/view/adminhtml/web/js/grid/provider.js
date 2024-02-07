/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
    'underscore',
    'Magento_Ui/js/grid/provider',
    'uiRegistry',
    'mage/translate',
    document.getElementsByClassName("mp_menu").length ? 'chartBundle' : '',
], function ($, _, Provider, Registry, $t) {
    'use strict';

    return Provider.extend({
        chartElement: 'sync-chart',
        dateFormat : '',
        isEnableReportMenu: function(){
            return $("#menu.mp_menu").length;
        },

        reload: function (options) {
            if(this.isEnableReportMenu()){
                this.dateFormat = 'Y-MM-DD';
                this.addParamsToFilter();
            }

            this._super(options);
        },

        /**
         * Compatible with Mageplaza Reports
         */
        addParamsToFilter: function () {
            var dateRangElement = $('#daterange');

            this.params.filters.created_at = {};
            if(!this.dateFormat){
                var name = 'mpzoho_sync_listing.mpzoho_sync_listing.listing_top.listing_filters.created_at';
                var createAtComponent =  Registry.get(name);

                createAtComponent.visible(false);
                this.dateFormat = Registry.get(name + '.from').outputDateFormat;
            }
            this.params.filters.created_at.startDate = dateRangElement.data().startDate.format(this.dateFormat);
            this.params.filters.created_at.endDate   = dateRangElement.data().endDate.format(this.dateFormat);

        },

        /**
         * @param data
         * @returns {*}
         */
        processData: function (data) {
            var self = this,
                key  = [],
                i;


            if(this.isEnableReportMenu()){
                if (this.params.filters) {
                    $.each(data.items, function(index, value) {
                        var startDate = new Date(self.params.filters.created_at.startDate).getTime(),
                            endDate   = new Date(self.params.filters.created_at.endDate).getTime(),
                            current   = new Date(value.created_at.split(' ')[0]).getTime();

                        if (startDate > current || endDate < current) {
                            key.push(index);
                        }
                    });

                    for (i = key.length -1; i >= 0; i--) {
                        data.items.splice(key[i],1);
                    }
                }

                this.buildChart(data);
            }

            return this._super(data);
        },

        /**
         * @returns {string[]}
         */
        getMpFields: function () {
            return [
                'total_object',
                'total_pending',
                'total_request',
            ];
        },

        /**
         * Build chart when Mp Reports enable
         */
        buildChart: function (data) {
            var items = data.items,
                zohoData = [0, 0, 0],
                mpFields = this.getMpFields(),
                isCreateChart = false;

            if (Object.keys(items).length) {
                _.each(items, function (record) {
                    _.each(mpFields, function (val, key) {
                        var value = Number(record[val]);

                        if(value > 0){
                            zohoData[key] += value;
                            isCreateChart = true;
                        }
                    });
                });
            }

            if (isCreateChart) {
                this.createChart(zohoData);
                $('#' + this.chartElement).show();
            } else {
                $('#' + this.chartElement).hide();
            }

            if(this.params.filters && this.params.filters.created_at){
                delete this.params.filters.created_at;
            }

        },

        /**
         * @param ChartData
         */
        createChart: function (ChartData){
            var config = {
                type: 'pie',
                data: {
                    datasets: [{
                        data: ChartData,
                        fill: true,

                        backgroundColor: ['rgba(255,0,0,0.5)', '#33CB80', '#F1B55F'],
                        borderWidth: 1
                    }],
                    labels: [$t('Total Object'), $t('Total Pending'), $t('Total Request')]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    legend: {
                        display: true,
                        position: 'left'
                    },
                    tooltips: {
                        callbacks: {}
                    }
                }
            };

            if (typeof window[this.chartElement] !== 'undefined' &&
                typeof window[this.chartElement].destroy === 'function'
            ) {
                window[this.chartElement].destroy();
            }

            /* global Chart */
            window[this.chartElement] = new Chart($('#' + this.chartElement), config);
        }
    });
});

define([
    'jquery',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.linearCharts', {
        options: {
            data: {},
            selectorInit: 'chart-overview'
        },

        _create: function () {
            this.renderSalesChart();
        },

        isXAxisHasDateFormat: function () {
            var testValue = this.options.data.length && this.options.data[0]['date'];

            return !isNaN(new Date(testValue));
        },

        renderSalesChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.XYChart),
                chartIdentifier = self.element.data('chart-identifier');

            am4core.useTheme(am4themes_animated);

            chart.data = self.options.data;
            chart.dateFormatter.firstDayOfWeek = 0;

            if (!this.isXAxisHasDateFormat()) {
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());

                categoryAxis.dataFields.category = 'date';
            } else {
                var dateAxis = chart.xAxes.push(new am4charts.DateAxis());

                dateAxis.renderer.grid.template.location = 0;
                dateAxis.renderer.minGridDistance = 50;
                dateAxis.renderer.labels.template.location = 0.5;

                dateAxis.baseInterval = {
                    'timeUnit': self.options.interval === '0' ? 'day' : self.options.interval,
                    'count': 1
                };
                dateAxis.gridIntervals.setAll([
                    { timeUnit: 'day', count: 1 },
                    { timeUnit: 'week', count: 1 },
                    { timeUnit: 'month', count: 1 },
                    { timeUnit: 'month', count: 2 },
                    { timeUnit: 'month', count: 3 },
                    { timeUnit: 'month', count: 6 },
                    { timeUnit: 'year', count: 1 },
                    { timeUnit: 'year', count: 2 },
                    { timeUnit: 'year', count: 5 },
                    { timeUnit: 'year', count: 10 },
                    { timeUnit: 'year', count: 50 },
                    { timeUnit: 'year', count: 100 },
                    { timeUnit: 'year', count: 200 },
                    { timeUnit: 'year', count: 500 },
                    { timeUnit: 'year', count: 1000 },
                    { timeUnit: 'year', count: 2000 },
                    { timeUnit: 'year', count: 5000 },
                    { timeUnit: 'year', count: 10000 },
                    { timeUnit: 'year', count: 100000 }
                ]);
            }

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis()),
                series = chart.series.push(new am4charts.LineSeries()),
                currency = self.options.currency ? self.options.currency : '';

            if (chartIdentifier === 'abandoned') {
                series.dataFields.valueY = 'count';
            } else {
                series.dataFields.valueY = 'value';
            }

            if (!this.isXAxisHasDateFormat()) {
                series.dataFields.categoryX = 'date';
            } else {
                series.dataFields.dateX = 'date';
            }

            series.tooltipText = currency + '[bold]{valueY}';
            series.tooltip.pointerOrientation = 'vertical';

            chart.cursor = new am4charts.XYCursor();
            chart.cursor.snapToSeries = series;
            chart.cursor.xAxis = dateAxis || categoryAxis;
            chart.scrollbarX = new am4core.Scrollbar();
            chart.zoomOutButton.marginRight = 30;

            // Enable export
            chart.exporting.menu = new am4core.ExportMenu();
            chart.exporting.menu.items = [{
                label: '...',
                menu: [{
                        label: 'Image',
                        menu: [
                            { type: 'png', label: 'PNG' },
                            { type: 'jpg', label: 'JPG' },
                            { type: 'svg', label: 'SVG' },
                            { type: 'pdf', label: 'PDF' }
                        ]
                    }, {
                        label: 'Print', type: 'print'
                    }
                ]
            }];
        }
    });

    return $.mage.linearCharts;
});

define([
    'jquery',
    'mage/adminhtml/grid'
], function ($, grid) {
    'use strict';

    $.widget('mage.amlandingAssignProducts', {
        options: {
            'selectedProducts': {},
            'categoryProducts': {},
            'gridJsObjectName': null,
            'storageFieldSelector': '[name="category_products"]'
        },

        _create: function () {
            this.options.categoryProducts = $H(this.options.selectedProducts);
            this.gridJsObject = window[this.options.gridJsObjectName];

            $(this.options.storageFieldSelector).val(Object.toJSON(this.options.categoryProducts));
            this.gridJsObject.rowClickCallback = $.proxy(this.categoryProductRowClick, this);
            this.gridJsObject.checkboxCheckCallback = $.proxy(this.registerCategoryProduct, this);
        },

        registerCategoryProduct: function(grid, element, checked) {
            if (checked) {
                this.options.categoryProducts.set(element.value, element.value);
            } else {
                this.options.categoryProducts.unset(element.value);
            }
            $(this.options.storageFieldSelector).val(Object.toJSON(this.options.categoryProducts));
            grid.reloadParams = {
                'selected_products[]': this.options.categoryProducts.keys()
            };
        },

        categoryProductRowClick: function(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input[type="checkbox"]');

                if (checkbox[0]) {
                    this.gridJsObject.setCheckboxChecked(checkbox[0], checkbox[0].checked);
                }
            }
        }
    });

    return $.mage.amlandingAssignProducts;
});

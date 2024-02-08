define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'domReady!'
], function ($, modalConfirm) {
    'use strict';

    $.widget('mage.categoryFormAdditionalAction', {
        options: {
            isCategoryDynamic: 0,
            saveConfirmed: false,
            changeDisplayModeUrl: null,
            displayModeCheckboxSelector: '[name="amlanding_is_dynamic"]',
            addProductsButtonSelector: '#am-add-products-button'
        },

        _create: function() {
            $('#container').on('click', this.options.displayModeCheckboxSelector, function() {
                var mode = $(this.options.displayModeCheckboxSelector).attr('checked') == 'checked' ? 1 : 0;
                $(this.options.addProductsButtonSelector).toggle(
                    $(this.options.displayModeCheckboxSelector).attr('checked') != 'checked'
                );
                $.ajax({
                    type: 'POST',
                    url: this.options.changeDisplayModeUrl,
                    data: {mode: mode},
                    context: $('body'),
                    showLoader: true,
                    success: function () {
                        $(document).trigger('merchandiser:DisplayModeChanged');
                    }.bind(this)
                });
            }.bind(this));

            $(this.options.addProductsButtonSelector).toggle(
                $(this.options.displayModeCheckboxSelector).attr('checked') != 'checked'
            );

            $('#save').on('click', function(e) {
                if (this.options.saveConfirmed) {
                    return;
                }

                if ($('[name="amlanding_is_dynamic"]').val() == 1
                    && jQuery("#am-condition-from-wrapper input").length == 1
                ) {
                    e.stopImmediatePropagation();
                    modalConfirm({
                        title: $.mage.__('Unable to perform the action'),
                        content: $.mage.__("Sorry, Automatic Category without conditions can’t be saved. Please specify at least one condition in Product Conditions tab."),
                    });
                    return;
                }

                if (this.options.isCategoryDynamic == 0 && $('[name="amlanding_is_dynamic"]').attr('checked') == 'checked') {
                    e.stopImmediatePropagation();
                    modalConfirm({
                        title: $.mage.__('Save current category?'),
                        content: $.mage.__("Important: the original 'physical' products will no longer appear in the category.\n" +
                            "When you turn off the Automatic Category, the last collected 'virtual' products will become 'physical'."),
                        actions: {
                            confirm: function () {
                                this.options.saveConfirmed = true;
                                $('#save').trigger('click');
                            }.bind(this)
                        }
                    });
                }
            }.bind(this));
        }
    });

    return $.mage.categoryFormAdditionalAction;
});

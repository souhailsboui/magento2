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
    'Mageplaza_ZohoCRM/js/view/variables'
], function ($, ZohoVariables) {
    "use strict";

    $.widget('mageplaza.zoho', {

        _create: function () {
            var self = this;

            if (!this.options.isEdit) {
                this.detachMappingObject();
                this.initObserve();
            } else {
                self.initVariables();
            }
        },

        detachMappingObject: function () {
            var magentoObject    = $("#magento_object").val();
            var optionZohoModule = '';

            $.each(this.options.mappingObject[magentoObject], function (index, module) {
                optionZohoModule += '<option value="' + module.value + '">' + module.label + '</option>';
            });

            $("#zoho_module").html(optionZohoModule);
        },

        checkMapping: function () {
            var self = this;

            $("#magento_object").change(function () {
                self.detachMappingObject();
            });
        },

        validateWebsite: function () {
            var websiteIdsElement = $('#website_ids');

            $('#website_ids-error').remove();
            if (websiteIdsElement.val() === null) {
                websiteIdsElement.parent().append(this.getErrorElement());

                return false;
            }

            return true;
        },

        getErrorElement: function () {
            return '<label class="mage-error" id="website_ids-error">' + this.options.errorLabel + '</label>';
        },

        /**
         * Init observe
         */
        initObserve: function () {
            this.checkMapping();
            this.initSync();
        },

        initVariables: function () {
            var self = this;

            $(".insert_variable").click(function () {
                if (self.options.variables) {
                    var fieldTarget = $(this).attr('target');

                    ZohoVariables.setEditor(fieldTarget + '-value');
                    ZohoVariables.openVariableChooser(self.options.variables);
                }
            });
        },

        initSync: function () {
            var self = this;

            $("#sync-next").click(function () {
                if (self.validateWebsite()) {
                    var params = {
                        website_ids: $('#website_ids').val(),
                        magento_object: $('#magento_object').val(),
                        zoho_module: $('#zoho_module').val()
                    };

                    $.ajax({
                        method: 'POST',
                        url: self.options.mappingUrl,
                        data: params,
                        showLoader: true,
                        success: function (response) {
                            if (response.canMapping) {
                                $("#mapping-body").append(response.mapping_html);
                                $('#general>legend>span').text(self.options.generalLabel);
                                $('.page-columns .side-col').show();
                                $('.admin__field.field.field-status,' +
                                    '.admin__field.field.field-name,.admin__field.field.field-priority').show();
                                $('button#save,button#reset,button#save_and_continue').show();
                                $('#container').attr('style', 'width:calc( (100%) * 0.75 - 30px )');
                                $("#sync-next").hide();
                                $('#magento_object,#zoho_module,#website_ids').attr('readonly', 'readonly')
                                    .css('pointer-events', 'none');
                                self.options.variables = JSON.parse(response.variables);
                                self.initVariables();
                                $('#mp_mapping').trigger('contentUpdated');
                            } else {
                                location.reload();
                            }
                        }
                    });
                }
            });
        }
    });

    return $.mageplaza.zoho;
});

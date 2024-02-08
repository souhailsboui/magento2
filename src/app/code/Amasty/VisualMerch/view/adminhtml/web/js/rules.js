/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'jquery',
    'Magento_Rule/rules',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'prototype'
], function (jQuery, VarienRulesForm, confirm, __) {

    var AmastyRulesForm = new Class.create(VarienRulesForm, {
        initialize: function ($super, parent, newChildUrl, importUrl) {
            $super(parent, newChildUrl);
            this.importUrl = importUrl;
            if ($$('[data-index="conditions_fieldset"]').length) {
                var parentFieldset = $$('[data-index="conditions_fieldset"]').first();
                $(parentFieldset).on('change', '#am-categories-select', function(){
                    confirm({
                        content: __('Are you sure, you want to import conditions?'),
                        actions: {
                            confirm: this.importConditions.bind(this)
                        }
                    });
                }.bind(this));
            }
        },

        importConditions: function () {
            new Ajax.Request(this.importUrl, {
                parameters: {
                    'form_key': FORM_KEY, 'source_id': $('am-categories-select').value
                },
                loader: true,
                onSuccess: function (transport) {
                    this.parent.update(transport.responseText);
                    var elems = this.parent.getElementsByClassName('rule-param');

                    for (var i = 0; i < elems.length; i++) {
                        this.initParam(elems[i]);
                    }
                }.bind(this),
                onFailure: this._processFailure.bind(this)
            });

        }
    });

    return AmastyRulesForm;
});
